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
| Author: SL                                                           |
+----------------------------------------------------------------------+
$Id: Json.php 222906 2006-11-06 16:56:17Z slaws $
*/


/**
 * SDO Json Data Access Service.
 *
 * This is a very simple implementation of a DAS that allows
 * SDOs to be converted to and from JSON. All of the hard work is done
 * by the JSON PECL extension (http://pecl.php.net/package/json)
 *
 */
class SDO_DAS_Json {

    //--------------------------------------------------------------------
    // Member variables
    //--------------------------------------------------------------------

    // Some constants uses in the decoding processing
    private $sdo_namespace     = "commonj.sdo";
    private $default_namespace = "GenericNS";

    // The SDO models used to generate the SDO when
    // a json string is decoded. These are mutually
    // exclusive at the moment in that you cannot
    // use SMD and XSD at the same time to define
    // a type model. This restriction is simply because
    // I can't get the data factory from the XML DAS
    // using the SDO API
    // TODO - I can't get the data factory from the
    //        XML DAS at present (need SDO API fix) so
    //        I have to create separate data factory and
    //        XML DAS depending on whether types
    //        are SMD or XSD defined
    private $data_factory      = null;
    private $xml_das           = null;

    // Flags to control the mutual exclusive nature of
    // the three model types
    // TODO - with SDO API changes to allow us to get
    //        data factory from XMLDAS we shouldn't
    //        need these
    private $is_smd_model      = false;
    private $is_xsd_model      = false;
    private $is_generic_model  = false;

    //--------------------------------------------------------------------
    // Object construction
    //--------------------------------------------------------------------

    /**
     * TODO - Builds a SDO_DAS_DataFactory containing a single generic open type
     * This is required when converting JSON into an SDO. As JSON provides
     * no schema for the data we are going to read so this is the most basic
     * geneic type model.
     */
    public function __construct()
    {
    }

    //--------------------------------------------------------------------
    // Accessors
    //--------------------------------------------------------------------
    public function isSmdModel()
    {
        return $this->is_smd_model;
    }

    public function isXsdModel()
    {
        return $this->is_xsd_model;
    }

    public function getDataFactory()
    {
        return $this->data_factory;
    }

    public function getXmlDas()
    {
        return $this->xml_das;
    }

    //--------------------------------------------------------------------
    // Functions that allow type information to be passed in
    //--------------------------------------------------------------------


    /**
     * @param string $smd_string
     * @param array  $namespace_map   takes the form "typename" => "namespace"
     */
    public function addTypesSmdString($smd_string, $namespace_map = null)
    {
        if ($this->is_xsd_model == true ) {
            // XSD has already been used to build part of the type
            // model so we can't now switch to using SMD
            throw SDO_Exception('Cannot add SMD types to the JSON DAS because XSD types have already been added');
        }

        if ($this->is_generic_model == true ) {
            // XSD has already been used to build part of the type
            // model so we can't now switch to using SMD
            throw SDO_Exception('Cannot add SMD types to the JSON DAS because the DAS has already been used to parse JSON generically');
        }

        $this->is_smd_model = true;

        if ($this->data_factory == null ) {
            $this->data_factory = SDO_DAS_DataFactory::getDataFactory();
        }

        if ($smd_string != null ) {
            $this->_parseSmd($smd_string, $namespace_map);
        }
    }

    public function addTypesSmdFile($smd_file = null, $namespace_map = null)
    {
        $smd_string   = file_get_contents($smd_file);
        $this->addTypesSmdString($smd_string, $namespace_map);
    }

    public function addTypesXsdFile($xsd_file)
    {
        if ($this->is_smd_model == true ) {
            // SMD has already been used to build part of the type
            // model so we can't now switch to using XSD
            throw SDO_Exception('Cannot add XSD types to the JSON DAS because SMD types have already been added');
        }

        if ($this->is_generic_model == true ) {
            // XSD has already been used to build part of the type
            // model so we can't now switch to using SMD
            throw SDO_Exception('Cannot add XSD types to the JSON DAS because the DAS has already been used to parse JSON generically');
        }

        $this->is_xsd_model = true;

        if ($this->xml_das == null ) {
            $this->xml_das = SDO_DAS_XML::create();
        }

        if ($xsd_file != null ) {
            $this->xml_das->addTypes($xsd_file);
        }
    }

