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
| Author: Graham Charters,                                                    |
|         Matthew Peters,                                                     |
|         Megan Beynon,                                                       |
|         Chris Miller,                                                       |
|         Caroline Maynard,                                                   |
|         Simon Laws                                                          |
+-----------------------------------------------------------------------------+
$Id: SCA_ServiceWrapperJson.php 234945 2007-05-04 15:05:53Z mfp $
*/

/**
 * This class is always called when an incoming soap request is for an SCA component
 * Because we always generate doc/lit wrapped WSDL for SCA components, the incoming request will
 * always have named parameters e.g. ticker => IBM. We need to strip the names off to call the
 * component, i.e. to turn the single array of named parameters into a list of positional parameters.
 * Also need to make the return back into an SDO.
 *
 * This is the opposite of what we do in the RemoteProxy
 */

require 'SDO/DAS/Json.php';

if ( ! class_exists('SCA_ServiceWrapperJson', false) ) {
    class SCA_ServiceWrapperJson
    {

        private $class_name = null;
        private $instance_of_the_base_class = null ;
        private $json_das   = null;

        /**
         * Create the service wrapper for a SCA Component. In the event that the mapping
         * of the SCA Component methods the base_class and xmldas types are set to
         * null.
         *
         * @param string $class_name
         * @param string $wsdl_filename
         */
        public function __construct($instance, $class_name, $wsdl_filename )
        {
            SCA::$logger->log("Entering");
            $this->class_name = $class_name;
            $this->instance_of_the_base_class = $instance;
            SCA::fillInReferences($this->instance_of_the_base_class);
            // create a JSON DAS and populate it with the valid types
            // that this service can create. Not strinctly required
            // for json encoding but the JSON Server uses this
            // DAS for decoding
            $xsds     = SCA_Helper::getAllXsds($class_name);
            $this->json_das = new SDO_DAS_Json();
            foreach ($xsds as $index => $xsds) {
                list($namespace, $xsdfile) = $xsds;
                if (SCA_Helper::isARelativePath($xsdfile)) {
                    $xsd = SCA_Helper::constructAbsolutePath($xsdfile, $class_name);
                    $this->json_das->addTypesXsdFile($xsd);
                }
            }
        }

        public function getJsonDas()
        {
            return $this->json_das;
        }

        public function getParametersForMethod($method_name)
        {
            $reader              = new SCA_AnnotationReader($this->instance_of_the_base_class);
            $service_description = $reader->reflectService();

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
        public function __call($method_name, $arguments)
        {
            SCA::$logger->log("Entering");
            SCA::$logger->log("Going to call $method_name method");


            $return = null;

            try
            {
                $return = call_user_func_array(array(&$this->instance_of_the_base_class, $method_name), $arguments);
            }
            catch ( Exception $e )
            {
               throw new SCA_RuntimeException(
               'Exception thrown in call to ' . $method_name 
               . ":" . $e->__toString());
            }

            // convert the reponse from the message call into something
            // that can be copied  into a JSON result string by the
            // JSON Server
            if ( $return == null ) {
                $response_object = "null";
            } else if ( is_object($return) ) {
                $response_object = $this->json_das->encode($return);
            } else if ( is_array($return) ) {
                throw new SCA_RuntimeException("Return from method $method_name of type array found. " .
                "Return types must be either primitives or SDOs");
            } else {
                $response_object = json_encode($return);
            }

            return $response_object;

        }
    }
}
?>