<?php
/**
 * +----------------------------------------------------------------------+
 * | (c) Copyright IBM Corporation 2006.                                  |
 * | All Rights Reserved.                                                 |
 * +----------------------------------------------------------------------+
 * |                                                                      |
 * | Licensed under the Apache License, Version 2.0 (the "License"); you  |
 * | may not use this file except in compliance with the License. You may |
 * | obtain a copy of the License at                                      |
 * | http://www.apache.org/licenses/LICENSE-2.0                           |
 * |                                                                      |
 * | Unless required by applicable law or agreed to in writing, software  |
 * | distributed under the License is distributed on an "AS IS" BASIS,    |
 * | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
 * | implied. See the License for the specific language governing         |
 * | permissions and limitations under the License.                       |
 * +----------------------------------------------------------------------+
 * | Author: Rajini Sivaram, Simon Laws                                   |
 * +----------------------------------------------------------------------+
 * $Id: Das.php 234945 2007-05-04 15:05:53Z mfp $
 * PHP Version 5
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Simon Laws <slaws@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */


/**
 * SDO XmlRpc Data Access Service.
 *
 * This is a very simple implementation of a DAS that allows
 * SDOs to be converted to and from XML-RPC. All of the hard work is done
 * by the XML-RPC extension
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Simon Laws <slaws@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */
class SCA_Bindings_Xmlrpc_DAS
{

    //--------------------------------------------------------------------
    // Member variables
    //--------------------------------------------------------------------

    // Some constants uses in the decoding processing
    protected $sdo_namespace     = "commonj.sdo";
    protected $default_namespace = "";

    // The SDO models used to generate the SDO when
    // an xmlrpc typelist is decoded.
    protected $data_factory      = null;
    protected $xml_das           = null;


    //--------------------------------------------------------------------
    // Object construction
    //--------------------------------------------------------------------

    /**
     * Builds a SDO_DAS_DataFactory containing a single generic open type
     * This is required when converting XMLRPC types into an SDO. As XMLRPC provides
     * no schema for the data we are going to read so this is the most basic
     * generic type model.
     */
    public function __construct()
    {
        $this->data_factory = SDO_DAS_DataFactory::getDataFactory();

        $this->data_factory->addType(
            $this->default_namespace,
            'GenericType',
            array('open' => true)
        );
    }

    //--------------------------------------------------------------------
    // Accessors
    //--------------------------------------------------------------------
    /**
     * Get Data Factory
     *
     * @return object
     */
    public function getDataFactory()
    {
        return $this->data_factory;
    }

    /**
     * Get XML DAS
     *
     * @return object
     */
    public function getXmlDas()
    {
        return $this->xml_das;
    }

    //--------------------------------------------------------------------
    // Functions that allow type information to be passed in
    //--------------------------------------------------------------------


