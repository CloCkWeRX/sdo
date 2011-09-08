<?php
/**
 * +-----------------------------------------------------------------------------+
 * | (c) Copyright IBM Corporation 2006.                                         |
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
 * +-----------------------------------------------------------------------------+
 * | Author:  Rajini Sivaram, Simon Laws                                         |
 * +-----------------------------------------------------------------------------+
 * $Id: Proxy.php 238265 2007-06-22 14:32:40Z mfp $
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Simon Laws <slaws@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */

require_once "SCA/SCA_Exceptions.php";
require_once "SCA/SCA_ReferenceType.php";
require_once "SCA/SCA_Helper.php";
require_once "SCA/Bindings/xmlrpc/Das.php";

/**
 * A proxy for references to services using the XML RPC protocol
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Simon Laws <slaws@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */
class SCA_Bindings_Xmlrpc_Proxy
{


    protected $service_url;
    protected $method_list;
    protected $type_list;
    protected $xmlrpc_das = null;
    protected $reference_type;

    /**
     * Constructor - create an XML RPC proxy
     * If a relative path is specified for URL, it will be resolved to an URL relative
     * to PHP_SELF. The proxy adds /RPC2 as PATH_INFO to the service url.
     *
     * @param string $target                       URL of XMLRPC service
     * @param string $base_path_for_relative_paths Base path
     * @param string $binding_config               Config

     */
    public function __construct($target,
        $base_path_for_relative_paths,
        $binding_config
    ) {
        $serviceUrl = SCA_Helper::constructAbsoluteTarget($target, $base_path_for_relative_paths);

        SCA::$logger->log("Proxy serviceUrl $serviceUrl");

        if (!strstr($serviceUrl, "/RPC2"))
            $this->service_url  = $serviceUrl."/RPC2";
        else
            $this->service_url  = $serviceUrl;

        SCA::$logger->log("URL $this->service_url");

        try {

            $methodDesc = $this->__call("system.describeMethods", array());
            $this->method_list = array();
            $methodList = array();
            $typeList = array();


            foreach ($methodDesc["methodList"] as $method) {
                $methodList[$method["name"]] = $method;
            }
            foreach ($methodDesc["typeList"] as $type) {
                $typeList[$type["name"]] = $type;
            }
            $this->method_list["methodList"] = $methodList;
            $this->method_list["typeList"] = $typeList;

        } catch (SCA_RuntimeException $e) {
            // The server probably does not support system.describeMethods
            // Ignore the exception, and attempt to make calls using generic types
        }

    }

    /**
     * Add types specified using @types to the reference annotation.
     * An XML DAS is added to the XMLRPC DAS to handle xsds.
     *
     * @param SCA_ReferenceType $reference_type Reference type
     *
     * @return null
     */
    public function addReferenceType(SCA_ReferenceType $reference_type)
    {

        $this->reference_type = $reference_type;

        // Add type descriptions to the XML DAS. We use XSDs if they
        // are provided.
        if (count($reference_type->getTypes()) > 0) {
            // Some XSD types are specified with the reference
            // annotation so use these XSDs to build the DAS
            $this->xmlrpc_das = $this->createXmlRpcDas($reference_type);

            // get the list of types that have been loaded into
            // the XMLDAS in this case
            $this->type_list = SCA_Helper::getAllXmlDasTypes($this->xmlrpc_das->getXmlDas());

        } else {

            $this->xmlrpc_das = new SCA_Bindings_Xmlrpc_DAS();
            $this->xmlrpc_das->addTypesXmlRpc($this->method_list["typeList"]);
        }

    }

    /**
     * Create XMLRPCDAS
     *
     * @param SCA_ReferenceType $reference_type Reference type
     *
     * @return object
     */
    protected function createXmlRpcDas($reference_type)
    {
        $xsds     = $reference_type->getTypes();
        $xmlrpc_das = new SCA_Bindings_Xmlrpc_DAS();
        foreach ($xsds as $index => $xsds) {
            list($namespace, $xsdfile) = $xsds;

            if (SCA_Helper::isARelativePath($xsdfile)) {
                $xsd = SCA_Helper::constructAbsolutePath($xsdfile, $reference_type->getClassName());
                $xmlrpc_das->addTypesXsdFile($xsd);
            }

        }
        return $xmlrpc_das;
    }


    /**
     * Create an XMLRPC DAS if one does not already exist.
     * For proxies corresponding to an @reference in an SCA component,
     * the DAS referring to the XSDs specified using @types and the typeList
     * from the server is created when addReferenceType is called. For
     * proxies corresponding to SCA::getService, an XMLRPC DAS referring
     * to the typeList from server is created when this method is first called
     * (when service->createDataObject is called or when an XMLRPC method
     *  returns a complex type)
     *
     * @return object
     */
    protected function getXmlRpcDas()
    {
        if ($this->xmlrpc_das == null) {
            $this->xmlrpc_das = new SCA_Bindings_Xmlrpc_DAS();
            $this->xmlrpc_das->addTypesXmlRpc($this->method_list["typeList"]);
        }
        return $this->xmlrpc_das;
    }


