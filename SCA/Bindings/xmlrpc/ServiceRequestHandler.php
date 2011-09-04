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
|         Simon Laws,                                                         |
|         Rajini Sivaram                                                      |
+-----------------------------------------------------------------------------+
$Id: ServiceRequestHandler.php 234945 2007-05-04 15:05:53Z mfp $
*/

require "SCA/Bindings/xmlrpc/Wrapper.php";
require "SCA/Bindings/xmlrpc/Server.php";

if ( ! class_exists('SCA_Bindings_Xmlrpc_ServiceRequestHandler', false) ) {

    class SCA_Bindings_Xmlrpc_ServiceRequestHandler
    {
        public function handle($calling_component_filename, $service_description)
        {
            SCA::$logger->log("Entering");
            SCA::$logger->log( "_handleXmlRpcRequest - $calling_component_filename\n" ) ;

            try {

                $wsdl_filename = null;
               
                // create a wrapper, which in turn creates a service
                // instance and fills in all of the references
                $class_name    = SCA_Helper::guessClassName($calling_component_filename);
                $instance        = SCA::createInstance($class_name);
                $service_proxy = new SCA_Bindings_Xmlrpc_Wrapper($instance, $class_name, $wsdl_filename);

                // create the xmlrpc server that will process the input message
                // and generate the result message
                $xmlrpc_server = new SCA_Bindings_Xmlrpc_Server($service_proxy);

                // handle the current request
                $xmlrpc_server->handle();

            } catch ( Exception $ex ) {
                // A catch all exception just in case something drastic goes wrong
                // This can often be the case in constructors of the XML infrastructure
                // classes where XMLRPC info can be read over remote connections. We
                // still want to send a sensible error back to the client


                $response = "{\"error\":\"" . $ex->getMessage();
                $response = $response . "\",\"version\":\"1.0\"}";

                // some debugging
//                file_put_contents("xmlrpc_messages.txt",
//                "Response at XMLRPC server = " . $response . "\n",
//                FILE_APPEND);

                header('Content-type: text/xml');

                echo $response;
            }
            return ;
        }
    }
}

?>
