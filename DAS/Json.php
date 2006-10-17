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
         * 
         * The stage of complexity after this is to allow XML schema to be 
         * passed in to define the shape of the SDO tree that is expected to 
         * be generated. This doesn't sit that easily with JSON RPC as would 
         * would not expect to find an XML schema. We need to do some work 
         * on how complex types might be described for the JSON format.
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
            $this->_copyObjectToSDO($json, $sdo);
            //print_r($sdo);
        
            return $sdo;
        }
	
        /**
         * Loops round all of the properties in a PHP object arranging for them 
         * to be copied into an SDO
         */
        private function _copyObjectToSDO($object, $sdo)
        {
            foreach ( $object as $param_name => $param_value ) {
                $this->_copyToSDO($param_name, $param_value, $sdo);
            }
        }
    
        /**
         * A recursive function that copies PHP arrays to an SDO data graph
         */
        private function _copyArrayToSDO($array_name, $array, $sdo)
        {
            $index = 0;
            foreach ( $array as $array_entry ) {
                $array_index = $array_name . $index;
                $this->_copyToSDO($array_index, $array_entry, $sdo);
                $index = $index + 1;
            }
        }   

        /**
         * Makes the decision on how the PHP object should be copied
         * and recurse as necessary
         */
        private function _copyToSDO($item_name, $item, $sdo)
        {   
            $item_type = gettype($item); 
            //echo "Name: " . $item_name . " Value: " . $item . " Type: ". $item_type . "\n";      
        
            if ( $item_type == "object" ) {
                $new_sdo = $this->data_factory->create('GenericNS', 'GenericType');
                $sdo[$item_name] = $new_sdo;
                $this->_copyObjectToSDO($item, $new_sdo);
            } else if ( $item_type == "array" ) {
                $new_sdo = $this->data_factory->create('GenericNS', 'GenericType');
                $sdo[$item_name] = $new_sdo;
                $this->_copyArrayToSDO($item_name, $item, $new_sdo);
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
        public function encode ( $sdo )
        {
            return json_encode($sdo);
        }
	
        public function __destruct()
        {
        }
    }
}
?>
