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
| Author: Rajini Sivaram, Simon Laws                                          |
+-----------------------------------------------------------------------------+
$Id: Wrapper.php 234945 2007-05-04 15:05:53Z mfp $
*/

/**
 * This class is called when an incoming xml-rpc request is for an SCA component
 *
 */
require_once "SCA/Bindings/xmlrpc/Das.php";
require_once "SCA/Bindings/xmlrpc/ServiceDescriptionGenerator.php";


class SCA_Bindings_Xmlrpc_Wrapper {

    private $class_name  = null;
    private $instance_of_the_base_class  = null ;
    private $xmlrpc_das  = null;
    private $annotations = null;
    private $method_aliases = array();
    private $xmlrpc_server;

    /**
     * Create the service wrapper for a SCA Component.
     *
     * @param string $class_name
     * @param string $wsdl_filename
     */
    public function __construct($instance, $class_name, $wsdl_filename )
    {
            SCA::$logger->log("Wrapper $class_name");

            $this->class_name = $class_name;
            $this->instance_of_the_base_class = $instance;
            SCA::fillInReferences($this->instance_of_the_base_class);
            $this->annotations= new SCA_AnnotationReader($this->instance_of_the_base_class);

            // create an XML-RPC DAS and populate it with the valid types
            // that this service can create.
            $xsds     = SCA_Helper::getAllXsds($class_name);
            $this->xmlrpc_das = new SCA_Bindings_Xmlrpc_DAS();
            foreach ($xsds as $index => $xsds) {
                list($namespace, $xsdfile) = $xsds;
                if (SCA_Helper::isARelativePath($xsdfile)) {
                    $xsd = SCA_Helper::constructAbsolutePath($xsdfile, $class_name);
                    $this->xmlrpc_das->addTypesXsdFile($xsd);
                }
            }

            $this->xmlrpc_server   = xmlrpc_server_create();
            $service_description = $this->annotations->reflectService();
            $serviceDescGen = new SCA_Bindings_Xmlrpc_ServiceDescriptionGenerator();
            $serviceDescGen->addIntrospectionData($this->xmlrpc_server, $service_description,
                                                  $this->method_aliases, $this->xmlrpc_das);

    }

    /**
     * Free resources allocated by this service wrapper
     *
     */
    public function __destruct() {
        xmlrpc_server_destroy($this->xmlrpc_server);
    }

    /**
     * Transform parameters of method to SDOs
     *
     * @param string $method_name (Method name, used to obtain method signature from annotations)
     * @param array  $params (Array of params returned by xmlrpc_decode)
     * @return array Array of params encoded as SDOs
     */
    private function transformParamsToSdo($method_name, $params) {


        // create an array to hold the new params
        // - numbers / strings / booleans -  remain as PHP objects
        // - objects/arrays - become SDOs, we obtain the type of each param from annotations
        $new_params_array = array();

        //get the list of parameter types
        $service_description = $this->annotations->reflectService();

        if (isset($service_description->operations) &&
            array_key_exists($method_name, $service_description->operations) &&
            array_key_exists("parameters", $service_description->operations[$method_name])) {

            $parameter_descriptions =  $service_description->operations[$method_name]["parameters"];
        }

        foreach ( $params as $param_name => $param_value ) {


            if (isset($parameter_descriptions) && array_key_exists($param_name, $parameter_descriptions))
                $param_description = $parameter_descriptions[$param_name];

            $param_type        = gettype($param_value);


            if ( $param_type == "object" || $param_type == "array") {
                if ( isset($param_description) &&
                     array_key_exists('objectType', $param_description) &&
                     array_key_exists('namespace', $param_description)) {

                    $new_params_array[] = $this->xmlrpc_das->decodeFromPHPArray($param_value,
                    $param_description["namespace"],
                    $param_description["objectType"]);

                } else {

                    $new_params_array[] = $this->xmlrpc_das->decodeFromPHPArray($param_value);

                    // throw new SCA_RuntimeException("Method $method_name parameter $param_name appears " .
                    // "to be an SDO but doesn't have a suitable " .
                    // "@param description element as the type and " .
                    // "namespace are not both defined");
                }

            } else {
                //  numbers / strings / booleans
                $new_params_array[] = $param_value;
            }
        }

        return $new_params_array;

    }


    /**
     * Pass the call on to the business method in the component
     *
     * Transform arguments to SDOs before call, and
     * encode the return value to send back.
     *
     * @param string $method_name
     * @param string $arguments
     * @return string Response to call encoded using XMLRPC
     */
    public function __call($method_name, $arguments)
    {
        SCA::$logger->log("call $method_name");

        if (array_key_exists($method_name, $this->method_aliases)) {
            $method_name = $this->method_aliases[$method_name];
        }

        $return = null;
        $new_params = $this->transformParamsToSdo($method_name, $arguments);

        if (method_exists($this->instance_of_the_base_class, $method_name)) {

            $return = call_user_func_array(array(&$this->instance_of_the_base_class, $method_name), $new_params);

        } else {
            $return["faultCode"] = 32601;
            $return["faultString"] = "Method '{$method_name}' not found in class {$this->class_name}";
        }

        $response_object = xmlrpc_encode_request(null, $return);
        return $response_object;

    }




    /**
     * Call an XMLRPC system method. Use XMLRPC extension to handle the system method.
     * In normal SCA operation, this method is used for system.describeMethods whenever
     * a Proxy is created. Use annotations to register methods on the server first.
     *
     * @param string $rawHTTPContents
     * @return string XMLRPC encoded response
     */
    public function callSystemMethod($rawHTTPContents) {

        SCA::$logger->log("callSystemMethod $rawHTTPContents");

        $response = xmlrpc_server_call_method($this->xmlrpc_server, $rawHTTPContents, null);

        SCA::$logger->log("callSystemMethod $response");

        return $response;
    }


}