    //--------------------------------------------------------------------
    // Generate model information from an SMD file
    //--------------------------------------------------------------------

    private function _parseSmd($smd_string, $namespace_map)
    {
        $smd = json_decode($smd_string);

        // check if any types have been specified in the SMD file.
        // types will only appear if complex types are used
        // simple types map directly to the SDO basic types
        if ( isset($smd->types) ) {
            $smd_types = $smd->types;

            // iterate over the types adding types to the
            // SDO model
            foreach ($smd_types as $type ) {
                $type_name = $type->name;
                $namespace = $this->default_namespace;

                // look type up in namespace map
                if ($namespace_map != null &&
                     array_key_exists($type_name, $namespace_map) ) {
                    $namespace = $namespace_map[$type_name];
                }

                // TODO - debugging
                //echo "Add Type $namespace:$type_name\n";

                // create the type
                // TODO - assuming they are all open types at
                //        the moment as I can;t detect this from
                //        the SMD

                $this->data_factory->addType($namespace,
                                             $type_name,
                                             array('open'=>true));
            }

            // iterate over the types adding properties to the types
            // that have already been created. This is a two pass
            // process because the property type must exist before
            // we create the property of that type
            foreach ($smd_types as $type ) {
                $type_name = $type->name;
                $namespace = $this->default_namespace;

                // look type up in namespace map
                if ($namespace_map != null &&
                     array_key_exists($type_name, $namespace_map) ) {
                    $namespace = $namespace_map[$type->name];
                }

                // create the properties of the type
                foreach ($type->typedef->properties as $property ) {
                    $property_name      = $property->name;
                    $property_type      = $property->type;
                    $property_namespace = $this->default_namespace;
                    $is_array           = false;

                    // work out whether the property is many
                    // valued
                    $array_pos          = strpos($property_type, "[]");

                    if ($array_pos !== false ) {
                        // it is an array so strip out the array
                        // markers
                        $property_type = substr($property_type, 0, $array_pos);
                        $is_array      = true;
                    }

                    // strip and whitespace from begining and end of property type
                    $property_type = trim($property_type);

                    // if this is a primitive type then we need to
                    // convert to the SDO primitive types
                    $converted_property_type = $this->_smdTypeToSdoType($property_type);

                    // fix up the namespace
                    if ($converted_property_type === $property_type ) {
                        // the type name wasn't changed so it's a
                        // complex type. Map the namespace to see
                        // if the user has told us what namespace this
                        // typename should have
                        if ($namespace_map != null &&
                             array_key_exists($property_type, $namespace_map) ) {
                            $property_namespace = $namespace_map[$property_type];
                        }
                    } else {
                        // its a primitive type so use the SDO namespace
                        $property_namespace = $this->sdo_namespace;
                    }

                    // TODO - debugging
                    //echo "Add Property $property_name of type $property_namespace:$converted_property_type many=$is_array to $namespace:$type_name\n";

                    $this->data_factory->addPropertyToType($namespace,
                                                           $type_name,
                                                           $property_name,
                                                           $property_namespace,
                                                           $converted_property_type,
                                                           array('containment'=>true,
                                                                 'many'       =>$is_array));
                }
            }
        }
    }

    private function _smdTypeToSdoType($smd_type_name)
    {
        $sdo_type_name = $smd_type_name;

        switch ($smd_type_name) {
        case "bit":
            $sdo_type_name = "Boolean";
            break;
        case "str":
            $sdo_type_name = "String";
            break;
        case "num":
            $sdo_type_name = "Integer";
/* TODO - difficult to know what to do about numbers
as they could be floats etc and SDO treats them
differently. We really need to parse the data as
it comes in but by then the model is set
        case "BigDecimal":
        case "BigInteger":
        case "Double":
        case "Float":
        case "Integer":
        case "Long":
        case "Short":
*/
            break;
        }

        return $sdo_type_name;
    }

    //--------------------------------------------------------------------
    // Generate generic model information from a JSON message
    //--------------------------------------------------------------------

