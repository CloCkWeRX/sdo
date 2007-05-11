<?php

/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005, 2007.                            |
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
| Author: Graham Charters                                              |
|         Caroline Maynard                                             |
|         Matthew Peters                                               |
+----------------------------------------------------------------------+

*/
require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'SDOWSDLProcessingTest::main');
}

class SDOWSDLProcessingTest extends PHPUnit_Framework_TestCase {


    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";
        $suite  = new PHPUnit_Framework_TestSuite("SDOWSDLProcessingTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }


    public function __construct($name) {
        parent :: __construct($name);
    }

    public function setUp() {
        // is the extension loaded ?
        $loaded = extension_loaded('sdo');
        $this->assertTrue($loaded, 'php_sdo extension is not loaded.');

        // are we using the matched extension version?
        $version = phpversion('sdo');
        $this->assertTrue(($version >= '1.2.1'), 'Incompatible version of php_sdo extension.');

    }

    public function tearDown() {
        // Can add test case cleanup here.  PHPUnit2_Framework_TestCase will automatically call it
    }
    
    public function printSdoType($sdo) {
        $reflection = new SDO_Model_ReflectionDataObject($sdo);
        $sdo_type   = $reflection->getType();
        $this->printType($sdo_type);
    }

    public function printType($type)
    {
        echo $type->getNamespaceURI() .
             ":" .
             $type->getName() .
             "\n";
        
        // iterate over the properties of this type 
        foreach ($type->getProperties() as $property) {
            echo $type->getNamespaceURI() .
                 ":" .
                 $type->getName() . 
                 " " . 
                 $property->getName() . 
                 " isMany= " .
                 $property->isMany() .
                 " " .
                 $property->getType()->getNamespaceURI() .
                 ":" .
                 $property->getType()->getName() .
                 " isOpen= " . 
                 $property->getType()->isOpenType() .
                 " isDataType= " .
                 $property->getType()->isDataType() .
                 "\n";                   
          
            // If this type is a complex type then recurse. 
            if (!$property->getType()->isDataType()) {
                $this->printType($property->getType());
            }  
        }
    }    

    // Test reading the WSDL xsds
    public function testWsdlXsdRead() {
        $xmldas = SDO_DAS_XML::create();
        $xmldas->addTypes(dirname(__FILE__) ."/../../../../SCA/Bindings/soap/soap/2003-02-11.xsd");
        $doc         = $xmldas->createDocument();
        $definitions = $doc->getRootDataObject();
        $reflection  = new SDO_Model_ReflectionDataObject($definitions);
        $sdo_type    = $reflection->getType();    
        $type_name   = $sdo_type->getName();
        $this->assertEquals($type_name, 'tDefinitions', 'SCA/Bindings/soap/soap/2003-02-11.xsd not read correctly');
        //$this->printSdoType($definitions);
    }

}
if (PHPUnit_MAIN_METHOD == 'SDOWSDLProcessingTest::main') {
    SDOAPITest::main();
}

?>