    /**
     * Add types returned by system.describeMethods to the list of known types
     * These types are used by the XMLRPC proxy if it does not have access to
     * the corresponding XSDs
     *
     * @param string $types_list    XMLRPC Typelist
     * @param array  $namespace_map takes the form "typename" => "namespace"
     *
     * @return null
     */
    public function addTypesXmlRpc($types_list, $namespace_map = null)
    {

        // check if any types have been returned by XMLRPC system.describeMethods
        // simple types map directly to the SDO basic types
        if ($types_list == null) {
            return;
        }

        // iterate over the types adding types to the
        // SDO model
        foreach ($types_list as $type) {
            $type_name = $type["name"];
            $namespace = $this->default_namespace;

            if (strpos($type_name, "system.") === 0)
                continue;

            // look type up in namespace map
            if ($namespace_map != null
                && array_key_exists($type_name, $namespace_map)
            ) {
                $namespace = $namespace_map[$type_name];
            }


            // create the type
            $this->data_factory->addType(
                $namespace,
                $type_name,
                array('open'=>true)
            );

            SCA::$logger->log("addType $namespace, $type_name");
        }

        // iterate over the types adding properties to the types
        // that have already been created. This is a two pass
        // process because the property type must exist before
        // we create the property of that type
        foreach ($types_list as $type) {
            $type_name = $type["name"];
            $namespace = $this->default_namespace;

            // Ignore system types since they dont appear in regular method params/return.
            if (strpos($type_name, "system.") === 0)
                continue;

            // look type up in namespace map
            if ($namespace_map != null
                && array_key_exists($type_name, $namespace_map)
            ) {
                $namespace = $namespace_map[$type["name"]];
            }

            // create the properties of the type
            foreach ($type["member"] as $property) {
                $property_name      = $property["name"];
                $property_type      = $property["type"];
                $property_namespace = $this->default_namespace;
                $is_array           = false;

                // work out whether the property is many
                // valued
                $array_pos          = strpos($property_type, "[]");

                if ($array_pos !== false) {
                    // it is an array so strip out the array
                    // markers
                    $property_type = substr($property_type, 0, $array_pos);
                    $is_array      = true;
                }

                // strip and whitespace from begining and end of property type
                $property_type = trim($property_type);

                // if this is a primitive type then we need to
                // convert to the SDO primitive types
                $converted_property_type = $this->xmlrpcTypeToSdoType($property_type);


                // fix up the namespace
                if (!$this->isPrimitiveType($property_type)) {
                    // Map the namespace to see
                    // if the user has told us what namespace this
                    // typename should have
                    if ($namespace_map != null
                        && array_key_exists($property_type, $namespace_map)
                    ) {
                        $property_namespace = $namespace_map[$property_type];
                    }
                } else {
                    // its a primitive type so use the SDO namespace
                    $property_namespace = $this->sdo_namespace;
                }



                $this->data_factory->addPropertyToType(
                    $namespace,
                    $type_name,
                    $property_name,
                    $property_namespace,
                    $converted_property_type,
                    array('containment'=>true,
                          'many'       =>$is_array)
                );


            }
        }
    }

    /**
     * Add types from the supplied XSD file to the XMLDAS owned by this DAS.
     * Create an XMLDAS if one does not already exist.
     *
     * @param string $xsd_file XSD File
     *
     * @return null
     */
    public function addTypesXsdFile($xsd_file)
    {

        if ($this->xml_das == null) {
            $this->xml_das = SDO_DAS_XML::create();
        }

        if ($xsd_file != null) {
            $this->xml_das->addTypes($xsd_file);
        }
    }


    /**
     * Convert XMLRPC type to SDO type
     *
     * @param string $xmlrpc_type_name Name
     *
     * @return string  SDO type name
     */
    protected function xmlrpcTypeToSdoType($xmlrpc_type_name)
    {
        $sdo_type_name = $xmlrpc_type_name;

        switch ($xmlrpc_type_name) {
        case "boolean":
            $sdo_type_name = "Boolean";
            break;
        case "string":
            $sdo_type_name = "String";
            break;
        case "i4":
        case "int":
            $sdo_type_name = "Integer";
            break;
        case "double":
            $sdo_type_name = "Double";
            break;
        }

        return $sdo_type_name;
    }


    /**
     * Check if specified XMLRPC type corresponds to a PHP primitive type.
     *
     * @param string $xmlrpc_type_name Name
     *
     * @return boolean True if primitive
     */
    protected function isPrimitiveType($xmlrpc_type_name)
    {
        return $xmlrpc_type_name == "boolean" ||
               $xmlrpc_type_name == "string" ||
               $xmlrpc_type_name == "String" ||
               $xmlrpc_type_name == "i4" ||
               $xmlrpc_type_name == "int" ||
               $xmlrpc_type_name == "double";

    }


    //--------------------------------------------------------------------
    // Decode an XMLRPC message to an SDO
    //--------------------------------------------------------------------


