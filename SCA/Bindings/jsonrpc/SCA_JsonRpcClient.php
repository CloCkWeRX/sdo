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
$Id: SCA_JsonRpcClient.php 234945 2007-05-04 15:05:53Z mfp $
*/

/**
* SCA JSON-RPC client
*
* This class provides a client side implementation of the JSON-RPC
* procol specified at (http://json-rpc.org/wiki/specification). This
* client is used when binding.jsonrpc is applied to an SCA @Reference
* annotation and processes JSON-RPC protocol messages on behalf
* of the SCA runtime.
*
*/

require_once "SDO/DAS/Json.php";


class SCA_JsonRpcClient {

    // stores the request in testing scenarios so that
    // the test can look at it
    public static $store_test_request = false;
    public static $test_request = null;
    public static $test_response = null;

    private $smd_file         = null;
    private $smd              = null;
    private $service_url      = null;
    private $methods          = null;
    private $json_das         = null;
    private $id               = 0;
    private $reference_type   = null;
    private $type_list        = null;

    /**
     * Constructor - create a JSON RPC client based on an
     *               SMD file
     */
    public function __construct($full_smd_file_name)
    {
        // get the smd file
        // I COULD USE file_get_contents FOR BOTH LOCAL AND REMOTE
        // FILES BUT THIS DOESN'T WORK FOR REMOTE FILES
        // OF A CERTAIN SIZE SO AS YOU ADD FUNCTIONS TO YOUR
        // SERVICE IT FAILS FOR NO APPARENT REASON SO I HAVE TO
        // TEST IF THE FILE IS LOCAL OR NOT
        if ( strstr($full_smd_file_name, 'http') ) {
            // use curl to get a remote file
            $request = curl_init($full_smd_file_name);
            curl_setopt($request, CURLOPT_HEADER, false);
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            $this->smd_file = curl_exec($request);

            // Get info about the response
            $response_http_code = curl_getinfo($request, CURLINFO_HTTP_CODE);

            if ($this->smd_file == null | $this->smd_file == false ) {
                throw new SCA_RuntimeException("SMD file $full_smd_file_name for remote JSON service " .
                "was not found or was empty");
            }

            // test the response status
            if ($response_http_code != 200 ) {
                throw new SCA_RuntimeException("Call to retrieve $full_smd_file_name failed with HTTP " .
                " response code " . $response_http_code);
            }
        } else {
            // the file is local so get it from the file system
            $this->smd_file = file_get_contents($full_smd_file_name);
        }

        // parse the SMD file
        $this->smd         = json_decode($this->smd_file);
        $this->service_url = $this->smd->serviceURL;
        $this->methods     = $this->smd->methods;
    }

    /**
     * TODO - We need to think about where to put this method but I need the
     * SMD file and access to the references so here will have to
     * do fo now. I want a cleaner abstraction of JsonRpcClient and
     * JsonProxy.
     */
    public function addReferenceType(SCA_ReferenceType $reference_type)
    {
        $this->reference_type = $reference_type;

        // Add type descriptions to the Json DAS. We use XSDs if they
        // are prvoided. If not we used the SMD and assume the default
        // namespace provided by the JSON DAS
        if ( count($reference_type->getTypes()) > 0 ) {
            // Some XSD types are specified with the reference
            // annotation so use these XSDs to build the JSON DAS

            $this->json_das = $this->getJsonDas($reference_type->getTypes(),$reference_type->getClassName());

            // get the list of types that have been loaded into
            // the JSON DAS in this case
            $this->type_list = SCA_Helper::getAllXmlDasTypes($this->json_das->getXmlDas());
        } else {
            // No XSDs are specified with the reference annotation
            // so use the SMD to build the JSON DAS
            $this->json_das = new SDO_DAS_Json();
            $this->json_das->addTypesSmdString($this->smd_file);
        }
    }

    public function getJsonDas($xsds,$class_name)
    {
        $json_das = new SDO_DAS_Json();
        foreach ($xsds as $index => $xsds) {
            list($namespace, $xsdfile) = $xsds;
            if (SCA_Helper::isARelativePath($xsdfile)) {
                $xsd = SCA_Helper::constructAbsolutePath($xsdfile, $class_name);
                $json_das->addTypesXsdFile($xsd);
            }
        }
        return $json_das;
    }

