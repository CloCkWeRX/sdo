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
$Id: Proxy.php 238265 2007-06-22 14:32:40Z mfp $
*/

/**
*
* TODO: think about the name 'proxy' : proxy for what? 'for a service that is going to be called locally.' or remotely.
* do we like the word proxy?
*
*/

require_once "SCA/SCA_Exceptions.php";
require_once "SCA/Bindings/jsonrpc/SCA_JsonRpcClient.php";
require_once "SCA/SCA_ReferenceType.php";

/**
* A proxy for references to services using the JSON RPC protocol
*
*/



class SCA_Bindings_Jsonrpc_Proxy {
    private $smd_file_name;
    private $jsonrpc_client;
    private $reference_type;
    private $containing_class_name;

    /**
     * Constructor - create a JRON RPC proxy based on the full pathname of
     *               an SMD file
     */
    public function __construct($target, $base_path_for_relative_paths, $binding_config)
    {
        SCA::$logger->log('Entering');

        $this->smd_file_name = SCA_Helper::constructAbsoluteTarget($target, $base_path_for_relative_paths);
        $this->jsonrpc_client = new SCA_JsonRpcClient($this->smd_file_name);

        SCA::$logger->log('Exiting');
    }

    /**
     * Add reference type
     *
     * @param SCA_ReferenceType $reference_type Reference type
     *
     * @return null
     */
    public function addReferenceType(SCA_ReferenceType $reference_type)
    {
        $this->reference_type = $reference_type;
        $this->jsonrpc_client->addReferenceType($reference_type);
    }


    /**
     * Not much goes on here but I've followed the pattern of having
     * separate proxy and server classes from the SOAP code just in case
     */
    public function __call($method_name, $arguments)
    {

        try {
            $return = call_user_func_array(array(&$this->jsonrpc_client, $method_name), $arguments);
        } catch( Exception $ex) {
             throw $ex;
        }
        return $return;
    }

    /**
     * Allows the reference user to create a data object
     * based on a type that is expected to form part of
     * a message to reference
     */
    public function createDataObject($namespace_uri, $type_name )
    {
        try {
            $xmldas     = $this->reference_type->getXmlDas();
            $dataobject = $xmldas->createDataObject($namespace_uri, $type_name);
            return $dataobject;
        } catch( Exception $e) {
            throw new SCA_RuntimeException($e->getMessage());
        }
    }
}
