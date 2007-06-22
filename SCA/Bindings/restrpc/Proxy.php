<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                         |
| All Rights Reserved.                                                        |
+-----------------------------------------------------------------------------+
| Licensed under the Apache License, Version 2.0 (the "License"); you may not |
| use this file except in compliance with the License. You may obtain a copy  |
| of the License at -                                                         |
|                                                                             |
|                   http://www.apache.org/licenses/LICENSE-2.0                |
|                                                                             |
| Unless required by applicable law or agreed to in writing, software         |
| distributed under the License is distributed on an "AS IS" BASIS, WITHOUT   |
| WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.            |
| See the License for the specific language governing  permissions and        |
| limitations under the License.                                              |
+-----------------------------------------------------------------------------+
| Author: Graham Charters,                                                    |
|         Matthew Peters,                                                     |
|         Megan Beynon,                                                       |
|         Chris Miller,                                                       |
|         Caroline Maynard,                                                   |
|         Simon Laws                                                          |
+-----------------------------------------------------------------------------+
*/

/**
 *
 * TODO: think about the name 'proxy' : proxy for what? 'for a service that is
 * going to be called locally.' or remotely.
 * do we like the word proxy?
 *
 */


require "SCA/SCA_Exceptions.php";

/**
 *
 *
 */

if ( ! class_exists('SCA_Bindings_restrpc_Proxy', false)) {

    class SCA_Bindings_restrpc_Proxy
    {
        private $target_url;
        private $reference_type;
        private $xml_das;
        private $type_list;
        private $received_headers;

        public function __construct($target,
                                    $base_path_for_relative_paths, 
                                    $binding_config)
        {
            SCA::$logger->log("Entering constructor");
            $this->target_url = SCA_Helper::constructAbsoluteTarget($target, $base_path_for_relative_paths);
            SCA::$logger->log("Exiting constructor");
        }

        /**
         * Once the proxy has been constructed the runtime passes in 
         * the information associated with the reference that the proxy
         * represents
         */
        public function addReferenceType(SCA_ReferenceType $reference_type)
        {
            $this->reference_type = $reference_type;

            // If there are type descriptions create and XML DAS and add them
            if ( count($reference_type->getTypes()) > 0 ) {
                // Some XSD types are specified with the reference
                // annotation so use these XSDs to build the XML DAS
                $this->xml_das = $reference_type->getXmlDas();
                
                // get the list of types that have been loaded into
                // the XML DAS
                $this->type_list = SCA_Helper::getAllXmlDasTypes($this->xml_das);
            } else {
                // No XSDs are specified so we assume that only simple type 
                // parameters will be used. 
            }
        }

        /**
         * Call the remote method using CURL to send the HTTP request
         */
        public function __call($method_name, $arguments)
        {
            SCA::$logger->log("Entering");
            SCA::$logger->log("method name is $method_name");
            // construct the request URL
            $url = $this->target_url."/".$method_name;

            // initialize a request using the CURL library
            $request = curl_init();

            $content_type = null;
            $body         = null;

            // Look to see if we have an SDO, i.e. convert to XML and do a POST 
            // or just simple type params, i.e. represent as URL params and do a GET
            if ( count($arguments) == 1 &&
                 is_object($arguments[0]) ){
                // looks like its a single SDO so convert to XML 
                $obj = $arguments[0];
                
                if($obj instanceof SDO_DataObjectImpl){
                    $body = $this->toXml($entry);
                } else {
                    throw new SCA_RuntimeException("Argument 0 to $method_name of type php object found. " .
                                                   "Arguments must be either primitives or a single SDO");
                } 

                curl_setopt($request, CURLOPT_POST, true);
                curl_setopt($request, CURLOPT_POSTFIELDS, $body);
                $content_type = "text/xml";                              
            } else {
                // looks like we have zero or more simple types to convert to 
                // form params. 
                $argument_id = 0;
                $no_of_arguments = count($arguments);
                foreach ( $arguments as $argument ) {
                    if ( $argument == null ) {
                        // ignore null parameters
                    } else if ( is_object($argument) ) {
                        throw new SCA_RuntimeException("Argument $argument_id to $method_name of type object found. " .
                                                       "Arguments must be either primitives or a single SDO");
                    } else if ( is_array($argument) ) {
                        throw new SCA_RuntimeException("Argument $argument_id to $method_name of type array found. " .
                                                       "Arguments must be either primitives or SDOs");
                    } else {
                        $body = $body . "param" . $argument_id . "=" . urlencode($argument);
                    }
                    $argument_id += 1;
                    
                    if ($argument_id < $no_of_arguments) {
                        $body = $body . "&";
                    }
                }
                $url = $url . "?" . $body;
                $content_type = "application/x-www-form-urlencoded";
            }

            curl_setopt($request, CURLOPT_URL, $url);
            curl_setopt($request, CURLOPT_HEADER, false);
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            $header = array("User-Agent: SCA",
                            "Content-Type: $content_type",
                            "Accept: text/xml");
            curl_setopt($request, CURLOPT_HTTPHEADER, $header);

            // Do the POST
            $response = curl_exec($request);

            // TODO probably need a better way of detecting PHP errors at the far end
            if ( strchr($response,'<b>Fatal error</b>') !== false
                || strchr($response,'<b>Parse error</b>') !== false) {
                SCA::$logger->log('Bad response from restrpc call: ' . $response);
                throw new SCA_RuntimeException('Bad response from restrpc call: ' . $response);
            }

            // Get info about the response
            $response_http_code = curl_getinfo($request, CURLINFO_HTTP_CODE);
            $content_type = curl_getinfo($request, CURLINFO_CONTENT_TYPE);

            // close the session
            curl_close($request);

            // test the response status
            if ( $response == null || $response == false  ) {
                SCA::$logger->log("restrpc call to $this->target_url for method " .
                                               "$method_name failed ");
                throw new SCA_RuntimeException("restrpc call to $this->target_url for method " .
                                               "$method_name failed ");
            }

            // test the response status
            if($response_http_code != 200){

                switch($response_http_code){
                    case 503:
                        //TODO: pick out the message from the response if there is one
                        //for now just pass back a one liner.
                        throw new SCA_ServiceUnavailableException("Target service $url - Service Unavailable");
                    case 409:
                        throw new SCA_ConflictException("Target service $url - Conflict");
                    case 407:
                        throw new SCA_AuthenticationException("Target service $url - Authentication Required");
                    case 400:
                        throw new SCA_BadRequestException("Target service $url - Bad Request");
                    case 500:
                        throw new SCA_InternalServerErrorException("Target service $url - Internal Server Error");
                    case 405:
                        throw new SCA_MethodNotAllowedException("Target service $url - Method Not Allowed");
                    case 401:
                        throw new SCA_UnauthorizedException("Target service $url - Unauthorized");
                    case 404:
                        throw new SCA_NotFoundException("Target service $url - not found");                        
                    default:
                        throw new SCA_RuntimeException("Target service $url - status code $response_http_code when 200 expected");
                }
            }            

            // Check for and handle primitive or XML responses
            if (strstr($content_type, 'xml')) {
                // Content-Type mentions XML so assume response is XML
                // Turn the XML response into an SDO 
                $return = $this->fromXml($response);
            } else {
                $return = $response;
            }

            return $return;
        }

        /**
         * Allows the reference user to create a data object
         * based on a type that is expected to form part of
         * a message to reference
         */
        public function createDataObject($namespace_uri, $type_name)
        {
            SCA::$logger->log("Entering");
            try {
                $dataobject = $this->xml_das->createDataObject($namespace_uri, $type_name);
                return $dataobject;
            } catch( Exception $e ) {
                throw new SCA_RuntimeException($e->getMessage());
            }
            return null;
        }

        private function fromXml($xml){
            SCA::$logger->log("Entering");
            try{
                $doc = $this->xml_das->loadString($xml);
                $ret = $doc->getRootDataObject();
                return $ret;
            }
            catch( Exception $e ){
                return $e->getMessage();

            }
        }
    }

}/* End instance check                                                        */

?>