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

if (! class_exists('SCA_AtomServer', false)) {
    class SCA_AtomServer {

        private $service_wrapper = null;
        private $xml_das         = null;
        private $input_stream    = "php://input";


        public function __construct($wrapper)
        {

            SCA::$logger->log("Entering constructor");
            //SCA::$logger->log("Wrapper = $wrapper");
            $this->service_wrapper = $wrapper;
            $this->xml_das        = $wrapper->getXmlDas();
            SCA::$logger->log("Exiting constructor");

        }


        public function handle()
        {
            SCA::$logger->log("Entering");

            $actions = array('POST'   => array('create',   1),
            'GET'    => array('retrieve', 1),
            'PUT'    => array('update',   2),
            'DELETE' => array('delete',   1));

            if (array_key_exists($_SERVER['REQUEST_METHOD'], $actions)) {
                $methodWithNumberOfParams = $actions[$_SERVER['REQUEST_METHOD']];
                $method                   = $methodWithNumberOfParams[0];

                SCA::$logger->log("Request received: $method");

            } else {
                //TODO find out correct response
                SCA::sendHttpHeader("HTTP/1.1 404 Not Found");
                echo $_SERVER['REQUEST_METHOD']." Not Supported.";
                return;
            }

            //*handle situations where we have the id in the url.*/

            /**
             These look like the variables to use:
                [REQUEST_URI] => /Samples/Atom/Contact.php/12
                [SCRIPT_NAME] => /Samples/Atom/Contact.php
                [PATH_INFO] => /12
             */

            /*
            * Note, if the PATH_INFO is not working, and you are using Apache 2.0,
            * check the AcceptPathInfo directive for php files.
            * See http://httpd.apache.org/docs/2.0/mod/core.html#acceptpathinfo
            */
            //Set $id - works for non-selector style, but not for selector style.
            if (isset($_SERVER['PATH_INFO'])) {
                $param = $_SERVER['PATH_INFO'];

                //test different length of param
                //$param = "/344656";

                //TODO: is there a case where there will not be a slash in [PATH_INFO]?
                //strip slash
                $lengthOfParam = strlen($param);
                $id            = substr($param, 1, $lengthOfParam);

                SCA::$logger->log("Resource id: $id");

            } else {
                $id = null;
            }

            //TODO should also check at this stage whether we have invalid combos such as PUT with null $id

            // POST
            // Get the request body
            $rawHTTPContents = file_get_contents($this->input_stream);

            SCA::$logger->log("raw http contents = " . $rawHTTPContents);

            try {

                //Get (and check) the service description for the class.
                $param_description =
                $this->service_wrapper->getParametersForMethod($method);


                //NOTE: we always give the component an sdo, but handle sdo or xml back from it.
                if ($method === 'create') {
                    SCA::$logger->log("The method is create()");

                    $sdo = $this->fromXml($rawHTTPContents);
                    //should now have an atom format sdo

                    if(!$sdo instanceof SDO_DataObjectImpl)
                    {
                        SCA::sendHttpHeader("HTTP/1.1 400 Bad Request");
                        echo "Request did not contain valid atom format xml";
                    }


                    SCA::$logger->log("Created an sdo from the xml input: $sdo");

                    $params_array = array($sdo);

                    $call_response = null;

                    $call_response = $this->service_wrapper->__call($method, $params_array);
                    SCA::$logger->log("call response: $call_response");


                    if ($call_response !== null) {

                        if(!($call_response instanceof SDO_DataObjectImpl)){
                            //if the thing received is xml...
                            //convert it to sdo
                            $call_response = $this->fromXml($call_response);
                        }

                        SCA::$logger->log("Call Response Structure: ". print_r($call_response, true));

                        //TODO: consider this - might some atom feeds still try to provide the link as the value of the link element rather than as an href attribute?
                        //$link = $call_response->link[0]->value;


                        //TODO: consider this - there might be a whole bunch of link elements, could there be more than one that has no 'rel' attribute?






                        //if this is not found, $rel will be null
                        //$rel = $call_response->link[1]->rel;
                        //but
                        //can't do this because $rel won't just be null if its not there.
                        //$rel = $link->rel;
                        //if($rel === null){

                        $location = null;
                        foreach($call_response->link as $link){

                            SCA::$logger->log("Found link element: $link");

                            if(isset($link->rel) === false){
                                $location = $link->href;
                            }

                        }

                        //if the location is still null, then there might be a number of reasons why.
                        //Handle the case where the reason is that all the link elements have a rel element. Use the href of first link element by default. TODO: this is not enough: we need a way of detecting the BEST 'rel' to use.
                        if($location === null){
                            SCA::$logger->log("Could not find a link element that did not have a rel attribute so using the first href provided as the resource location.");
                            //if there is no href then $location will still be null.
                            $location = $call_response->link[0]->href;
                        }

                        SCA::$logger->log("Location of resource: $location");

                        //convert response to xml to send back
                        $response = $this->toXml($call_response);

                        //Is the 'false' param required?
                        //It is for sending headers of the same type and
                        //these are different types?
                        SCA::sendHttpHeader("HTTP/1.1 201 Created", false, 201);

                        //Get out the link field and use it instead of the id.
                        //$link = $call_response->link[0]->value;
                        SCA::sendHttpHeader("Location:$location");
                        SCA::sendHttpHeader("Content-Type: application/atom+xml");
                        echo $response;
                    } else {


                        //TODO sort out what we will flow back:
                        //at the moment, only send back the message in the body
                        //if the http code spec says a body can be present.


                        //if the call response is null, then the create() method on the
                        //component did not return the body of the entry to be returned to
                        //the user. This would mean that they have not conformed to the spec
                        //but will it always mean that the create failed?
                        SCA::$logger->log("According to the Atompub Spec, expected create() on the component to return a copy of the successfully created resource but it has returned nothing.");
                        SCA::sendHttpHeader("HTTP/1.1 500 Internal Server Error");
                        //echo "Failed to create resource. \n";
                    }

                } else if ($method === 'retrieve') {
                    SCA::$logger->log("The method is retrieve()");


                    $call_response = null;




                    if($id === null){
                        $method = 'enumerate';
                    }



                    SCA::$logger->log("Calling $method on the Atom service wrapper");


                    //requests come in with the default syntax "http://server/component.php/id"
                    $call_response = $this->service_wrapper->__call($method, $id);

                    SCA::$logger->log("Response from calling the method $method is: $call_response");

                    if ($call_response !== null) {



                        if($call_response instanceof SDO_DataObjectImpl){
                            //if the thing received is an sdo...
                            //convert it to xml
                            $response_sdo = $this->toXml($call_response);
                        }else{
                            $response_sdo = $call_response;
                        }


                        SCA::sendHttpHeader("HTTP/1.1 200 OK");
                        SCA::sendHttpHeader("Content-Type: application/atom+xml");
                        echo $response_sdo;
                    } else {
                        SCA::$logger->log("Call response was null");
                        SCA::sendHttpHeader("HTTP/1.1 500 Internal Server Error");

                    }



                } else if ($method === 'update') {
                    SCA::$logger->log("The method is update()");

                    $sdo = $this->fromXml($rawHTTPContents);

                    $params_array = array($id, $sdo);

                    $call_response = null;

                    $call_response = $this->service_wrapper->__call($method, $params_array);

                    if ($call_response === true) {
                        SCA::$logger->log("The update was successful");
                        SCA::sendHttpHeader("HTTP/1.1 200 OK");
                        //NOTE: should not be returning a body.

                    } else {
                        //TODO find out the right response code
                        SCA::sendHttpHeader("HTTP/1.1 500 Internal Server Error");

                    }




                } else if ($method === 'delete') {
                    SCA::$logger->log("The method is delete()");

                    $call_response = null;

                    $call_response = $this->service_wrapper->__call($method, $id);

                    if ($call_response === true) {
                        SCA::$logger->log("The delete was successful");
                        SCA::sendHttpHeader("HTTP/1.1 200 OK");
                        //should not be returning a body
                    }
                }

            } catch(SCA_ServiceUnavailableException $ex){
                SCA::$logger->log("caught SCA_ServiceUnavailableException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 503 Service Unavailable");
            } catch(SCA_ConflictException $ex){
                SCA::$logger->log("caught SCA_ConflictException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 409 Conflict");
            } catch(SCA_AuthenticationException $ex){
                SCA::$logger->log("caught SCA_AuthenticationException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 407 Proxy Authentication Required");
            } catch(SCA_BadRequestException $ex){
                SCA::$logger->log("caught SCA_BadRequestException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 400 Bad Request");
            } catch(SCA_InternalServerErrorException $ex){
                SCA::$logger->log("caught SCA_InternalServerErrorException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 500 Internal Server Error");
            } catch(SCA_UnauthorizedException $ex){
                SCA::$logger->log("caught SCA_UnauthorizedException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 401 Unauthorized");
            } catch(SCA_NotFoundException $ex){
                //catch problem finding the requested resource thrown by the component
                SCA::$logger->log("caught SCA_NotFoundException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 404 Not Found");
            } catch(SCA_MethodNotAllowedException $ex){
                //catch problem finding the method encountered by the service wrapper.
                SCA::$logger->log("caught SCA_MethodNotAllowedException when calling method $method"); //TODO: log more info, class the method was called on, msg.
                SCA::sendHttpHeader("HTTP/1.1 405 Method Not Allowed");
            } catch (SCA_RuntimeException $ex) {

                SCA::$logger->log("Caught SCA_RuntimeException in AtomServer: ".$ex->getMessage()."\n");
                //TODO: output exceptions correctly.
                SCA::sendHttpHeader("HTTP/1.1 500 Internal Server Error");

            } catch (Exception $ex) {
                $call_response['error'] = $ex->getMessage();
                SCA::$logger->log("Found exception in AtomServer: ".$ex->getMessage()."\n");
                //TODO sort out how we want to do this:
                //at the moment, only send back the message in the body
                //if the http code spec says a body can be present.
                SCA::sendHttpHeader("HTTP/1.1 500 Internal Server Error");
            }

            return;
        }


        //TODO: refactor these methods - also appear in SDO_Typehandler and in AtomProxy
        private function fromXml($xml){
            try{
                $doc = $this->xml_das->loadString($xml);
                $ret = $doc->getRootDataObject();
                return         $ret;
            }
            catch( Exception $e ){
                SCA::$logger->log("Found exception in AtomServer: ".$e->getMessage()."\n");
                return $e->getMessage();

            }
        }

        private function toXml($sdo)
        {

            try{
                //get the type of the sdo eg. 'Contact', to avoid using 'BOGUS'
                $type = $sdo->getTypeName();
                SCA::$logger->log("type is $type");
                $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/Atom1.0.xsd');
                $xdoc   = $xmldas->createDocument('', $type, $sdo);
                $xmlstr = $xmldas->saveString($xdoc);
                return         $xmlstr;
            }
            catch(Exception $e){
                SCA::$logger->log("Found exception in AtomServer: ".$e->getMessage()."\n");
                return $e->getMessage();
            }
        }

        //could take parameters ($input_stream_type, $path) later and manage more types
        public function setInputStream($file_path)
        {
            $this->input_stream = "file://$file_path";
            return;
        }

        public function __destruct()
        {
        }
    }
}
?>