    /**
     * Guess the namespace of the return type by comparing the return type
     * with the types from the XSDs
     *
     * @param string $name_of_type Name
     *
     * @return mixed
     */
    protected function guessReturnTypeNamespace($name_of_type)
    {
        $number_of_types_found = 0;
        $return_namespace      = null;

        // First check if any types have been specified in XSDs
        // If no types are specified in XSDs a null return namespace
        // results and the xmlrpc das will use the default namespace
        if (isset($this->type_list)) {
            // now match the required type name against all the
            // types specified in XSDs
            foreach ($this->type_list as $type) {
                $type_namespace = $type[0];
                $type_name      = $type[1];

                if ($name_of_type == $type_name) {
                    $return_namespace      = $type_namespace;
                    $number_of_types_found = $number_of_types_found + 1;
                }
            }


            // it is possible of course that our collection of XSDs
            // associated with a reference can specify the same type name
            // in two or more different namespaces. Raise a warning if this
            // happens
            if ($number_of_types_found > 1) {
                throw new SCA_RuntimeException(
                    "Type name $name_of_type appears multiple times in the " .
                    "XSDs provided in @type annotations with the reference " .
                    "The namespace chosen was $return_namespace"
                );
            }
        }
        return $return_namespace;
    }


    /**
     * Convert the associative array returned by xmlrpc_decode to an SDO
     *
     * @param array  $array_value Value
     * @param string $method_name Name
     *
     * @return SDO
     */
    protected function xmlRpcReturnValToSdo($array_value, $method_name)
    {

        $type_name = null;
        $return_namespace = null;

        if (isset($this->method_list["methodList"][$method_name])) {
            $methodInfo = $this->method_list["methodList"][$method_name];
            if (array_key_exists('signatures', $methodInfo) && array_key_exists('returns', $methodInfo["signatures"][0])) {
                $return_description = $methodInfo["signatures"][0]["returns"];
                $type_name = $return_description[0]["type"];
            }

            $return_namespace = $this->guessReturnTypeNamespace($type_name);
        }



        $xmlrpc_das = $this->getXmlRpcDas();
        $sdo = $xmlrpc_das->decodeFromPHPArray($array_value, $return_namespace, $type_name);

        return $sdo;
    }


    /**
     * Call the remote XMLRPC service method using CURL to
     * send the HTTP request
     *
     * @param string $method_name Method name
     * @param array  $arguments   (Arguments are passed in as SDOs)
     *
     * @return mixed Return value of method as SDO or primitive
     */
    public function __call($method_name, $arguments)
    {

        // Make a copy of the arguments since xmlrpc_encode_request converts sdos to arrays
        $callArgs = array();
        for ($i = 0; $i < count($arguments); $i++) {
            if (is_object($arguments[$i])) {
                $callArgs[$i] =  clone $arguments[$i];
            } else {
                $callArgs[$i] =  $arguments[$i];
            }
        }

        $xml_request = xmlrpc_encode_request($method_name, $callArgs);


        SCA::$logger->log("Request $xml_request");

        // some debugging
        //            file_put_contents("xmlrpc_messages.txt",
        //                              "Request at XMLRPC client = " . $xml_request . "\n",
        //                              FILE_APPEND);

        // send this string to the service url
        $request = curl_init($this->service_url);

        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $xml_request);
        curl_setopt($request, CURLOPT_HEADER, false);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $header = array("User-Agent: SCA",
        "Content-Type: text/xml",
        "Accept: text/xml");
        curl_setopt($request, CURLOPT_HTTPHEADER, $header);

        // Do the POST
        $response = curl_exec($request);

        // Get info about the response
        $response_http_code = curl_getinfo($request, CURLINFO_HTTP_CODE);

        // close the session
        curl_close($request);

        // some debugging
        //            file_put_contents("xmlrpc_messages.txt",
        //                              "Response at XMLRPC client = " . $response . "\n",
        //                              FILE_APPEND);

        // test the response status
        if ($response == null || $response == false) {
            throw new SCA_RuntimeException(
                "XML-RPC call to $this->service_url for method " .
                "$method_name failed "
            );
        }

        // test the response status
        if ($response_http_code != 200) {
            throw new SCA_RuntimeException(
                "XML-RPC call to $this->service_url for method " .
                "$method_name failed with HTTP response code " .
                $response_http_code
            );
        }

        // decode the response
        $method = null;
        $xml_response = xmlrpc_decode_request($response, $method);

        SCA::$logger->log("response string ". $response);

        // test to see if there is an error in the response message
        if (is_array($xml_response) && isset($xml_response["faultCode"])) {

            $faultCode = $xml_response["faultCode"];
            $faultReason = $xml_response["faultString"];
            if ($faultCode != null) {

                throw new SCA_RuntimeException(
                    "XML-RPC call to $this->service_url for method " .
                    "$method_name failed. Fault Code: $faultCode, Reason: $faultReason"
                );
            }

        } else if ((is_array($xml_response)||is_object($xml_response)) && !strstr($method_name, "system.")) {

            $return_object = $this->xmlRpcReturnValToSdo($xml_response, $method_name);

        } else {

            // number / boolean / string / SystemMethod response
            $return_object = $xml_response;
        }

        return $return_object;
    }


    /**
     * Allows the reference user to create a data object
     * based on a type that is expected to form part of
     * a message to reference
     *
     * @param string $namespace_uri Namespace URI
     * @param string $type_name     Type name
     *
     * @return mixed
     */
    public function createDataObject($namespace_uri, $type_name)
    {
        try {

            $xmlrpc_das = $this->getXmlRpcDas();
            $dataobject = $xmlrpc_das->createDataObject($namespace_uri, $type_name);

            return $dataobject;
        } catch (Exception $e) {
            throw new SCA_RuntimeException($e->getMessage());
        }
    }


}