    /**
     * Convert a PHP associative arrary returned by xmlrpc_decode into an SDO
     * 1) If the type was contained in an XSD, use the XMLDAS to create object
     * 2) If the type was obtained from server using system.describeMethods, create
     *    object with the specified type and generic namespace
     * 3) Otherwise create object using generic type
     *
     * @param object $obj            Object
     * @param string $root_namespace Root namesapce
     * @param string $root_type      Root type
     *
     * @return SDO
     */
    public function decodeFromPHPArray($obj, $root_namespace = null, $root_type = null)
    {
        // copy the rpc elements into an SDO object. We force any
        // top level object here to be an SDO object on the assumption
        // that we will not be passed fragments.
        $sdo = null;

        // guess the namespace if one is not provided
        if ($root_namespace == null) {
            $root_namespace = $this->default_namespace;
        }

        $done = false;
        if ($root_type != null) {

            if ($this->xml_das != null) {

                try {

                    $sdo = $this->xml_das->createDataObject($root_namespace, $root_type);

                    $this->decodeObjectToSDOTyped($obj, $sdo);

                    $done = true;

                } catch (Exception $e) {
                     // Could not create the object using XMLDAS - either the type was
                     // not specified using @types, or the xsd file could not be loaded
                }
            }

            if (!$done) {

                try {

                    $sdo = $this->data_factory->create($root_namespace, $root_type);

                    $this->decodeObjectToSDOTyped($obj, $sdo);

                    $done = true;

                } catch (Exception $e) {
                     // Could not create the object using XMLRPC DAS
                }
            }


        }
        if (!$done) {
            // either there is no model or there is no root type
            // so we need to create a generic model

            // parse the xmlrpc message using this type hierarchy

            $sdo = $this->data_factory->create($this->default_namespace, 'GenericType');
            if ($this->gettype($obj) != "array") {
                $this->decodeObjectToSDO($obj, $sdo);
            } else {
                $this->decodeArrayToSDO("", $obj, $sdo);

            }
        }

        return $sdo;
    }

    /**
     * Objects decoded by XMLRPC contain
     *     Primitives - PHP primitives
     *     Arrays - PHP numerically indexed arrays
     *     Objects - PHP associative arrays
     *
     * If this is a performance concern, it should be possible to decode the XML returned by XMLRPC
     * directly into and SDO.
     *
     * @param object $obj Object
     *
     * @return string
     */
    protected function gettype($obj)
    {
        $type = gettype($obj);
        if ($type == "array") {

            if (!is_numeric(implode(array_keys($obj)))) {
                $type = "object";
            }

        }
        return $type;

    }

    /**
     * A recursive function that copies PHP objects to an SDO data graph
     *
     * @param object $object Object
     * @param mixed  $sdo    SDO
     *
     * @return null
     */
    protected function decodeObjectToSDOTyped($object, $sdo)
    {
        foreach ($object as $param_name => $param_value) {
            $this->decodeToSDOTyped($param_name, $param_value, $sdo);
        }
    }


    /**
     * A recursive function that copies PHP arrays to an SDO data graph
     *
     * @param string $array_name Name
     * @param array  $array      Target array
     * @param object $sdo        ServiceDataObject
     *
     * @return null
     */
    protected function decodeArrayToSDOTyped($array_name, $array, $sdo)
    {
        $array_index = 0;
        foreach ($array as $array_entry) {
            $array_entry_type = $this->gettype($array_entry);

            // SCA::$logger->log( "Typed - Array Name: " . $array_name .
            //                      " Array Index: " . $array_index .
            //                      " Type: ". $array_entry_type . "\n");

            if ($array_entry_type == "object") {
                $new_sdo = $sdo->createDataObject($array_name);
                $this->decodeObjectToSDOTyped($array_entry, $new_sdo);
            } else if ($array_entry_type == "array") {
                $new_sdo = $sdo->createDataObject($array_name);
                $this->decodeArrayToSDO($array_name, $array_entry, $new_sdo);
            } else {
                $array_object = $sdo[$array_name];
                $array_object[] = $array_entry;
            }
            $array_index = $array_index + 1;
        }
    }

    /**
     * Makes the decision on how the PHP object should be copied
     * and recurse as necessary
     *
     * @param string $item_name Name
     * @param string $item      Item
     * @param SDO    $sdo       SDO
     *
     * @return null
     */
    protected function decodeToSDOTyped($item_name, $item, $sdo)
    {
        $item_type = $this->gettype($item);

        // SCA::$logger->log( "Typed - Name: " . $item_name .  " Type: ". $item_type . "\n");

        if ($item_type == "object") {
            $new_sdo = $sdo->createDataObject($item_name);
            $this->decodeObjectToSDOTyped($item, $new_sdo);
        } else if ($item_type == "array") {
            //$new_sdo = $sdo->createDataObject($item_name);
            $this->decodeArrayToSDOTyped($item_name, $item, $sdo);
        } else {
            $sdo[$item_name] = $item;
        }
    }

