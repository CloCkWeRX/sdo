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
 * This class handles incoming Atom requests.
 */

require "SCA/SCA_Exceptions.php";


if ( ! class_exists('SCA_ServiceWrapperAtom', false)) {
    class SCA_ServiceWrapperAtom {

        private $class_name = null;
        private $instance_of_the_base_class = null;
        private $xml_das = null;

        /**
         * Create the service wrapper for a SCA Component. In the event that the 
         * mapping of the SCA Component methods the base_class and xmldas types are 
         * set to null.
         * 
         * @param string $class_name
         * @param string $wsdl_filename
         */
        public function __construct($class_name)
        {
            SCA::$logger->log("Entering constructor");
            SCA::$logger->log("class_name = $class_name");

            $this->class_name = $class_name;
            $this->instance_of_the_base_class = SCA::createInstance($class_name);
            SCA::fillInReferences($this->instance_of_the_base_class);

            //need an xmldas
            //want to have the xsds in here. do xmldas only here then do add types.
            $this->xml_das = self::getXmldasForAtom($class_name, "");

            SCA::$logger->log("Exiting Constructor");

        }/* End service wrapper constructor  */

        //TODO: refactor this back into getXmldas - should just be able to load the atom schema if the namespace and type are http://www.w3.org/2005/Atom and entryType. This needs some thought though as the namespace could change, and someone else might use the type name entryType in a non-atom schema
        public static function getXmldasForAtom($class_name, $namespace_uri)
        {

            SCA::$logger->log("Entering");

            $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/Atom1.0.xsd');
            // expect to find xsds along with the SCA code - automatically loads xhtml1.xsd and xml.xsd as part of this.

            // TODO examine this code again
            // one might imagine that what is wanted is a map:
            // array(namespace => Set Of xsd)
            // but this is not what we have
            // Code Analyser correctly picks up a number
            // of strangenesses
            $xsds   = SCA_Helper::getAllXsds($class_name);
            //$atomhelperlog->log("SCA_ServiceWrapperAtom::getXmldasForAtom() Just tried to find xsds in the component: ". print_r($xsds, true) ." \n");

            //$xmldas = SDO_DAS_XML::create();
            foreach ($xsds as $index => $xsds) {
                list($namespace, $xsdfile) = $xsds;
                if (SCA_Helper::isARelativePath($xsdfile)) {
                    $xsd = SCA_Helper::constructAbsolutePath($xsdfile, $class_name);
                    $xmldas->addTypes($xsd);
                }
            }

           // $atomhelperlog->log("SCA_ServiceWrapperAtom::getXmldasForAtom() xmldas after adding other types from the class:: $xmldas \n");
            SCA::$logger->log("Exiting");

            return $xmldas;
        }


        public function getXmlDas()
        {
            return $this->xml_das;
        }

        public function getParametersForMethod($method_name)
        {
            $reader              = new SCA_AnnotationReader($this->instance_of_the_base_class);
            $service_description = $reader->reflectService();

            //$this->atomservicewrapperlog->log("SCA_ServiceWrapperAtom::__call() - what does the service desc eval look like when method_name is $method_name ?...".$service_description["operations"][$method_name]."\n");

            //$this->atomservicewrapperlog->log("SCA_ServiceWrapperAtom::__call() - what does the service_desc look like?".print_r($service_description, true)."\n");

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
            //trigger_error("about to call method $method_name on $this->class_name\n");

            $return = null;
            //allowing null so that method that takes no args can be called

            //$this->atomservicewrapperlog->log("SCA_ServiceWrapperAtom::__call() - about to call the method\n");

            //trigger_error("About to do call_user_func_array\n");
            SCA::$logger->log("About to do call_user_func_array with method $method_name and arguments $arguments \n");
            $return = call_user_func_array(array(&$this->instance_of_the_base_class,
            $method_name), $arguments);
            SCA::$logger->log("Got return back from call_user_func_array: $return");


            SCA::$logger->log("Exiting __call");

            return $return;
        }/* End of call function                                                  */

    }/* End Service Wrapper class                                                 */

}/* End instance check                                                            */
?>