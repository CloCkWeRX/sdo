<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006, 2007.                                   |
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
$Id: SCA_GenerateSmd.php 235424 2007-05-11 15:53:57Z mfp $
*/
require_once 'SCA/SCA_ServiceDescription.php';



class SCA_GenerateSmd {

    private static $xmldas;

    /**
     *  Filter the service desrciption (which is derived
     *  from the annotations in the service class) to produce
     *  a "Simple Method Description" object for use by
     *  JSON-RPC (well the DOJO flavour of it anyhow)
     */
    public static function generateSmd($service_desc)
    {
        // an array of types used to ensure that complex
        // type descriptions are only written out once
        // this is separate from the smd object that is
        // being built up so that it doesn't get written
        // out to JSON at the end
        $type_array = array();

        // construct an XMLDAS base on the XSDs in the
        // service description
        //
        // TODO - this xmldas could be provided as part of the
        //        service description. I would like to see a class
        //        hierarchy in PHP that holds the service
        //        description and can be cached if necessary
        self::$xmldas = SCA_Helper::getXmldasFormXsdArray(
        $service_desc->class_name,
        $service_desc->xsd_types);

        // work out what the hostname is so that the
        // url can be constructed
        $http_host = null;
        if ( isset($_SERVER['HTTP_HOST']) ) {
            $http_host = $_SERVER['HTTP_HOST'];
        } else {
            $http_host = "localhost";
        }

        $smd              = new stdClass;
        $smd->SMDVersion  = ".1";
        $smd->serviceType = "JSON-RPC";
        $smd->serviceURL  = str_replace(' ', '%20', "http://" . $http_host . $_SERVER['SCRIPT_NAME']);
        $smd->methods     = array();

        foreach ( $service_desc->operations as $operation_name => $operation ) {
            $method              = new stdClass;
            $method->name        = $operation_name;
            $method->parameters  = array();
            $smd->methods[]      = $method;

            if (array_key_exists("parameters", $operation) &&
            $operation["parameters"]) {
                // create the method parameters entries
                foreach ( $operation["parameters"] as $parameter ) {
                    $param                = new stdClass;
                    $param->name          = $parameter["name"];
                    if ( array_key_exists('objectType', $parameter) ) {
                        $param->type      = $parameter["objectType"];
                        self::generateSmdType($smd,
                        $parameter["objectType"],
                        $parameter["namespace"],
                        $type_array); // add the type info to the smd
                    } else {
                        $param->type = $parameter["type"];
                    }
                    $method->parameters[] = $param;
                }
            }

            if ( array_key_exists("return", $operation) &&
            $operation["return"] ) {
                // create the method return type entries
                foreach ( $operation["return"] as $return ) {
                    $rtn = new stdClass;
                    if ( array_key_exists('objectType', $return) ) {
                        $rtn->type = $return["objectType"];
                        self::generateSmdType($smd,
                        $return["objectType"],
                        $return["namespace"],
                        $type_array); // add the type info to the smd
                    } else {
                        $rtn->type = $return["type"];
                    }
                    $method->return = $rtn;
                }
            }
        }

        // turn the smd into JSON format
        $str = json_encode($smd);

        // TODO - hack to remove the encoded /s from the encoded string
        $str = str_replace("\/", "/", $str);

        return $str;
    }

    /**
     * Here we contruct an extension to the SMD where types are
     * described using a simple hierarchy. Dojo ignores this at
     * present but we can take account of it in SCA
     */
    public static function generateSmdType($smd, $type_name, $namespace, &$type_array)
    {
        // ensure that there is a types array. We only generate
        // a types array at this late stage so that we only generate
        // one if there are types to add to it
        if ( isset($smd->types) == false ) {
            $smd->types       = array();
        }

        $composite_type_name = $namespace . ":" . $type_name;

        // ensure that types are only written out once
        if ( !array_key_exists($composite_type_name, $type_array) ) {
            $type                             = new stdClass;
            $smd->types[]                     = $type;
            $type_array[$composite_type_name] = $type;

            $type->name                       = $type_name;
            $type->typedef                    = new stdClass;
            $type->typedef->properties        = array();

            // create a data object of the required type
            // TODO - we shouldn't have to do this but I can't work out
            //        how to get the named type from the data factory
            $do = self::$xmldas->createDataObject($namespace, $type_name);

            // get the type information for the data object
            $reflection = new SDO_Model_ReflectionDataObject($do);
            $do_type    = $reflection->getType();

            // test to make sure this is not a primitive type
            if ($do_type->isDataType()) {
                // shouldn't get here I don't think
                // what to do - raise an error?
            }

            // iterate over the properties of this type
            foreach ($do_type->getProperties() as $property) {
                $property_name           = $property->getName();
                $property_type           = $property->getType();
                $property_type_name      = $property_type->getName();
                $property_type_namespace = $property_type->getNamespaceURI();

                // create the entry for the type
                $property_object         = new stdClass;
                $property_object->name   = $property_name;

                // convert from SDO property type names to JSON property type names
                $property_object->type   = self::sdoTypeToSmdType($property_type_name);

                // work out if this is an array or not
                if ( $property->isMany() ) {
                    $property_object->type .= " []";
                }

                // store away the property in the typedef
                $type->typedef->properties[] = $property_object;

                // If this type is a complex type then recurse.
                if (!$property_type->isDataType()) {
                    self::generateSmdType($smd, $property_type_name,
                    $property_type_namespace, $type_array);
                }
            }
            // TODO - what to do about non-containment references
        }
    }

    /**
     * Convert SDO simple type names to SMD type names
     */
    public static function sdoTypeToSmdType($sdo_type_name)
    {
        $smd_type_name = $sdo_type_name;

        switch ($sdo_type_name) {
            case "Boolean":
                $smd_type_name = "bit";
                break;
            case "Byte":
            case "Bytes":
            case "Character":
            case "Date":
            case "String":
            case "URI":
                $smd_type_name = "str";
                break;
            case "BigDecimal":
            case "BigInteger":
            case "Double":
            case "Float":
            case "Integer":
            case "Long":
            case "Short":
                $smd_type_name = "num";
                break;
        }

        return $smd_type_name;
    }
}