    /**
     * Call the remote JSON service method using CURL to
     * send the HTTP request
     */
    public function __call($method_name, $arguments)
    {
        // check that the requested method exists
        $method = null;
        foreach ($this->methods as $methods_entry ) {
            if ($methods_entry->name == $method_name ) {
                $method = $methods_entry;
            }
        }

        if ($method == null ) {
            throw new SCA_RuntimeException("Trying to call $method_name on remote service " .
            "using JSONRPC and the method cannot be found. " .
            "The remote service has the following interface " .
            $this->smd_file);
        }

        // collect useful information about the method
        $return_type = $method->return->type;

        // construct the JSON request
        $json_request = "{\"id\":" . $this->id . ",";

        // increment the id so we get a unique id for calls from this client
        // its done up here so that we don't resuse IDs in this session
        // regardless of what happens to the call
        // TODO - is this sufficient?
        $this->id = $this->id + 1;

        $json_request = $json_request . "\"method\":\"$method_name\",";
        $json_request = $json_request . "\"params\":[";

        // convert all the arguments into JSON strings
        $argument_id = 0;
        foreach ($arguments as $argument ) {

            if ($argument == null ) {
                $json_request = $json_request;
            } else if ( is_object($argument) ) {
                $json_request = $json_request .
                $this->json_das->encode($argument);
            } else if ( is_array($argument) ) {
                throw new SCA_RuntimeException("Argument $argument_id to $method_name of type array found. " .
                "Arguments must be either primitives or SDOs");
            } else {
                $json_request = $json_request . json_encode($argument);
            }

            $argument_id += 1;

            if ($argument_id < count($arguments) ) {
                $json_request = $json_request . ",";
            }
        }

        $json_request = $json_request . "],\"version\":\"1.0\"}";

        // some debugging
        //            file_put_contents("json_messages.txt",
        //            "Request at JSONRPC client = " . $json_request . "\n",
        //            FILE_APPEND);
        if (self::$store_test_request == false){
            // send this string to the service url
            $request = curl_init($this->service_url);

            curl_setopt($request, CURLOPT_POST, true);
            curl_setopt($request, CURLOPT_POSTFIELDS, $json_request);
            curl_setopt($request, CURLOPT_HEADER, false);
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            $header = array("User-Agent: SCA",
            "Content-Type: application/json-rpc",
            "Accept: application/json-rpc");
            curl_setopt($request, CURLOPT_HTTPHEADER, $header);

            // Do the POST
            $response = curl_exec($request);

            // TODO probably need a better way of detecting PHP errors at the far end
            if ( strchr($response,'<b>Fatal error</b>') !== false
            || strchr($response,'<b>Parse error</b>') !== false) {
                throw new SCA_RuntimeException('Bad response from jsonrpc call: ' . $response);
            }

            // Get info about the response
            $response_http_code = curl_getinfo($request, CURLINFO_HTTP_CODE);

            // close the session
            curl_close($request);

            // some debugging
            //            file_put_contents("json_messages.txt",
            //            "Response at JSONRPC client = " . $response . "\n",
            //            FILE_APPEND);

            // test the response status
            if ($response == null || $response == false  ) {
                throw new SCA_RuntimeException("JSONRPC call to $this->service_url for method " .
                "$method_name failed ");
            }

            // test the response status
            if ($response_http_code != 200 ) {
                throw new SCA_RuntimeException("JSONRPC call to $this->service_url for method " .
                "$method_name failed with HTTP response code " .
                $response_http_code);
            }
        } else {
            self::$test_request = $json_request;
            $response           = self::$test_response;
        }

        // decode the response
        $json_response = json_decode($response);

        $id = $json_response->id;

        // test to make sure that the id on the response matches
        // the id on the request
        if ($id <> ($this->id - 1) ) {
            throw new SCA_RuntimeException("JSONRPC call to $this->service_url for method " .
            "$method_name failed as the id of the response $id " .
            "does not match id of the request " .
            ($this->id - 1));
        }

        // test to see if there is an error in the response message
        if ( isset($json_response->error) ) {
            $error = $json_response->error;
            if ($error != null ) {
                throw new SCA_RuntimeException("JSONRPC call to $this->service_url for method " .
                "$method_name failed. The JSON error field contained " .
                '"' . $error . '"');
            }
        } else {
            // decode the response based on its type.
            $result        = $json_response->result;
            $return_object = null;

            if ( is_object($result) ) {
                // decode the complex object into an SDO. The return type
                // has come from  the SMD. If XSD files
                // have also been specified then we guess the namespace
                // of the return type based on the matching the return
                // type name from the SMD with the type names from the
                // XSD
                $return_namespace = $this->_guessReturnTypeNamespace($return_type);
                $return_object    = $this->json_das->decodeFromPHPObject($result,
                $return_type,
                $return_namespace);
            } else if ( is_array($result) ) {
                throw new SCA_RuntimeException("JSONRPC call to $this->service_url for method " .
                "$method_name failed. Result of type array found. " .
                "Return types must be either primitives or SDOs");
            } else {
                // do nothing in this case. The json_decode method
                // call has done all the work for us for primitive
                // types and null
                $return_object = $result;
            }
        }

        return $return_object;
    }

    /**
     * Guess the namespace of the return type by comparing the return type
     * (from the SMD) with the types from the XSDs
     */
    private function _guessReturnTypeNamespace($name_of_type)
    {
        $number_of_types_found = 0;
        $return_namespace      = null;

        // first check if any types have been specified in XSDs
        // if no types are specified in XSDs a null return namspace
        // reults and the json das will use the default namespace
        if ( isset($this->type_list) ) {
            // now match the required type name against all the
            // types specified in XSDs
            foreach ($this->type_list as $type ) {
                $type_namespace = $type[0];
                $type_name      = $type[1];

                if ($name_of_type == $type_name ) {
                    $return_namespace      = $type_namespace;
                    $number_of_types_found = $number_of_types_found + 1;
                }
            }

            // If we can't find a matching type name then
            // raise a warning
            if ($number_of_types_found == 0 ) {
                throw new SCA_RuntimeException("Type name $name_of_type from SMD not found in the " .
                "XSDs provided in @type annotations with the reference " .
                "so namespace can't be determined");
            }

            // it is possible of course that our collection of XSDs
            // associated with a reference can specify the same type name
            // in two or more different namespaces. Raise a warning if this
            // happens
            if ($number_of_types_found > 1 ) {
                throw new SCA_RuntimeException("Type name $name_of_type from SMD appears multiple times in the " .
                "XSDs provided in @type annotations with the reference " .
                "The namespace chosen was $return_namespace");
            }
        }
        return $return_namespace;
    }

}
