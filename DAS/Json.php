<?php
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005.                                  |
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

class SDO_DAS_Json {

    private $data_factory;

    /**
     * Builds a SDO_DAS_DataFactory containing a single generic open type
     * This is required when converting JSON into an SDO. As JSON provides
     * no schema for the data we are going to read this is the most basic
     * geneic type model. 
     */
    public function __construct()
    {
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
     * We really should something smarter such as a two pass generation 
     * of the SDO
     *  Pass 1 - recurse through the PHP object guessing the type
     *           of each object and construct a type model to match
     *  Pass 2 - recurse through the PHP object again creating 
     *           an SDO data graph based on the type model from Pass 1 
     *           and the data from the PHP oject 
     * or do a proper shema free implementation of data loading in SDO.
     */
    public function decode($jsonString)
    {          	
        // decode json string into PHP variable representation
        $jsonVar = json_decode($jsonString);
        
        $sdo = $this->data_factory->create('GenericNS', 'GenericType');

        $this->_copyObjectToSDO($jsonVar, $sdo);
               
        return $sdo;
    }
	
    /**
     * A recursive function that copies PHP objects and 
     * properties to an SDO data graph
     */
    private function _copyObjectToSDO ( $object, $sdo )
    {
        foreach ( $object as $paramName => $paramValue ) {
            $type =  gettype($paramValue);
                                
            if ( $type == "object" ) {
                $newSdo = $this->data_factory->create('GenericNS', 'GenericType');
                $sdo[$paramName] = $newSdo;
                $this->_copyObjectToSDO($paramValue, $newSdo);
            } else {
                $sdo[$paramName] = $paramValue;
            
                // Could do some work here to determine the type of the parameter
                // from string/number/boolean. PHP thinks they are all strings
            }
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
?>
