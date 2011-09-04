<?php
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006, 2007.                            |
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+
|                                                                      |
| Licensed under the Apache License, Version 2.0 (the "License"); you  |
| may not use this file except in compliance with the License. You may |
| obtain a copy of the License at                                      |
| http://www.apache.org/licenses/LICENSE-2.0                           |
|                                                                      |
| Unless required by applicable law or agreed to in writing, software  |
| distributed under the License is distributed on an "AS IS" BASIS,    |
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
| implied. See the License for the specific language governing         |
| permissions and limitations under the License.                       |
+----------------------------------------------------------------------+
| Author: SL                                                           |
+----------------------------------------------------------------------+
$Id: SCA_JsonRpcServer.php 234945 2007-05-04 15:05:53Z mfp $
*/

/**
 * SCA JSON-RPC server
 * 
 * This class provides a server side implementation of the JSON-RPC
 * procol specified at (http://json-rpc.org/wiki/specification). This
 * server is used when binding.jsonrpc is applied to an SCA @Servier
 * annotation and processes JSON-RPC protocol messages on behalf 
 * of the SCA runtime.
 * 
 */

if ( ! class_exists('SCA_JsonRpcServer', false) ) {
    class SCA_JsonRpcServer {
    
        // can be set by tests to inject a test request message
        public static $test_request = null;

        private $service_wrapper = null;

        /**
         * Constructor - create a JSON RPC server to call the 
         *               provided service wrapper
         */
        public function __construct($wrapper)
        {
            $this->service_wrapper = $wrapper;
        }

        /**
         * A static function that returns a PHP object representing the JSON request
         */
        static public function getRequest()
        {
            // Need to decide where whether this is POST or GET style

            // GET
            // TODO Do we need to support GET style parameter passing?

            // POST
            // Get the JSON request as an SDO
            // Should really be using a schema for the JSON call here
            if (self::$test_request == null){
                $rawHTTPContents = file_get_contents("php://input");
            } else {
                $rawHTTPContents = self::$test_request;
            }

            // some debugging
            //            file_put_contents("json_messages.txt",
            //            "Request at JSONRPC server = " . $rawHTTPContents ."\n",
            //            FILE_APPEND);
            SCA::$logger->log("Request at JSONRPC server = " . $rawHTTPContents);

            // decode json string into a php object
            $request = json_decode($rawHTTPContents);

            return $request;
        }

        /**
         * handle incomming JSON RPC requests
         */
        public function handle()
        {
            $request = self::getRequest();

            // get the jsonrpc element from the request
            $method = $request->method;
            $params = $request->params;
            $id     = $request->id;

            // create an object that holds the params
            // - numbers / strings / booleans -  remain as PHP objects
            // - objects - become SDOs, we obtain the type of each param from annotations
            // - arrays are invalid at the top level - params must be primitive or SDO
            $new_params_array = array();

            //get the list of parameter types
            $parameter_descriptions = $this->service_wrapper->getParametersForMethod($method);

            foreach ( $params as $param_name => $param_value ) {

                $param_description = $parameter_descriptions[$param_name];
                $param_type        = gettype($param_value);

                /* some debug
                ob_start();
                $debug =  " ParamName: "  . $param_name . " ParamValue: " . $param_value . " ParamType: "  . $param_type . "\n";
                echo $debug;
                print_r( $param_description );
                $debug = ob_get_contents();
                ob_end_clean();
                file_put_contents("json_messages.txt",
                $debug,
                FILE_APPEND);
                */

                if ( $param_type == "object" ) {
                    if ( array_key_exists('objectType', $param_description) &&
                    array_key_exists('namespace', $param_description) ) {
                        $new_params_array[] = $this->service_wrapper->getJsonDas()->decodeFromPHPObject($param_value,
                        $param_description["objectType"],
                        $param_description["namespace"]);
                    } else {
                        throw new SCA_RuntimeException("Method $method parameter $param_name appears " .
                        "to be and SDO but doesn't have a suitable " .
                        "@param description element as the type and " .
                        "namespace are not both defined");
                    }
                } else if ( $param_type == "array" ) {
                    throw new SCA_RuntimeException("Parameter $param_name of type array found as " .
                    "a top level parameter of incoming message $rawHTTPContents " .
                    " message parameters must be either primitives or SDOs");
                } else {
                    $new_params_array[] = $param_value;
                }
            }

            $response = "{\"id\":" . $id . ",";
            /*
            try {
            $call_response = $this->service_wrapper->__call($method, $new_params_array);
            $response      = $response . "\"result\":" . $call_response;
            }
            catch ( Exception $ex ) {
            $response     = $response . "\"error\":" . json_encode($ex->getMessage());
            }
            */

            $call_response  = "null";
            $error_response = "null";

            try {
                $call_response = $this->service_wrapper->__call($method, $new_params_array);
            } catch ( Exception $ex ) {
                $error_response = json_encode($ex->getMessage());
            }

            $response = $response . "\"result\":" . $call_response;
            $response = $response . ",\"error\":" . $error_response;
            $response = $response . ",\"version\":\"1.0\"}";

            // some debugging
            //            file_put_contents("json_messages.txt",
            //            "Response at JSONRPC server = " . $response . "\n",
            //            FILE_APPEND);
            SCA::$logger->log("Response at JSONRPC server = " . $response);

            SCA::sendHttpHeader('Content-type: application/json-rpc');
            echo $response;

            return;
        }
    }
}
?>
