<?php
/*
 * +-----------------------------------------------------------------------------+
 * | (c) Copyright IBM Corporation 2006, 2007.                                   |
 * | All Rights Reserved.                                                        |
 * +-----------------------------------------------------------------------------+
 * | Licensed under the Apache License, Version 2.0 (the "License"); you may not |
 * | use this file except in compliance with the License. You may obtain a copy  |
 * | of the License at -                                                         |
 * |                                                                             |
 * |                   http://www.apache.org/licenses/LICENSE-2.0                |
 * |                                                                             |
 * | Unless required by applicable law or agreed to in writing, software         |
 * | distributed under the License is distributed on an "AS IS" BASIS, WITHOUT   |
 * | WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.            |
 * | See the License for the specific language governing  permissions and        |
 * | limitations under the License.                                              |
+-----------------------------------------------------------------------------+
| Author: Graham Charters,                                                    |
|         Matthew Peters,                                                     |
|         Megan Beynon,                                                       |
|         Chris Miller,                                                       |
|         Caroline Maynard,                                                   |
|         Simon Laws                                                          |
+-----------------------------------------------------------------------------+
$Id: ServiceRequestHandler.php 234945 2007-05-04 15:05:53Z mfp $
*/

require_once "SCA/Bindings/jsonrpc/SCA_ServiceWrapperJson.php";
require_once "SCA/Bindings/jsonrpc/SCA_JsonRpcServer.php";
require_once 'SCA/Bindings/jsonrpc/SCA_GenerateSmd.php';



class SCA_Bindings_Jsonrpc_ServiceRequestHandler
{
    /**
     * Handle
     *
     * @param string $calling_component_filename Filename
     * @param string $service_description        Service description
     *
     * @return mixed
     */
    public function handle($calling_component_filename, $service_description)
    {
        SCA::$logger->log("Entering");
        try {
            $smd_filename = str_replace('.php', '.smd', $calling_component_filename);

            if (!file_exists($smd_filename)) {
                file_put_contents($smd_filename,
                SCA_GenerateSmd::generateSmd($service_description));
                //                    ,self::generateSMD($calling_component_filename)
                //                   );
            }

            $wsdl_filename = null;

            // create a wrapper, which in turn creates a service
            // instance and fills in all of the references
            $class_name      = SCA_Helper::guessClassName($calling_component_filename);
            $instance        = SCA::createInstance($class_name);
            $service_wrapper = new SCA_ServiceWrapperJson($instance, $class_name, $wsdl_filename);

            // create the jsonrpc server that will process the input message
            // and generate the result message
            $jsonrpc_server = new SCA_JsonRpcServer($service_wrapper);
            $jsonrpc_server->handle();
        } catch (Exception $ex) {
            // A catch all exception just in case something drastic goes wrong
            // This can often be the case in constructors of the JSON infsatructure
            // classes where SMD files can be read over remote connections. We
            // still want to send a sensible error back to the client

            // TODO extend the JSON service to expose this kind of function
            //      through public methods
            $response = "{\"id\":\"";
            $response = $response . SCA_JsonRpcServer::getRequest()->id;
            $response = $response . "\",\"error\":\"" . $ex->getMessage();
            $response = $response . "\",\"version\":\"1.0\"}";
            // TODO what to do about id? We can catch errors before the
            //      input is parsed

            // some debugging
            //                file_put_contents("json_messages.txt",
            //                "Response at JSONRPC server = " . $response . "\n",
            //                FILE_APPEND);

            header('Content-type: application/json-rpc');

            echo $response;
        }
        return;
    }
}
