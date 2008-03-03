<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006, 2007.                                   |
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
| Author: Wangkai Zai                                                         |
|                                                                             |
+-----------------------------------------------------------------------------+
*/
require "SCA/SCA_Exceptions.php";
require 'SCA/Bindings/message/SAM_Client.php';
require "SCA/Bindings/message/Mapper.php";
require 'SCA/Bindings/message/ServiceDescriptionGenerator.php';

if (! class_exists('SCA_Bindings_message_Proxy', false)) {

    class SCA_Bindings_message_Proxy {

        private $ms_client    = null;
        private $mapper       = null; 
        private $waitResponseTimeout = -1;
        
        /**
         * Constructor 
         */
        public function __construct($path_to_msd,
                                    $immediate_caller_directory, 
                                    $binding_config)
        {
            SCA::$logger->log('entering');

            //check if SAM extensions is loaded
            if ( ! (in_array('sam', get_loaded_extensions())) ) {
                throw new SCA_RuntimeException("The SAM extension must be loaded");
            }

            if ( $binding_config === null ) {
                $binding_config = array();
            }
            $binding_config['msd'] = $path_to_msd ;

            /*parse MSD file */
            $msd_config = SCA_Bindings_message_ServiceDescriptionGenerator::parseBindingConfig($binding_config);

            if(isset($msd_config->responseTimeout)){
                $this->waitResponseTimeout = $msd_config->responseTimeout;
            }

            if (!isset($msd_config->wsdl)) {
                throw new SCA_RuntimeException("Path to WSDL file is required to the binding configuration,
                                                or the 'wsdl' property should be stated as 'disabled' explicitly.");
            }else{
                /*check if wsdl is disabled, 
                if not the wsdl schema will be loaded  */
                if ($msd_config->wsdl != 'disabled') {
                    $path_to_wsdl = SCA_Helper::constructAbsoluteTarget($msd_config->wsdl, $immediate_caller_directory);
                    SCA::$logger->log('The proxy will use wsdl file :'.$path_to_wsdl);

                    $this->mapper = new SCA_Bindings_message_Mapper();
                    try {
                        $this->mapper->setWSDLTypes($path_to_wsdl);
                        //$xmldas = $this->mapper->getXmlDas();
                    } catch( SCA_RuntimeException $se ) {
                        if (substr($path_to_wsdl, 0, 5) == 'http:'
                        && strpos($se->getMessage(), 'SDO_Exception') !== false
                        && strpos($se->getMessage(), 'Unable to parse') !== false
                        && strpos($se->getMessage(), 'Document is empty') !== false) {
                            throw new SCA_RuntimeException('A call to SCA specified a URL: '
                            . $path_to_wsdl
                            . " The document returned was empty. One explanation for this may be apache bug 39662. See http://issues.apache.org/bugzilla/show_bug.cgi?id=36692. You may need to obtain the WSDL in a browser and save it as a local file.");
                        }
                        throw $se ;
                    }
                }
            }

            /*create and config a SAM client*/
            $this->ms_client = new SCA_Bindings_message_SAMClient($this);
            $this->ms_client->config($msd_config);

            SCA::$logger->log('SAM is ready');

        }

        public function __call($method_name, $arguments){
            SCA::$logger->log('entering');
            SCA::$logger->log("method name = $method_name");

            /*do we have a WSDL schema*/
            if ($this->mapper === null) {
                // no, there should be a single prarmeter
                $msgbody = $arguments[0];
            }else{
                // yes, generate XML payload according to the WSDL schema
                try
                {
                    $msgbody = $this->_getXMLPayload($method_name, $arguments);
                }
                catch( SDO_Exception $sdoe )
                {
                    throw new SCA_RuntimeException($sdoe->getMessage());
                }
            }

            /*sending message*/
            if( !$correlid = $this->ms_client->sendRequest($method_name, $msgbody)){
                throw new SCA_RuntimeException("Send Request Failed");
            }

            /*waiting for the response*/
            if ( $this->waitResponseTimeout >=0 ) {
                $msg_response = $this->ms_client->getResponse($correlid,$this->waitResponseTimeout);
                if($msg_response){
                    /*do we have a WSDL schema*/
                    if ($this->mapper === null) {
                        // no, returns message body directly
                        $rc = trim($msg_response->body);
                    }else{
                        // yes, unpackage XML payload and returns a sdo object 
                        $response = $this->mapper->fromXML(trim($msg_response->body));
                        $rc = $response[$method_name . "Return"];
                    }
                }else{
                    throw new SCA_RuntimeException('SAM Error occured when attempting to receive response: '
                                                   .$this->ms_client->getLastError(0));
                }
                return $rc;
            }
            
        }

        /**
         * function that resets the reply-to queue
         * the change will take effect from the next request 
         */ 
        public function setReplyQueue($queue){
            SCA::$logger->log("setting ReplyQueue to $queue");
            $this->ms_client->setResponseQueue($queue);
        }

        /**
         * function that resets the response timeout
         * the change will take effect from the next request
         * 
         * @param int $timeout, new timeout in microseconds
         *                      zero means wait for infinite
         *                      negative value means NOT to expect response.
         */ 
        public function setWaitResponseTimeout($timeout){
            SCA::$logger->log("setting waitResponseTimeout to $timeout");
            $this->waitResponseTimeout = $timeout;
            
        }

        public function addReferenceType($reference_type)
        {
            //SCA::$logger->log('entering');
        }

        public function createDataObject($namespace_uri, $type_name)
        {
            try {
                $xmldas = $this->mapper->getXmlDas();
                $object = $xmldas->createDataObject($namespace_uri, $type_name);
                return  $object;
            } catch( Exception $e ) {
                throw new SCA_RuntimeException($e->getMessage());
            }
        }


        private function _getXMLPayload($method_name, $arguments)
        {
            $xmldas        = $this->mapper->getXmlDas();
            //print ($xmldas);
            $xdoc          = $xmldas->createDocument($method_name);
            $operation_sdo = $xdoc->getRootDataObject();
            $operation_sdo = $this->_copyPositionalArgumentsIntoSdoProperties($operation_sdo, $arguments);
            $str = $xmldas->saveString($xdoc, 2);
            return         $str;
        }

        private function _copyPositionalArgumentsIntoSdoProperties($operation_sdo, $arguments)
        {
            $reflection = new SDO_Model_ReflectionDataObject($operation_sdo);
            $type       = $reflection->getType();
            $i          = 0;
            foreach ($type->getProperties() as $property) {
                $arg                                 = $arguments[$i];
                $operation_sdo[$property->getName()] = is_object($arg)
                ? clone $arg : $arg;
                $i++;
            }
            return $operation_sdo;

        }


    }
}

?>