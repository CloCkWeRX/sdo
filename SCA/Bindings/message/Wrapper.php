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

if (! class_exists('SCA_Bindings_message_Wrapper', false)) {

    class SCA_Bindings_message_Wrapper
    {
        private $instance_of_the_base_class = null ;
        private $service_description        = null ;
        private $mapper                     = null ;

        public function __construct($class_name, $service_description, $mapper)
        {
            SCA::$logger->log('Entering');
            SCA::$logger->log("class name = $class_name");

            $this->mapper                    = $mapper;
            $this->service_description        = $service_description ;
            $this->instance_of_the_base_class = SCA::createInstance($class_name);
            SCA::fillInReferences($this->instance_of_the_base_class);

            SCA::$logger->log('Exiting');
        }

        /**
         * This method provides call back point to the message listener
         * on the events of incoming message.
         * 
         * And pass the call on to the business method. 
         * just like the __call() method in other binding's wrapper class
         * 
         */
        public function onMessage($msg)
        {
            SCA::$logger->log('Entering') ;
            $method_name = trim($this->_getMethodName($msg));
            SCA::$logger->log("method name = $method_name.");

            /* test the availability of the target method*/
            if (array_key_exists($method_name,$this->service_description->operations)){
                $target_operation = $this->service_description->operations[$method_name];
            }else{
                throw new SCA_RuntimeException(
                "Operation ".$method_name." is undefined in the target service component");
            }

            $arguments = $this->_getParametersArray($msg);
            
            try
            {
                $return = call_user_func_array(
                    array(&$this->instance_of_the_base_class, $method_name), 
                    $arguments);
            }
            catch ( Exception $e )
            {
               throw new SCA_RuntimeException(
               'Exception thrown in call to ' . $method_name . ":" . $e->__toString());
            }

            if (!is_null($return)) {
                if($this->mapper === null){
                    //XML not used, assume $return is simple type.
                    $response_msg = $return;
                }else{
                    //generate XML Payload 
                    $xmldas = $this->mapper->getXmlDas();
                    $xdoc = $xmldas->createDocument($method_name . "Response");
                    $response_object = $xdoc->getRootDataObject();
                    $response_object[$method_name."Return"] = is_object($return)
                                                                ? clone $return
                                                                : $return;
                    $response_msg = $xmldas->saveString($xdoc, 2);
                }
            }else{
                return;
            }
            
            return $response_msg;
        }

        /*
         * According to the OSOA specification, the following are rules for operation selection
         * 
         * 1. if there is only one method in the class, that method is selected
         * 2. otherwise, if JMS user property 'scaOperationName' is present, that method is selected
         * 3. otherwise, method named 'onMessage' will be called
         *
         */
        private function _getMethodName($msg){
            $operations = $this->service_description->operations;
            if (sizeof($operations) == 1) {
                return key($operations);
            }

            if (isset($msg->header->scaOperationName)) {
                return $msg->header->scaOperationName;
            }

            return "onMessage";
        }

        /**
         * Unpackage message body.
         */
        private function _getParametersArray($msg){
            if($this->mapper === null){
                //XML not used
                $params_array[] = trim($msg->body);
            }else{
                $params_sdo = $this->mapper->fromXML(trim($msg->body));
                $params_array = array();
                foreach($params_sdo as $param) {
                    $params_array[] = $param;
                }
            }
            return $params_array;
        }

        
    }
}
?>