    // TODO - not sure we actually need this because I want to get
    //        rid of the generic parse

    //--------------------------------------------------------------------
    // Decode a JSON message to an SDO
    //--------------------------------------------------------------------

    /**
     * Take a JSON string and convert it into an SDO
     */
    public function decode($jsonString, $root_type = null, $root_namespace = null )
    {
        // decode json string into PHP variable representation
        $json = json_decode($jsonString);

        return $this->decodeFromPHPObject($json, $root_type, $root_namespace);
    }

    /**
     * Take a PHP Object and convert it into an SDO
     */
    public function decodeFromPHPObject($json, $root_type = null, $root_namespace = null )
    {
        // copy the rpc elements into an SDO object. We force any
        // top level object here to be an SDO object on the assumption
        // that we will not be passed fragments of JSON. I.e. $jason_string
        // will always start with '{' and end with '}'
        $sdo = null;

        // guess the namespace if one is not provided
        if ($root_namespace == null ) {
            $root_namespace = $this->default_namespace;
        }

        // currently do a different parse if types have specified
        // compared to the generic parse we started with
        // just want to keep the code separate while we move toward
        // the newer type driven parse.
        if ($this->is_smd_model == true &&
             $root_type          != null ) {
            // walk the jason tree creating the correct types based
            // on the model from the specified root type down
            $sdo = $this->data_factory->create($root_namespace, $root_type);
            $this->_decodeObjectToSDONew($json, $sdo);
        } else if ($this->is_xsd_model == true &&
                    $root_type         != null ) {
            // walk the jason tree creating the correct types based
            // on the model from the specified root type down
            $sdo = $this->xml_das->createDataObject($root_namespace, $root_type);
            $this->_decodeObjectToSDONew($json, $sdo);
        } else {
            // either there is no model or there is no root type
            // so we need to create a generic model
            $this->is_generic_model = true;

            if ($this->data_factory == null ) {
                // create an empty data factory
                $this->data_factory = SDO_DAS_DataFactory::getDataFactory();

                // first parse the incomming JSON message to construct
                // a type hierarchy.
                // TODO - I'm cheating here by just using a generic type
                //        for now
                $this->data_factory->addType('GenericNS',
                                             'GenericType',
                                             array('open'=>true));
            }

            // parse the JSON message using this type hierarchy
            $sdo = $this->data_factory->create('GenericNS', 'GenericType');
            $this->_decodeObjectToSDO($json, $sdo);
        }

        return $sdo;
    }

    private function _decodeObjectToSDONew($object, $sdo)
    {
        foreach ($object as $param_name => $param_value ) {
            $this->_decodeToSDONew($param_name, $param_value, $sdo);
        }
    }

