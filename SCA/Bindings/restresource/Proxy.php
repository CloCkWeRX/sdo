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
 * Proxy for restresource requests. This uses Curl to create the appropriate
 * HTTP request for each of the create, retrieve, update, delete and 
 * enumerate functions. 
 *
 */

if ( ! class_exists('SCA_Bindings_restresource_Proxy', false)) {

    class SCA_Bindings_restresource_Proxy
    {
        private $target_url;
        private $reference_type;
        private $xml_das;
        private $type_list;
        private $received_headers;
        private $resource_class;        

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
                // No XSDs are specified so we assume that XML strings
                // are passed in instead of SDOs
            }
        }

        /**
         * create a new resource. 
         *
         * @param $entry can be an sdo or xml string
         * @return string
         *
         **/
        public function create($resource)
        {

            SCA::$logger->log("Entering create");
            $headers  = array("Content-Type: application/xml ");

            //check whether it is an sdo or an xml string.
            if($resource instanceof SDO_DataObjectImpl){
                //if the thing received is an sdo convert it to xml
                if ( $this->xml_das !== null ) {
                    $xml = SCA_Helper::sdoToXml($this->xml_das, $resource);
                } else {
                    throw new SCA_RuntimeException('Trying to create a resource with SDO but ' .
                                                   'no types specified for reference');
                }
            }else{
                $xml = $resource;
            }
            
            $handle        = curl_init($this->target_url);
            $headers       = array("User-Agent: SCA",
                                   "Content-Type: text/xml;",
                                   "Accept: text/plain");                   
            $curlopt_array = array(CURLOPT_HTTPHEADER => $headers,
                                   CURLOPT_RETURNTRANSFER => true,
                                   CURLOPT_POST => true,
                                   CURLOPT_POSTFIELDS => $xml,
                                   CURLOPT_HEADERFUNCTION => array($this, '_headerCallback'));
            curl_setopt_array($handle, $curlopt_array);

            //TODO: might want to add User-Agent and Accept headers.

            $this->received_headers = array();
            SCA::$logger->log("Sending create request");

            $result = curl_exec($handle);
 
            if ($result === false) {
                //TODO placeholder for error handling
                throw new SCA_RuntimeException(curl_error($handle),
                                               curl_errno($handle));
            }

            $response_http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);
            
            $response_exception = $this->buildResponseException($response_http_code, '201');
            
            if($response_exception != null) {
                throw $response_exception;
            } else if (!array_key_exists('LOCATION', $this->received_headers)) {
                throw new SCA_RuntimeException('No Location: header received from create()');
            } else {
                return $this->received_headers['LOCATION'];
            }
        }

        /**
         * retrieve an existing resource. 
         *
         * @param string $id the resource id to retrieve
         * @return string
         *
         **/
        public function retrieve($id)
        {

            SCA::$logger->log("Entering retrieve");
 
            $slash_if_needed = ('/' === $this->target_url[strlen($this->target_url)-1])?'':'/';
            $handle = curl_init($this->target_url . $slash_if_needed . $id);
            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_HTTPGET, true);
            $headers = array("User-Agent: SCA",
                             "Content-Type: text/plain;",
                             "Accept: text/xml");          
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($handle);

            $response_http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);

            $response_exception = $this->buildResponseException($response_http_code, '200');
            
            if($response_exception != null) {
                throw $response_exception;
            } else {
	            //convert the result into an sdo.
	            $sdo = SCA_Helper::xmlToSdo($this->xml_das, $result);            
	            return $sdo;
            }
        }

        /**
         * update an existing resource. 
         *
         * @param string $id the resource id to retrieve
         * @param sdo    $sdo the updated resource
         * @return boolean
         *
         **/
        public function update($id, $resource)
        {
            SCA::$logger->log("Entering update()");
            //check whether it is an sdo or an xml string.
            if($resource instanceof SDO_DataObjectImpl){
                //if the thing received is an sdo convert it to xml
                if ( $this->xml_das !== null ) {
                    $xml = SCA_Helper::sdoToXml($this->xml_das, $resource);
                } else {
                    throw new SCA_RuntimeException('Trying to update a resource with SDO but ' .
                                                   'no types specified for reference');
                }
            }else{
                $xml = $resource;
            }

            $slash_if_needed = ('/' === $this->target_url[strlen($this->target_url)-1])?'':'/';

            $handle = curl_init($this->target_url.$slash_if_needed."$id");
            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($handle, CURLOPT_POSTFIELDS, $xml);

            //replace with Content-Type: atom whatever
            $headers       = array("User-Agent: SCA",
                                   "Content-Type: text/xml;",
                                   "Accept: text/plain");    
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($handle);

            $response_http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            curl_close($handle);

            $response_exception = $this->buildResponseException($response_http_code, '200');         

            if($response_exception != null) {
                throw $response_exception;
            } else {
                //update does not return anything in the body            
                return true;
            }
        }

        /**
         * delete an existing resource. 
         *
         * @param string $id the resource id to delte
         * @return boolean
         *
         **/
        public function delete($id)
        {

            SCA::$logger->log("Entering delete()");
            $slash_if_needed = ('/' === $this->target_url[strlen($this->target_url)-1])?'':'/';

            $handle = curl_init($this->target_url.$slash_if_needed."$id");
            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "DELETE");
            $headers       = array("User-Agent: SCA",
                                   "Content-Type: text/plain;",
                                   "Accept: text/plain");
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($handle);

            $response_http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            curl_close($handle);

            $response_exception = $this->buildResponseException($response_http_code, '200');         

            if($response_exception != null) {
                throw $response_exception;
            } else {
                //delete does not return a body            
                return true;
            }
        }

        /**
         * get all of the existing resources. 
         *
         * @return an SDO representing the collection
         *
         **/
        public function enumerate()
        {

            SCA::$logger->log("Entering enumerate()");
            
            $handle = curl_init($this->target_url);

            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_HTTPGET, true);

            $headers = array("User-Agent: SCA",
                             "Content-Type: text/plain;",
                             "Accept: text/xml");         
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($handle);

            $response_http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            curl_close($handle);

            $response_exception = $this->buildResponseException($response_http_code, '200');         

            if($response_exception != null) {
                throw $response_exception;
            } else { 
                //convert the result into an sdo.
                $sdo = SCA_Helper::xmlToSdo($this->xml_das, $result);                
                return $sdo;
            }
        }

        /**
         * Allows the reference user to create a data object
         * based on a type that is expected to form part of
         * a message to reference
         */
        public function createDataObject( $namespace_uri, $type_name )
        {
            SCA::$logger->log("Entering");
            try {
                $xmldas     = $this->reference_type->getXmlDas();
                $dataobject = $xmldas->createDataObject($namespace_uri, $type_name);
                return $dataobject;
            } catch( Exception $e ) {
                throw new SCA_RuntimeException($e->getMessage());
            }
            return null;
        }


        /*
        * Callback set by CURL_HEADERFUNCTION
        * Receives header lines from the response.
        */
        private function _headerCallback ($handle, $header)
        {
            SCA::$logger->log("Entering");
            SCA::$logger->log("header = $header");
            /* split the header on the first : */
            $split_header = preg_split('/\s*:\s*/', trim($header), 2);
            if (count($split_header) > 1) {
                $this->received_headers[strtoupper($split_header[0])] =
                $split_header[1];
            } else if (!empty($split_header[0])) {
                $this->received_headers[] = $split_header[0];
            }

            return strlen($header);
        }
        
        
        private function buildResponseException($response_http_code, $expected_response_code){
            $return_exception = null;
            
            //Create SCA exceptions based on the HTTP response code.
            if($response_http_code != $expected_response_code){
                SCA::$logger->log("HTTP error $response_http_code in proxy");
                
                switch($response_http_code){
                    case 503:
                        $return_exception = new SCA_ServiceUnavailableException("Service Unavailable");
                        break;
                    case 409:
                        $return_exception = new SCA_ConflictException("Conflict");
                        break;
                    case 407:
                        $return_exception = new SCA_AuthenticationException("Authentication Required");
                        break;
                    case 400:
                        $return_exception = new SCA_BadRequestException("Bad Request");
                        break;
                    case 500:
                        $return_exception = new SCA_InternalServerErrorException("Internal Server Error");
                        break;
                    case 405:
                        $return_exception = new SCA_MethodNotAllowedException("Method Not Allowed");
                        break;
                    case 401:
                        $return_exception = new SCA_UnauthorizedException("Unauthorized");
                        break;
                    case 404:
                        $return_exception = new SCA_NotFoundException("Resource not found");
                        break;
                    default:
                        $return_exception = new SCA_RuntimeException('status code '. 
                                                                     $response_http_code . 
                                                                     ' found ' .  
                                                                     $expected_response_code .
                                                                     ' expected');
                        break;
                }
            }            
            return $return_exception;
        }
    }

}/* End instance check                                                        */

?>