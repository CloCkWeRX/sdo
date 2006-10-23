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
$Id$
*/


/**
 * SDO Json Data Access Service.
 * 
 * This is a very simple implementation of a DAS that allows
 * SDOs to be converted to and from JSON. All of the hard work is done 
 * by the JSON PECL extension (http://pecl.php.net/package/json)
 * 
 */

if ( ! class_exists('SDO_DAS_Json', false) ) {
    class SDO_DAS_Json {

        //private $xml_das;
        private $data_factory;

        /**
         * Builds a SDO_DAS_DataFactory containing a single generic open type
         * This is required when converting JSON into an SDO. As JSON provides
         * no schema for the data we are going to read so this is the most basic
         * geneic type model. 
         */
        public function __construct()
        {
            // as some point we want to give the option to 
            // be able to provide a real schema for the SDO
            // that is going to be generated
            //$xml_das     = SDO_DAS_XML::create(some schema);
        
            // construct a generic type model hat handles any type of 
            $this->data_factory   = SDO_DAS_DataFactory::getDataFactory();
        
            $this->data_factory->addType('GenericNS', 
                                         'GenericType', 
                                         array('open'=>true));  
        }

        /**
         * Take a JSON string and convert it into an SDO
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
        public function decode($jsonString)
        {          	
            // decode json string into PHP variable representation
            $json = json_decode($jsonString);
            //print_r($json);
               
            // copy the rpc elements into an SDO object. We force any
            // top level object here to be an SDO object on the assumption
            // that we will not be passed fragments of JSON. I.e. $jason_string
            // will always start with '{' and end with '}'
            $sdo = $this->data_factory->create('GenericNS', 'GenericType');
            $this->_decodeObjectToSDO($json, $sdo);
            //print_r($sdo);
        
            return $sdo;
        }
	
        /**
         * Loops round all of the properties in a PHP object arranging for them 
         * to be copied into an SDO
         */
        private function _decodeObjectToSDO($object, $sdo)
        {
            foreach ( $object as $param_name => $param_value ) {
                $this->_decodeToSDO($param_name, $param_value, $sdo);
            }
        }
    
        /**
         * A recursive function that copies PHP arrays to an SDO data graph
         */
        private function _decodeArrayToSDO($array_name, $array, $sdo)
        {
            $index = 0;
            foreach ( $array as $array_entry ) {
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
/*
            $debug = "Name: " . $item_name . " Value: " . $item . " Type: ". $item_type . "\n";      
            file_put_contents("json_messages.txt", 
                              $debug,
                              FILE_APPEND);              
*/        
            if ( $item_type == "object" ) {
                $new_sdo = $this->data_factory->create('GenericNS', 'GenericType');
                $sdo[$item_name] = $new_sdo;
                $this->_decodeObjectToSDO($item, $new_sdo);
            } else if ( $item_type == "array" ) {
                $new_sdo = $this->data_factory->create('GenericNS', 'GenericType');
                $sdo[$item_name] = $new_sdo;
                $this->_decodeArrayToSDO($item_name, $item, $new_sdo);
            } else {
                $sdo[$item_name] = $item;
                // Could do some work here to determine the type of the parameter
                // from string/number/boolean. PHP thinks they are all strings
            }
        }     
	
        /**
         * The encoding process is simple as we rely entirly on the 
         * json_encode funtion from the JSON PECL package and SDO's ability
         * to pretend to be an array
         */
/*        
        public function encode_old ( $sdo )
        {
            return json_encode($sdo);
        }
*/        
        public function encode ( $sdo )
        {
            $json_string = null;
          
            $this->_encodeObjectFromSDO($sdo, $json_string);
         
            return $json_string;
        }     
        
        private function _encodeObjectFromSDO ( $sdo, &$json_string )
        {
            $json_string .= "{";
          
            $reflection = new SDO_Model_ReflectionDataObject($sdo);
            $sdo_type   = $reflection->getType(); 

            $last = end($sdo);
            reset($sdo);
            foreach ( $sdo as $property_name => $property_value ) {
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
                    if ( $sdo_type->isOpenType() == true ) {
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
                        if ( $php_type == "object") {
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
                
                if ( $is_array ) {
                    // it's an array
                    $this->_encodeArrayFromSDO($property_value, $json_string, $sdo_property_type);
                } else if ( $is_object ) {
                    // it's an object
                    $this->_encodeObjectFromSDO($property_value, $json_string);
                } else {
                    // it's a primitive type
                    $this->_encodePrimitiveFromSDO($property_value, $json_string, $sdo_property_type_name);
                }
                
                if ( $property_value != $last ) {
                    $json_string .= ",";
                }
            }
	  
            $json_string .= "}";
        }
        
        private function _encodeArrayFromSDO ( $sdo, &$json_string, $sdo_type )
        {
            $json_string .= "[";   
            
            if ( $sdo_type->isDataType() == true )
            {
                // it's an array of primitives
                $last = end($sdo);
                reset($sdo);
                foreach ( $sdo as $property_name => $property_value ) {
                    $this->_encodePrimitiveFromSDO($property_value, $json_string, $sdo_type);
                    if ( $property_value != $last ) {
                        $json_string .= ",";
                    }
                }                
            } else {
                // it's an array of objects
                $last = end($sdo);
                reset($sdo);
                foreach ( $sdo as $property_name => $property_value ) {
                    $this->_encodeObjectFromSDO($property_value, $json_string);
                     if ( $property_value != $last ) {
                        $json_string .= ",";
                    }
                }
            }

            $json_string .= "]";
        }
        
        private function _encodePrimitiveFromSDO ( $sdo, &$json_string, $type_name )
        {
            // Note. both PHP and SDO primitive types are included here to 
            //       cover the open type case where I can't predice from the 
            //       model what might have been added to the SDO
            switch ($type_name) {
                case "Boolean":

                case "boolean":
                    if ( $sdo == true ) {
                        $json_string .= "true";
                    } else {
                        $json_string .= "true"; 
                    }
                    break;
                case "Byte":
                case "Bytes":
                case "Character":
                case "Date":
                case "String":
                case "URI":

                case "string":
                    $json_string .= "\"" . $sdo . "\"";
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
        
        public function __destruct()
        {
        }
    }
}
?>
