<?php
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                  |
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
| Author: Rajini Sivaram, Simon Laws                                   |
+----------------------------------------------------------------------+
$Id$
*/

if ( ! class_exists('SCA_Bindings_Xmlrpc_Server', false) ) {
    class SCA_Bindings_Xmlrpc_Server {

        private $service_wrapper = null;

        /**
         * Constructor - create a XML RPC server to call the
         *               provided service wrapper
         */
        public function __construct($wrapper)
        {
            $this->service_wrapper = $wrapper;

        }


        /**
         * handle incoming XML RPC requests
         */
        public function handle()
        {
            try {

                // Get the XMLRPC request (raw POST data)
                $rawHTTPContents = file_get_contents("php://input");

                // some debugging
//                file_put_contents("xmlrpc_messages.txt",
//                              "Request at XML-RPC server = " . $rawHTTPContents ."\n",
//                              FILE_APPEND);

                // decode xmlrpc request
                $params = xmlrpc_decode_request($rawHTTPContents, $method);

                SCA::$logger->log("Request $rawHTTPContents");
                SCA::$logger->log("handle request $method");

                if ($method == null || strlen($method) == 0) {
                
                    $error["faultCode"] = 32600;
                    $error["faultString"] = "Invalid XML-RPC request : $rawHTTPContents";
                    $response = xmlrpc_encode_request(null, $error);

                } else if (strpos($method, "system.") === 0) {
    
                    $response = $this->service_wrapper->callSystemMethod($rawHTTPContents);

                    // some debugging
//                    file_put_contents("xmlrpc_messages.txt",
//                                  "Response at XML-RPC server = " . $response . "\n",
//                                  FILE_APPEND);


                } else {


                    $response = $this->service_wrapper->__call($method, $params);
                }
            } catch ( Exception $ex ) {

                $error["faultCode"] = $ex instanceof SCA_RuntimeException ? 32000 : 32500;
                $error["faultString"] = $ex->__toString();
                $response = xmlrpc_encode_request(null, $error);
            }


            // some debugging
//            file_put_contents("xmlrpc_messages.txt",
//                              "Response at XML-RPC server = " . $response . "\n",
//                              FILE_APPEND);

            header('Content-type: text/xml');
            echo $response;

            return;
        }



        public function __destruct()
        {
        }
    }

}

?>
