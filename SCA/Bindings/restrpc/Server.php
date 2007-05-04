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
| Author: Graham Charters,                                             |
|         Matthew Peters,                                              |
|         Megan Beynon,                                                |
|         Chris Miller,                                                |
|         Caroline Maynard,                                            |
|         Simon Laws                                                   |
+----------------------------------------------------------------------+
*/

require "SCA/SCA_Exceptions.php";

if (! class_exists('SCA_Bindings_restrpc_Server', false)) {
    class SCA_Bindings_restrpc_Server {

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

            try {
                // get the name of the method to call from the URL
                $method = $_SERVER['PATH_INFO'];
            
                // strip the forward slash from the beginning of this string
                $length_of_method = strlen($method);
                $method           = substr($method, 1, $length_of_method);
                SCA::$logger->log("The method is $method");
                
                //Get the service description for the class.
                $param_description = $this->service_wrapper->getParametersForMethod($method);
                
                $verb = $_SERVER['REQUEST_METHOD'];

                 if ($verb === 'POST') {
                    SCA::$logger->log("Processing POST");
                    
                    // decide what the input type is and pull out the
                    // parameters accordingly

                    $params_array = null;
                    SCA::$logger->log("Content type = " . $_SERVER['CONTENT_TYPE']);
                    
                    if (stripos($_SERVER['CONTENT_TYPE'], 'x-www-form-urlencoded')){
                        // form input
                        SCA::$logger->log("Form parameters found");                        
                        $params_array = $this->fromRequest($_POST, $param_description);      
                    } else if (stripos($_SERVER['CONTENT_TYPE'], "xml")){
                        //XML input
                        SCA::$logger->log("XML Input found");
                        // Get the request body
                        $rawHTTPContents = file_get_contents("php://input");
                        SCA::$logger->log("raw http contents = " . $rawHTTPContents);
                        $params_array = $this->fromXML($rawHTTPContents);
                    } else {
                        // if no form params or xml provided we assume
                        // no parameters are required
                        SCA::$logger->log("No parameters found");
                    }
                    
                    $call_response = null;
                    $call_response = $this->service_wrapper->__call($method, $params_array);


                } else if ($verb === 'GET') {
                    SCA::$logger->log("Processing GET");
                    
                    $params_array = $this->fromRequest($_GET, $param_description);                          

                    $call_response = null;
                    $call_response = $this->service_wrapper->__call($method, $params_array);

                } 

                SCA::sendHttpHeader("HTTP/1.1 200");
                    
                if ($call_response !== null) {

                    if($call_response instanceof SDO_DataObjectImpl){
                        //if the thing received is SDO convert it to XML
                        SCA::sendHttpHeader("Content-Type: text/xml");                            
                        echo $this->toXml($call_response);
                    } else {
                        SCA::sendHttpHeader("Content-Type: text/plain");
                        echo $call_response;
                    }
                }
            }
            catch(SCA_ServiceUnavailableException $ex){
                SCA::$logger->log("caught SCA_ServiceUnavailableException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 503");
            }
            catch(SCA_ConflictException $ex){
                SCA::$logger->log("caught SCA_ConflictException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 409");
            }
            catch(SCA_AuthenticationException $ex){
                SCA::$logger->log("caught SCA_AuthenticationException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 407");
            }
            catch(SCA_BadRequestException $ex){
                SCA::$logger->log("caught SCA_BadRequestException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                header("HTTP/1.1 400");
            }
            catch(SCA_InternalServerErrorException $ex){
                SCA::$logger->log("caught SCA_InternalServerErrorException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 500");
            }
            catch(SCA_UnauthorizedException $ex){
                SCA::$logger->log("caught SCA_UnauthorizedException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 401");
            }
            catch(SCA_MethodNotAllowedException $ex){
                //catch problem finding the method encountered by the service wrapper.
                SCA::$logger->log("caught SCA_MethodNotAllowedException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 405");
            }
            catch (SCA_RuntimeException $ex) {

                SCA::$logger->log("Caught SCA_RuntimeException in restrpc server: ".$ex->getMessage()."\n");
                //TODO: output exceptions correctly.
                SCA::sendHttpHeader("HTTP/1.1 500");
            }
            catch (Exception $ex) {
                $call_response['error'] = $ex->getMessage();
                SCA::$logger->log("Found exception in restrpc server: ".$ex->getMessage()."\n");
                //TODO sort out how we want to do this:
                //at the moment, only send back the message in the body
                //if the http code spec says a body can be present.
                SCA::sendHttpHeader("HTTP/1.1 500");
            }

            return;
        }


        //TODO: refactor these methods 
        
        /**
         * extract an array of simply type parameters from the incoming 
         * POST or GET request
         */
        private function fromRequest($request, $parameter_descriptions){

            $param_array = array();
            
            $request_string = "";
            foreach ( $request as $param_name => $param_value ) {
                $request_string    = $request_string . 
                                     "  Name=" . $param_name .
                                     " Value=" . $param_value;
                
                // TODO - fix this. It's not used at the moment and doesn't work
                // when the values are passed from the proxy because the type names
                // are made up and don't match the service description.                     
                //$param_description = $parameter_descriptions[$param_name];
                
                $param_type        = gettype($param_value);

                // TODO - I don't do any checking that the params that have been 
                // passed in actually match the params required. 
                $param_array[] = $param_value;
            }
            
            SCA::$logger->log($request_string);                        
            
            return $param_array;
        }
        
        private function fromXml($xml){
            try{
                $doc = $this->xml_das->loadString($xml);
                $sdo = $doc->getRootDataObject();
                SCA::$logger->log("Created an sdo from the xml input: $sdo");
                $param_array = array($sdo);
                return $param_array;
            }
            catch( Exception $e ){
                SCA::$logger->log("Found exception in SCA_Binidings_restrpc_Server ".
                                  "convertind xml to sdo: ".
                                  $e->getMessage()."\n");
                return $e->getMessage();
            }
        }

        private function toXml($sdo)
        {
            try{
                $type   = $sdo->getTypeName();
                $xdoc   = $this->xml_das->createDocument('', $type, $sdo);
                $xmlstr = $this->xml_das->saveString($xdoc);
                return $xmlstr;
            }
            catch(Exception $e){
                SCA::$logger->log("Found exception in SCA_Binidings_restrpc_Server ".
                                  "converting sdo to xml".
                                  $e->getMessage()."\n");
                return $e->getMessage();
            }
        }

        public function __destruct()
        {
        }
    }
}
?>
