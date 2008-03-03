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

if ( ! class_exists('SCA_Bindings_restresource_Wrapper', false)) {
    class SCA_Bindings_restresource_Wrapper {

        private $class_name     = null;
        private $class_instance = null;
        private $xml_das        = null;

        /**
         * Create the service wrapper for an SCA Component. In the event that the 
         * mapping of the SCA Component methods the base_class and xmldas types are 
         * set to null.
         * 
         * @param string $class_name
         */
        public function __construct($class_name)
        {
            //TODO: get rid of the wsdl filename here
            SCA::$logger->log("Entering constructor");
            SCA::$logger->log("class_name = $class_name");

            $this->class_name     = $class_name;
            $this->class_instance = SCA::createInstance($class_name);
            SCA::fillInReferences($this->class_instance);

            // Get an xmldas to handle the SDOs passing in and 
            // out of the wrapped service. This call creates the das
            // and adds all of the service types to it.
            $this->xml_das = SCA_Helper::getXmldas($class_name, "");

            SCA::$logger->log("Exiting Constructor");

        }/* End service wrapper constructor  */


        public function getXmlDas()
        {
            return $this->xml_das;
        }

        public function getParametersForMethod($method_name)
        {
            $reader              = new SCA_AnnotationReader($this->class_instance);
            $service_description = $reader->reflectService();

            $operations = $service_description->operations;

            if(!array_key_exists($method_name, $operations)){
                throw new SCA_MethodNotAllowedException("Method not allowed.");
            }

            return $service_description->operations[$method_name]["parameters"];
        }

        /**
         * Pass the call on to the business method in the component
         *
         * Unwrap the arguments first e.g. when the argument array is array('ticker' =. 'IBM')
         * pull off the name part to make it array('IBM')
         * Then pass to the method
         * Then wrap the return value back into an SDO. The element name is ...Response with a
         * property ...Return which contains the return value.
		*/
        public function __call($method_name, $arguments=null)
        {
            SCA::$logger->log("Entering __call");
            SCA::$logger->log("about to call method $method_name on $this->class_name");

            $return = null;
            //allowing null so that method that takes no args can be called

            SCA::$logger->log("About to do call_user_func_array with method $method_name and arguments $arguments \n");
            $return = call_user_func_array(array(&$this->class_instance,
                                                 $method_name), 
                                           $arguments);
            SCA::$logger->log("Got return back from call_user_func_array: $return");

            SCA::$logger->log("Exiting __call");

            return $return;
        }/* End of call function                                                  */

    }/* End Service Wrapper class                                                 */

}/* End instance check                                                            */
?>