    /**
     * A recursive function that copies PHP arrays to an SDO data graph
     */
    private function _decodeArrayToSDONew($array_name, $array, $sdo)
    {
        $array_index = 0;
        foreach ($array as $array_entry ) {
            $array_entry_type = gettype($array_entry);

            //echo "Typed - Array Name: " . $array_name . " Array Index: " . $array_index . " Array Entry: " . $array_entry . " Type: ". $array_entry_type . "\n";

            if ($array_entry_type == "object" ) {
                $new_sdo = $sdo->createDataObject($array_name);
                $this->_decodeObjectToSDONew($array_entry, $new_sdo);
            } else if ($array_entry_type == "array" ) {
                $new_sdo = $sdo->createDataObject($array_name);
                $this->_decodeArrayToSDO($array_name, $array_entry, $new_sdo);
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
     */
    private function _decodeToSDONew($item_name, $item, $sdo)
    {
        $item_type = gettype($item);

        //echo "Typed - Name: " . $item_name . " Value: " . $item . " Type: ". $item_type . "\n";

        if ($item_type == "object" ) {
            $new_sdo = $sdo->createDataObject($item_name);
            $this->_decodeObjectToSDONew($item, $new_sdo);
        } else if ($item_type == "array" ) {
            //$new_sdo = $sdo->createDataObject($item_name);
            $this->_decodeArrayToSDONew($item_name, $item, $sdo);
        } else {
            $sdo[$item_name] = $item;
            // Could do some work here to determine the type of the parameter
            // from string/number/boolean. PHP thinks they are all strings
        }
    }

    /**
     * Decodes JSON string to SDO using a generic type model.
     *
     * This is the most basic implementation of this function possible
     * in that all it does is recurse around the PHP object that
     * results from using the json_decode() function and creates
     * an SDO data graph based on the GenericType defined above.
     * This process makes some assumptions
     * 1/ a JSON object maps directly to an SDO obect
     * 2/ all JSON simple types appear as strings in an SDO object
     * 3/ a JSON array maps to an SDO object whose property names take the form
     *      arrayname#
     *    where arrayname is the name of the JSON property that holds the array
     *          #         is the index of the array item
     *    PHP allows properties to be accessed using an array index so this
     *    kind of works on the way in. There will be problems going back to
     *    JSON though as this will come out as an object with strange parameter
     *    names rather than an array
     *
     * The next stage of complexity is to do a two pass generation
     * of the SDO
     *  Pass 1 - recurse through the PHP object guessing the type
     *           of each object and construct a type model to match
     *  Pass 2 - recurse through the PHP object again creating
     *           an SDO data graph based on the type model from Pass 1
     *           and the data from the PHP oject
     */
    private function _decodeObjectToSDO($object, $sdo)
    {
        foreach ($object as $param_name => $param_value ) {
            $this->_decodeToSDO($param_name, $param_value, $sdo);
        }
    }

    /**
     * A recursive function that copies PHP arrays to an SDO data graph
     */
    private function _decodeArrayToSDO($array_name, $array, $sdo)
    {
        $index = 0;
        foreach ($array as $array_entry ) {
            $array_index = $array_name . $index;
            $this->_decodeToSDO($array_index, $array_entry, $sdo);
            $index = $index + 1;
        }
    }

    /**
     * Makes the decision on how the PHP object should be copied
     * and recurse as necessary
     */
    private function _decodeToSDO($item_name, $item, $sdo)
    {
        $item_type = gettype($item);

//            echo "Generic Name: " . $item_name . " Value: " . $item . " Type: ". $item_type . "\n";
/*
        $debug = "Name: " . $item_name . " Value: " . $item . " Type: ". $item_type . "\n";

        file_put_contents("json_messages.txt",
                          $debug,
                          FILE_APPEND);
*/
        if ($item_type == "object" ) {
            $new_sdo = $this->data_factory->create('GenericNS', 'GenericType');
            $sdo[$item_name] = $new_sdo;
            $this->_decodeObjectToSDO($item, $new_sdo);
        } else if ($item_type == "array" ) {
            $new_sdo = $this->data_factory->create('GenericNS', 'GenericType');
            $sdo[$item_name] = $new_sdo;
            $this->_decodeArrayToSDO($item_name, $item, $new_sdo);
        } else {
            $sdo[$item_name] = $item;
            // Could do some work here to determine the type of the parameter
            // from string/number/boolean. PHP thinks they are all strings
        }
    }


    //--------------------------------------------------------------------
    // Encode an SDO to a JSON message
    //--------------------------------------------------------------------

    /**
     * The encoding process is simple. We iterate across the
     * provided SDO converting as we go. The following mapping
     * is observed
     *   Data object          -> {}
     *   Data obect property  -> "propery name" : property value
     *   Many valued property -> "propery name" : [property value, ...]
     *   Primitive            -> property value
     */
    public function encode ($sdo )
    {
        $json_string = null;

        $this->_encodeObjectFromSDO($sdo, $json_string);

        return $json_string;
    }

    private function _encodeObjectFromSDO ($sdo, &$json_string )
    {
        $json_string .= "{";

        $reflection = new SDO_Model_ReflectionDataObject($sdo);
        $sdo_type   = $reflection->getType();

        $sdo_size = $this->_count($sdo);
        $i        = 0;
        foreach ($sdo as $property_name => $property_value ) {
            $json_string .= "\"" . $property_name . "\":";

            $sdo_property           = null;
            $sdo_property_type      = null;
            $sdo_property_type_name = null;
            $is_array               = false;
            $is_object              = false;

            // get the property entry from the type so that we can.
            // find out if we are dealing with a many valued property.
            // Need to take account of any open types where the named property
            // won't exist in the model
            try {
                $sdo_property           = $sdo_type->getProperty($property_name);
                $sdo_property_type      = $sdo_property->getType();
                $sdo_property_type_name = $sdo_property_type->getName();
                $is_array               = $sdo_property->isMany();
                $is_object              = !$sdo_property_type->isDataType();

            } catch (SDO_PropertyNotFoundException $ex ) {
                if ($sdo_type->isOpenType() == true ) {
                    // We can validly have properties that
                    // don't appear in the model. For now we
                    // assume that these are single valued
                    // so $is_array is left as false.
                    //
                    // We have to go a little further to get the
                    // property type as we can't pluck it directly from
                    // the model (the model doesn't have the type in
                    // it as the model is open).

                    // First take a look at the php type as we can't reflect
                    // on the data unless it is an SDO object (as opposed to
                    // a primitive type)
                    $php_type = gettype($property_value);
                    if ($php_type == "object") {
                        $is_object              = true;
                        $reflection             = new SDO_Model_ReflectionDataObject($property_value);
                        $sdo_property_type      = $reflection->getType();
                        $sdo_property_type_name = $sdo_property_type->getName();
                    } else {
                        $is_object              = false;
                        $sdo_property_type_name = $php_type;
                    }
                } else {
                    // there is a real problem so throw
                    // the exception on
                    throw $ex;
                }
            }

            if ($is_array ) {
                // it's an array
                $this->_encodeArrayFromSDO($property_value, $json_string, $sdo_property_type);
            } else if ($is_object ) {
                // it's an object
                $this->_encodeObjectFromSDO($property_value, $json_string);
            } else {
                // it's a primitive type
                $this->_encodePrimitiveFromSDO($property_value, $json_string, $sdo_property_type_name);
            }

            $i++;
            if ($i < $sdo_size ) {
                $json_string .= ",";
            }
        }

        $json_string .= "}";
    }

    private function _encodeArrayFromSDO ($sdo, &$json_string, $sdo_type )
    {
        $json_string .= "[";

        $sdo_size = $this->_count($sdo);
        $i        = 0;

        if ($sdo_type->isDataType() == true )
        {
            // it's an array of primitives
            foreach ($sdo as $property_name => $property_value ) {
                $this->_encodePrimitiveFromSDO($property_value, $json_string, $sdo_type->getName());
                $i++;
                if ($i < $sdo_size ) {
                    $json_string .= ",";
                }
            }
        } else {
            // it's an array of objects
            foreach ($sdo as $property_name => $property_value ) {
                $this->_encodeObjectFromSDO($property_value, $json_string);
                $i++;
                if ($i < $sdo_size ) {
                    $json_string .= ",";
                }
            }
        }

        $json_string .= "]";
    }

    private function _encodePrimitiveFromSDO ($sdo, &$json_string, $type_name )
    {
        // Note. both PHP and SDO primitive types are included here to
        //       cover the open type case where I can't predict from the
        //       model what might have been added to the SDO
        switch ($type_name) {
            case "Boolean":

            case "boolean":
                if ($sdo == true ) {
                    $json_string .= "true";
                } else {
                    $json_string .= "false";
                }
                break;
            case "Byte":
            case "Bytes":
            case "Character":
            case "Date":
            case "String":
            case "URI":

            case "string":
                $json_string .= "\"" . addslashes($sdo) . "\"";
                break;
            case "BigDecimal":
            case "BigInteger":
            case "Double":
            case "Float":
            case "Integer":
            case "Long":
            case "Short":

            case "integer":
            case "float":
            case "double":
                $json_string .= $sdo;
                break;

            // TODO - what to do about nulls
        }
    }

    /**
     * Temporary count function because using the real count on an
     * SDO returns the number of properties in the model rather than
     * in the data object
     */
    private function _count ($array )
    {
        $i = 0;

        foreach ($array as $item ) {
            $i++;
        }

        return $i;
    }

    public function __destruct()
    {
    }
}
