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

if (! class_exists('SCA_Bindings_restresource_Server', false)) {
    class SCA_Bindings_restresource_Server {

        private $service_wrapper = null;
        private $xml_das         = null;


        public function __construct($wrapper)
        {
            SCA::$logger->log("Entering constructor");
            $this->service_wrapper = $wrapper;
            $this->xml_das         = $wrapper->getXmlDas();
            SCA::$logger->log("Exiting constructor");
        }

        public function handle()
        {
            SCA::$logger->log("Entering");

            // the actions that the rest resource service may perform
            // and extra action "enumerate" is supported when a GET
            // with no resource id submitted
            $actions = array('POST'   => array('create',   1),
                             'GET'    => array('retrieve', 1),
                             'PUT'    => array('update',   2),
                             'DELETE' => array('delete',   1));

            if (array_key_exists($_SERVER['REQUEST_METHOD'], $actions)) {
                $methodWithNumberOfParams = $actions[$_SERVER['REQUEST_METHOD']];
                $method                   = $methodWithNumberOfParams[0];
            } else {
                //TODO find out correct response
                header("HTTP/1.1 404 ");
                echo $_SERVER['REQUEST_METHOD']." Not Supported.";
                return;
            }

            // Dtermine if a resource id is provided in the URL
            // Resource ids take the following form
            //    [REQUEST_URI] => /Samples/SomeScript.php/12
            //    [SCRIPT_NAME] => /Samples/SomeScript.php
            //    [PATH_INFO] => /12
            // Note, if the PATH_INFO is not working, and you are using Apache 2.0,
            // check the AcceptPathInfo directive for php files.
            // See http://httpd.apache.org/docs/2.0/mod/core.html#acceptpathinfo

            //Set $id - works for non-selector style, but not for selector style.
            if (isset($_SERVER['PATH_INFO'])) {
                $param = $_SERVER['PATH_INFO'];

                //strip slash
                $lengthOfParam = strlen($param);
                $id            = substr($param, 1, $lengthOfParam);
            } else {
                $id = null;
                
                // no id is provided so if this is a GET switch the method over to enumerate
                if ($method === "retrieve"){
                    $method = "enumerate";
                }
            }
            
            // TODO - Capture any query parameters passed with the URL

            // Get the request body which is provided when POST is used
            $rawHTTPContents = urldecode(file_get_contents("php://input"));
            SCA::$logger->log("raw http contents = " . $rawHTTPContents);
            
            try {

                //Get the service description for the class.
                $param_description = $this->service_wrapper->getParametersForMethod($method);

                //always give the component an sdo, but handle sdo or xml back from it.
                if ($method === 'create') {
                    SCA::$logger->log("The method is create()");

                    $sdo = SCA_Helper::xmlToSdo($this->xml_das, $rawHTTPContents);
                    
                    // should now have an sdo of the type specified by @param
                    // so add it to the parameter array
                    $params_array = array($sdo);
                   
                    $call_response = null;
                    $call_response = $this->service_wrapper->__call($method, $params_array);

                    if ($call_response !== null) {                  
                        header("HTTP/1.1 201");
                        header("Content-Type: application/plain");
                        header("Location: $call_response");
                        
                        // return a link to the newly created resource
                        // TODO - not sure we need to do this in the body
                        //        as well as the header 
                        echo $call_response;
                    } else {
                        // The content couldn;t be created 
                        header("HTTP/1.1 500");
                    }

                } else if ($method === 'retrieve') {
                    SCA::$logger->log("The method is retrieve()");

                    $call_response = null;

                    SCA::$logger->log("Calling $method on the restresource service wrapper, passing in the id $id");
                    $call_response = $this->service_wrapper->__call($method, $id);

                    SCA::$logger->log("Response from calling the method $method is: $call_response");
                   
                    if ($call_response !== null) {
                        if($call_response instanceof SDO_DataObjectImpl){
                            //if the thing received is an sdo...
                            //convert it to xml
                            $response_xml = SCA_Helper::sdoToXml($this->xml_das, $call_response);
                        }else{
                            $response_xml = $call_response;
                        }

                        header("HTTP/1.1 200");
                        header("Content-Type: application/xml");
                        echo $response_xml;
                    } else {
                        SCA::$logger->log("Call response was null");
                        header("HTTP/1.1 404");
                    }
                } else if ($method === 'update') {
                    SCA::$logger->log("The method is update()");

                    $sdo = SCA_Helper::xmlToSdo($this->xml_das,$rawHTTPContents);
                    //should now have an sdo of the type specified by @param

                    $params_array = array($id, $sdo);

                    $call_response = null;
                    $call_response = $this->service_wrapper->__call($method, $params_array);

                    //TODO: make sure these tests reflect the correct return values.
                    if ($call_response == true) {
                        header("HTTP/1.1 200 OK");
                        //should not be returning a body.
                    } else {
                        header("HTTP/1.1 404");
                        echo "Failed to u[date resource with id ".$id."\n";                        
                    }
                } else if ($method === 'delete') {
                    SCA::$logger->log("The method is delete()");

                    $call_response = null;
                    $call_response = $this->service_wrapper->__call($method, $id);

                    //TODO: make sure these tests reflect the correct return values.
                    if ($call_response === true) {
                        header("HTTP/1.1 200 OK");
                        //should not be returning a body
                     } else {
                        header("HTTP/1.1 404");
                        echo "Failed to delete resource with id ".$id."\n";
                    }
                } else if ($method === 'enumerate') {
                    SCA::$logger->log("The method is enumerate()");

                    $call_response = null;

                    SCA::$logger->log("Calling $method on the restresource service wrapper");
                    $call_response = $this->service_wrapper->__call($method);

                    SCA::$logger->log("Response from calling the method $method is: $call_response");
                   
                    if ($call_response !== null) {
                        if($call_response instanceof SDO_DataObjectImpl){
                            //if the thing received is an sdo...
                            //convert it to xml
                            $response_xml = SCA_Helper::sdoToXml($this->xml_das, $call_response);
                        }else{
                            $response_xml = $call_response;
                        }

                        header("HTTP/1.1 200");
                        header("Content-Type: application/xml");
                        echo $response_xml;
                    } else {
                        SCA::$logger->log("Call response was null");
                        header("HTTP/1.1 500");
                    }                
                
                }
            }
            catch(SCA_ServiceUnavailableException $ex){
                SCA::$logger->log("caught SCA_ServiceUnavailableException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                header("HTTP/1.1 503");
            }
            catch(SCA_ConflictException $ex){
                SCA::$logger->log("caught SCA_ConflictException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                header("HTTP/1.1 409");
            }
            catch(SCA_AuthenticationException $ex){
                SCA::$logger->log("caught SCA_AuthenticationException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                header("HTTP/1.1 407");
            }
            catch(SCA_BadRequestException $ex){
                SCA::$logger->log("caught SCA_BadRequestException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                header("HTTP/1.1 400");
            }
            catch(SCA_InternalServerErrorException $ex){
                SCA::$logger->log("caught SCA_InternalServerErrorException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                header("HTTP/1.1 500");
            }
            catch(SCA_UnauthorizedException $ex){
                SCA::$logger->log("caught SCA_UnauthorizedException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                header("HTTP/1.1 401");
            }
            catch(SCA_MethodNotAllowedException $ex){
                //catch problem finding the method encountered by the service wrapper.
                SCA::$logger->log("caught SCA_MethodNotAllowedException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                header("HTTP/1.1 405");
            }
            catch(SCA_NotFoundException $ex){
                //catch problem finding the reosurce encountered by the service wrapper.
                SCA::$logger->log("caught SCA_NotFoundException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                header("HTTP/1.1 404");
            }            
            catch (SCA_RuntimeException $ex) {

                SCA::$logger->log("Caught SCA_RuntimeException in restresource binding: ".$ex->getMessage()."\n");
                header("HTTP/1.1 500");
            }
            catch (Exception $ex) {
                $call_response['error'] = $ex->getMessage();
                SCA::$logger->log("Found exception in restresource bining: ".$ex->getMessage()."\n");
                header("HTTP/1.1 500");
            }

            return;
        }

        public function __destruct()
        {
        }
    }
}
?>
