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
require "SCA/SCA_Exceptions.php";

if ( ! class_exists('SCA_Bindings_Atom_Proxy', false)) {
    class SCA_Bindings_Atom_Proxy
    {
        /**
         * Holds the target uri of the Atom feed
         *
         * @var string
         */
        private $target;

        private $resource_class;
        private $xmldas;

        /**
         * Hold headers from curl
         */
        private $received_headers;

        public function __construct($target)
        {
            SCA::$logger->log("Entering constructor");
            $this->target = $target;
            SCA::$logger->log("Exiting constructor");
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

        /**
         * Atom has fixed methods to interact with a feed and resources __call
         * is implemented to catch any problem cases.
         */
        public function __call($method_name, $arguments)
        {
            SCA::$logger->log("Call to invalid method '$method_name'.  Atom only supports create(), retrieve(), update(), delete() or enumerate()");
            throw new SCA_MethodNotAllowedException("Call to invalid method '$method_name'.  Atom only supports create(), retrieve(), update(), delete() or enumerate()");
            //throw new SCA_RuntimeException("Call to invalid method $method_name.  Atom only supports create(), retrieve(), update(), delete() or enumerate()");
        }

        /**
         * Enter description here...
         *
         * @param unknown_type $entry can be an SDO or an XML string
         *
         * @return SDO
         */
        public function create($entry) // $entry can be
        {
            SCA::$logger->log("Entering");

            $headers = array("Content-Type: application/atom+xml ");

            //Need to convert the SDO to xml
            if ($entry instanceof SDO_DataObjectImpl) {
                $xml = $this->toXml($entry);
            } else {
                $xml = $entry;
            }

            SCA::$logger->log("Building request using cURL");
            $handle        = curl_init($this->target);
            $curlopt_array = array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADERFUNCTION => array($this, '_headerCallback'));
            curl_setopt_array($handle, $curlopt_array);

            //TODO: might want to add User-Agent and Accept headers.

            $this->received_headers = array();
            SCA::$logger->log("Sending request");

            $result = curl_exec($handle);

            if ($result === false) {
                throw new SCA_RuntimeException(curl_error($handle),
                curl_errno($handle));
            }

            $sdo = $this->_fromXml($result);

            $response_http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            SCA::$logger->log("create(): Http code returned: $response_http_code");
            SCA::$logger->log("create(): Body returned: $result");

            curl_close($handle);


            /* By this point the header callback should have stored the
            * received headers
            */
            if ($response_http_code != 201) {

                switch($response_http_code) {
                    case 503:
                        //TODO: pick out the message from the response if there is one
                        //for now just pass back a one liner.
                        throw new SCA_ServiceUnavailableException("Service Unavailable");
                    case 409:
                        throw new SCA_ConflictException("Conflict");
                    case 407:
                        throw new SCA_AuthenticationException("Authentication Required");
                    case 400:
                        throw new SCA_BadRequestException("Bad Request");
                    case 500:
                        throw new SCA_InternalServerErrorException("Internal Server Error");
                    case 405:
                        throw new SCA_MethodNotAllowedException("Method Not Allowed");
                    case 401:
                        throw new SCA_UnauthorizedException("Unauthorized");
                    default:
                        throw new SCA_RuntimeException('create() status code '. $response_http_code . ' when 201 expected');
                }
            } else if (!array_key_exists('LOCATION', $this->received_headers)) {
                throw new SCA_RuntimeException('No Location: header received from create()');
            } else {
                return $sdo;
            }
        }


        public function retrieve($id=null)
        {
            SCA::$logger->log("Entering");

            //TODO these should all be building a setopts array not sending a lot.

            /* If the id begins with "http:" or "https:" let it through     */
            if (strpos($id, 'http:') === 0 || strpos($id, 'https:') === 0 ) {
                $handle = curl_init($id);
            }
            else if ($id !== null) {
                //$this->target is the target of the atom binding. If there is no slash on the end of the target provided, one is added.
                $slash_if_needed =
                ('/' === $this->target[strlen($this->target)-1])?'':'/';

                $handle = curl_init($this->target.$slash_if_needed."$id");
            }
            else {
                $handle = curl_init($this->target);
            }
            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_HTTPGET, true);
            curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);

            $headers = array("Content-Type: application/atom+xml;");
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($handle);

            $response_http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            SCA::$logger->log("retrieve(): Http code returned: $response_http_code");
            SCA::$logger->log("retrieve(): Body returned: $result");

            curl_close($handle);

            //convert the result into an sdo.
            $sdo = $this->_fromXml($result);

            if ($response_http_code != 200) {

                switch($response_http_code) {
                    case 503:
                        //TODO: pick out the message from the response if there is one
                        //for now just pass back a one liner.
                        throw new SCA_ServiceUnavailableException("Service Unavailable");
                    case 409:
                        throw new SCA_ConflictException("Conflict");
                    case 407:
                        throw new SCA_AuthenticationException("Authentication Required");
                    case 400:
                        throw new SCA_BadRequestException("Bad Request");
                    case 500:
                        throw new SCA_InternalServerErrorException("Internal Server Error");
                    case 405:
                        throw new SCA_MethodNotAllowedException("Method Not Allowed");
                    case 401:
                        throw new SCA_UnauthorizedException("Unauthorized");
                    default:
                        throw new SCA_RuntimeException('retrieve() status code '. $response_http_code . ' when 200 expected');
                }
            } else {
                return $sdo;
            }
        }

        public function update($id, $sdo)
        {
            SCA::$logger->log("Entering");
            if ($sdo instanceof SDO_DataObjectImpl) {
                $xml = $this->toXml($sdo);
            } else {
                $xml = $sdo;
            }

            /* If the id begins with "http:" or "https:" let it through     */
            if (strpos($id, 'http:') === 0 || strpos($id, 'https:') === 0 ) {
                $handle = curl_init($id);
            }
            else {
                $slash_if_needed =
                ('/' === $this->target[strlen($this->target)-1])?'':'/';

                $handle = curl_init($this->target.$slash_if_needed."$id");
            }
            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($handle, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);

            $headers = array("Content-Type: application/atom+xml;");
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($handle);

            $response_http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            SCA::$logger->log("update(): Http code returned: $response_http_code");

            curl_close($handle);

            //NOTE: update does not return anything in the body

            if ($response_http_code != 200) {

                switch ($response_http_code) {
                    case 503:
                        //TODO: pick out the message from the response if there is one
                        //for now just pass back a one liner.
                        throw new SCA_ServiceUnavailableException("Service Unavailable");
                    case 409:
                        throw new SCA_ConflictException("Conflict");
                    case 407:
                        throw new SCA_AuthenticationException("Authentication Required");
                    case 400:
                        throw new SCA_BadRequestException("Bad Request");
                    case 500:
                        throw new SCA_InternalServerErrorException("Internal Server Error");
                    case 405:
                        throw new SCA_MethodNotAllowedException("Method Not Allowed");
                    case 401:
                        throw new SCA_UnauthorizedException("Unauthorized");
                    default:
                        throw new SCA_RuntimeException('update() status code '. $response_http_code . ' when 200 expected');
                }
            } else {
                return true;
            }
        }


        public function delete($id)
        {
            SCA::$logger->log("Entering");

            /* If the id begins with "http:" or "https:" let it through     */
            if (strpos($id, 'http:') === 0 || strpos($id, 'https:') === 0 ) {
                $handle = curl_init($id);
            }
            else {
                //TODO these should all be building a setopts array not sending a lot.
                $slash_if_needed =
                ('/' === $this->target[strlen($this->target)-1])?'':'/';

                $handle = curl_init($this->target.$slash_if_needed."$id");
            }
            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);

            $headers = array("Content-Type: application/atom+xml;");
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);


            $result = curl_exec($handle);

            $response_http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            curl_close($handle);

            //NOTE: delete does not return a body

            if ($response_http_code != 200) {

                switch ($response_http_code) {
                    case 503:
                        //TODO: pick out the message from the response if there is one
                        //for now just pass back a one liner.
                        throw new SCA_ServiceUnavailableException("Service Unavailable");
                    case 409:
                        throw new SCA_ConflictException("Conflict");
                    case 407:
                        throw new SCA_AuthenticationException("Authentication Required");
                    case 400:
                        throw new SCA_BadRequestException("Bad Request");
                    case 500:
                        throw new SCA_InternalServerErrorException("Internal Server Error");
                    case 405:
                        throw new SCA_MethodNotAllowedException("Method Not Allowed");
                    case 401:
                        throw new SCA_UnauthorizedException("Unauthorized");
                    default:
                        throw new SCA_RuntimeException('delete() status code '. $response_http_code . ' when 200 expected');
                }
            } else {
                return true;
            }
        }



        public function enumerate()
        {
            SCA::$logger->log("Entering enumerate()");

            //TODO these should all be building a setopts array not sending a lot.

            $handle = curl_init($this->target);

            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_HTTPGET, true);
            curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);


            $headers = array("Content-Type: application/atom+xml;");
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($handle);


            $response_http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            SCA::$logger->log("enumerate(): Http code returned: $response_http_code");
            SCA::$logger->log("enumerate(): Body returned: $result");

            curl_close($handle);

            $sdo = $this->_fromXml($result);

            if ($response_http_code != 200) {
                switch ($response_http_code) {
                    case 503:
                        //TODO: pick out the message from the response if there is one
                        //for now just pass back a one liner.
                        throw new SCA_ServiceUnavailableException("Service Unavailable");
                    case 409:
                        throw new SCA_ConflictException("Conflict");
                    case 407:
                        throw new SCA_AuthenticationException("Authentication Required");
                    case 400:
                        throw new SCA_BadRequestException("Bad Request");
                    case 500:
                        throw new SCA_InternalServerErrorException("Internal Server Error");
                    case 405:
                        throw new SCA_MethodNotAllowedException("Method Not Allowed");
                    case 401:
                        throw new SCA_UnauthorizedException("Unauthorized");
                    default:
                        throw new SCA_RuntimeException('enumerate() status code '. $response_http_code . ' when 200 expected');
                }
            } else {
                return $sdo;
            }

        }

        /**
         * Allows the reference user to create a data object
         * based on a type that is expected to form part of
         * a message to reference
         *
         * @param string $namespace_uri
         * @param string $type_name
         *
         * @return SDO
         */
        public function createDataObject($namespace_uri, $type_name)
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


        /**
         * TODO - We need to think about where to put this method
         */
        public function addReferenceType(SCA_ReferenceType $reference_type)
        {
            SCA::$logger->log("Entering");

            $this->reference_type = $reference_type;

            // Add type descriptions to the XML DAS. We use XSDs if they
            // are prvoided.
            if ( count($reference_type->getTypes()) > 0 ) {
                // Some XSD types are specified with the reference
                // annotation so use these XSDs to build the XMLDAS
                $this->xmldas = $reference_type->getXmlDas();

                // get the list of types that have been loaded into
                // the XMLDAS in this case
                $this->type_list =
                SCA_Helper::getAllXmlDasTypes($this->xmldas);
            } else {
                //TODO: This is where we end up if we don't specify @types on a client side atom component with an @reference!
                //TODO: refactor this and check routes to this part of the code result in proper response
                $this->xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/Atom1.0.xsd');
            }
        }


        //NOTE: there is a problem with this process - it generates the elements
        //as attributes with values rather than elements, so when they are unpacked on the other side and converted back to an sdo, the values are lost and only the 'attributes' are part of the sdo.
        private function toXml($sdo, $xsd=null)
        {
            SCA::$logger->log("Entering");

            try {
                $type   = $sdo->getTypeName();
                $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/Atom1.0.xsd');

                /*
                //Because the atom format is extensible, if the sdo contains an extended version of the entry type, then we will need to add the types belonging to the sdo we've just received. Code here for this is commented out for now, and the assumed solution is that the client would provide the xsd for the extended entry, so that we can add the types from it to the Atom model.
                //Without an xsd supplied, if you try to create an xml doc from an sdo which has properties that arent in the das, SDO will try to handle what it receives by making the properties in the sdo attributes in the xml doc (rather than elements) but it should probably be throwing an error instead. Working on testcases to explore this behaviour further and raise a defect around this.
                //so the question is how do we get the types for the sdo? Going to have to ask for the location of an xsd to be passed in with the sdo, at least for first version of this code.
                //TODO need to make sure we are finding the xsd however they express the path information.
                //$xmldas->addTypes($xsd);
                */

                $xdoc   = $xmldas->createDocument('', $type, $sdo);
                $xmlstr = $xmldas->saveString($xdoc);
                return         $xmlstr;
            } catch(Exception $e) {
                return $e->getMessage();
            }
        }

        private function _fromXml($xml){
            SCA::$logger->log("Entering");

            try {
                $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/Atom1.0.xsd');
                $doc    = $xmldas->loadString($xml);
                $ret    = $doc->getRootDataObject();
                return  $ret;
            } catch( Exception $e ) {
                return $e->getMessage();
            }
        }


    }


}/* End instance check                                                        */

?>