    /**
     * Decodes XMLRPC object to SDO using a generic type model.
     *
     * This is the most basic implementation of this function possible
     * in that all it does is recurse around the PHP array that
     * results from using the xmlrpc_decode() function and creates
     * an SDO data graph based on the GenericType defined above.
     * This process makes some assumptions
     * 1) an associative array maps directly to an SDO object
     * 2) a numerically indexed array maps to an SDO array
     *
     * The next stage of complexity is to do a two pass generation
     * of the SDO
     *  Pass 1 - recurse through the PHP object guessing the type
     *           of each object and construct a type model to match
     *  Pass 2 - recurse through the PHP object again creating
     *           an SDO data graph based on the type model from Pass 1
     *           and the data from the PHP oject
     *
     * @param object $object Object
     * @param SDO    $sdo    SDO
     *
     * @return null
     */
    protected function decodeObjectToSDO($object, $sdo)
    {
        foreach ($object as $param_name => $param_value) {
            $this->decodeToSDO($param_name, $param_value, $sdo);
        }
    }

    /**
     * A recursive function that copies PHP arrays to an SDO data graph
     *
     * @param string $array_name Name
     * @param array  $array      Target array
     * @param object $sdo        ServiceDataObject
     *
     * @return null
     */
    protected function decodeArrayToSDO($array_name, $array, $sdo)
    {
        $index = 0;
        foreach ($array as $array_entry) {
            $array_index = $array_name . $index;
            $this->decodeToSDO($array_index, $array_entry, $sdo);
            $index = $index + 1;
        }
    }

    /**
     * Makes the decision on how the PHP object should be copied
     * and recurse as necessary
     *
     * @param string $item_name Name
     * @param string $item      Item
     * @param SDO    $sdo       SDO
     *
     * @return null
     */
    protected function decodeToSDO($item_name, $item, $sdo)
    {
        $item_type = $this->gettype($item);

        if ($item_type == "object") {
            $new_sdo = $this->data_factory->create($this->default_namespace, 'GenericType');
            $sdo[$item_name] = $new_sdo;
            $this->decodeObjectToSDO($item, $new_sdo);
        } else if ($item_type == "array") {
            $new_sdo = $this->data_factory->create($this->default_namespace, 'GenericType');
            $sdo[$item_name] = $new_sdo;
            $this->decodeArrayToSDO($item_name, $item, $new_sdo);
        } else {
            $sdo[$item_name] = $item;
        }
    }

    /**
     * Create an SDO data object of the specified namespace/typename
     * 1) If this DAS contains an XMLDAS, first attempt to create the object using the XMLDAS
     * 2) If that does not succeed, attempt to create the object using the typelist obtained
     *    from the XMLRPC server using system.describeMethods
     * 3) If 1) and 2) fail, create an object of generic type.
     *
     * @param string $root_namespace Root namesapce
     * @param string $root_type      Root type
     *
     * @return null
     */
    public function createDataObject($root_namespace, $root_type)
    {

        $done = false;

        try {
            if ($this->xml_das != null) {
                $sdo = $this->xml_das->createDataObject($root_namespace, $root_type);
                $done = true;

            }

        } catch (Exception $e) {
        }

        if (!$done) {
            try {
                if ($root_namespace == null)
                    $root_namespace = $this->default_namespace;
                $sdo = $this->data_factory->create($root_namespace, $root_type);
                $done = true;

            } catch (Exception $e) {
            }
        }

        if (!$done && $root_namespace !== $this->default_namespace) {
            try {
                $root_namespace = $this->default_namespace;
                $sdo = $this->data_factory->create($root_namespace, $root_type);
                $done = true;
            } catch (Exception $e) {
            }
        }

        if (!$done) {
            $sdo = $this->data_factory->create($this->default_namespace, 'GenericType');
        }

        return $sdo;
    }

}
