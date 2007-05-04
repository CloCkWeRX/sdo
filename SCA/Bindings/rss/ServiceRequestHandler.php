<?php

include 'SCA/Bindings/rss/Wrapper.php';
include 'SCA/Bindings/rss/RssDas.php';

class SCA_Bindings_rss_ServiceRequestHandler
{
        
    public function handle($calling_component_filename)
    {
        
        SCA::$logger->log("Entering");

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

        //*handle situations where we have the id in the url.*/

        /**
             These look like the variables to use:
                [REQUEST_URI] => /Samples/RSS/Contact.php/12
                [SCRIPT_NAME] => /Samples/RSS/Contact.php
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

        } else if (isset($_GET['id'])) {
            $id = $_GET['id'];  //left so that our rewrite test still works
        } else {
            $id = null;
        }

        try {

            //always give the component an sdo, but handle sdo or xml back from it.
            if ($method === 'create') {
                SCA::$logger->log("$method == create");

                // Can only do GET (retrieve) on RSS
                // return http method not allowed status
                header("HTTP/1.1 405");
            } else if ($method === 'retrieve') {

                $call_response = null;
                try {

                    if($id === null){
                        $method = 'enumerate';
                    }

                    SCA::$logger->log("Calling $method on the RSS service wrapper, passing in the id $id");

                    $class_name = SCA_Helper::guessClassName($calling_component_filename);
                    $service_component = SCA::createInstance($class_name);
                    SCA::fillInReferences($service_component);

                    $call_response = call_user_func_array(array(&$service_component,
                                                   $method), $id);
 
                    SCA::$logger->log("Response from calling the method $method is: $call_response");
                    //TODO: make sure these tests reflect the correct return values.
                    if ($call_response !== null) {

                        $response_xml;

                        // Handle the different types of response (SDO, PHP Class, Raw XML)
                        if($call_response instanceof SDO_DataObjectImpl) {
                            //if the thing received is an sdo...
                            //convert it to xml
                            $response_xml = SCA_Bindings_rss_RssDas::toXml($call_response);
                        } else if ($call_response Instanceof Channel) {
                            // TODO: write the mapping from php classes to XML...
                            SCA::$logger->log("TODO: write the mapping from php rss classes to xml.");
                            $response_xml = null;
                        } else if (is_string($call_response)) {
                            $response_xml = $call_response;
                        }


                        header("HTTP/1.1 200");
                        header("Content-Type: application/xml");
                        echo $response_xml;
                    } else {
                        SCA::$logger->log("Caught call_response is null exception in RSSServer");
                        //TODO find out the right response
                        header("HTTP/1.1 500");
                        //echo "The requested resource <em>$id</em> does not exist on this database";
                    }
                }
                //catch a bunch of exceptions. TODO: pull out the message in the exception and flow it back
                //start with service unavailable and then conflict as these are least fatal
                catch(SCA_ServiceUnavailableException $ex){
                    header("HTTP/1.1 503");
                }
                catch(SCA_ConflictException $ex){
                    header("HTTP/1.1 409");
                }
                catch(SCA_AuthenticationException $ex){
                    header("HTTP/1.1 407");
                }
                catch(SCA_BadRequestException $ex){
                    header("HTTP/1.1 400");
                }
                catch(SCA_InternalServerErrorException $ex){
                    SCA::$logger->log("Caught SCA_InternalServerErrorException in RSSServer");
                    header("HTTP/1.1 500");
                }
                catch(SCA_MethodNotAllowedException $ex){
                    //note  - this one is more likely to be thrown by the server code than the component code.
                    header("HTTP/1.1 405");
                }
                catch(SCA_UnauthorizedException $ex){
                    header("HTTP/1.1 401");
                }
                catch(SCA_RuntimeException $ex){
                    SCA::$logger->log("Caught SCA_RuntimeException in RSSServer\n");
                    header("HTTP/1.1 500");
                }
                catch ( Exception $ex ) {
                    SCA::$logger->log("Caught an exception in RSSServer: ".$ex->getMessage()."\n");
                    $call_response['error'] = $ex->getMessage();
                    //TODO find out the right response
                    header("HTTP/1.1 500");
                }


            } else if ($method === 'update') {

                // Can only do GET (retrieve) on RSS
                // return http method not allowed status
                header("HTTP/1.1 405");
            } else if ($method === 'delete') {

                // Can only do GET (retrieve) on RSS
                // return http method not allowed status
                header("HTTP/1.1 405");

            }

        }
        catch(SCA_MethodNotAllowedException $ex){
            //catch problem finding the method encountered by the service wrapper.
            header("HTTP/1.1 405");
        }
        catch (SCA_RuntimeException $ex) {

            //TODO: output exceptions correctly.
            header("HTTP/1.1 500");

        }

        return;

    }



}

?>