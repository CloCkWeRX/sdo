<?php
/*
+----------------------------------------------------------------------+
| Copyright IBM Corporation 2007.                                      |
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
| Author: Rajini Sivaram                                               |
+----------------------------------------------------------------------+
$Id$
*/

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA.php';
require_once 'SCA/Bindings/xmlrpc/Das.php';


class SCA_XmlRpcTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        if (!extension_loaded('xmlrpc')) {
            $this->markTestSkipped('Test skipped as the xmlrpc extension is not loaded');
        }

        $this->xmlrpc_das = new SCA_Bindings_Xmlrpc_DAS();

        $this->setUpXmlRpcTypes($this->xmlrpc_das);
        $this->setUpXsdTypes($this->xmlrpc_das);
    }

    private function setUpXmlRpcTypes($xmlrpc_das)
    {

        $typeList = <<<END
<introspection>
    <typeList>

        <typeDescription name='SimpleType' basetype='struct' desc='Simple type'>
    
            <value type='int' name='intValue'></value>
            <value type='double' name='doubleValue'></value>
            <value type='string' name='strValue'></value>

        </typeDescription>

        <typeDescription name='ComplexType' basetype='struct' desc='Complex type'>

            <value type='SimpleType []' name='objArrValue'></value>
            <value type='int []' name='intArrValue'></value>
            <value type='int' name='intValue'></value>
            <value type='double' name='doubleValue'></value>
            <value type='string' name='strValue'></value>
            <value type='SimpleType' name='objValue'></value>

        </typeDescription>
    </typeList>
</introspection>
END;

        $call = <<<END
<?xml version="1.0" encoding="iso-8859-1"?>
<methodCall>
    <methodName>system.describeMethods</methodName>
    <params/>
</methodCall>
END;


            $xmlrpc_server   = xmlrpc_server_create();
            $descArray = xmlrpc_parse_method_descriptions($typeList);
            xmlrpc_server_add_introspection_data($xmlrpc_server, $descArray);
            $response = xmlrpc_server_call_method($xmlrpc_server, $call, null);
            xmlrpc_server_destroy($xmlrpc_server);
            $methodDesc = xmlrpc_decode_request($response, $method);

            $this->xmlrpc_das->addTypesXmlRpc($methodDesc["typeList"]);

    }

    private function setUpXsdTypes($xmlrpc_das)
    {
        $xsd = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<schema xmlns="http://www.w3.org/2001/XMLSchema"
        targetNamespace="XmlRpcTestNamespace"
        xmlns:tns="XmlRpcTestNamespace"
        xmlns:AuthorNS="XmlRpcTestNamespace">

  <complexType name="SimpleXsdType">
    <sequence>
      <element name="name" type="string"/>
      <element name="value" type="double"/>
    </sequence>
  </complexType>

  <complexType name="ComplexXsdType">
    <sequence>
      <element name="objArrValue" type="tns:SimpleXsdType"  minOccurs="0" maxOccurs="unbounded" />
      <element name="intArrValue" type="int"  minOccurs="0" maxOccurs="unbounded" />
      <element name="intValue" type="int"/>
      <element name="strValue" type="string"/>
      <element name="doubleValue" type="double"/>
      <element name="objValue" type="tns:SimpleXsdType"/>
    </sequence>
  </complexType>
</schema>
END;

        $xsdFile = dirname(__FILE__) . '/types.xsd';
        file_put_contents($xsdFile, $xsd);

        $this->xmlrpc_das->addTypesXsdFile($xsdFile);
    }

    private function areEqual($val, $otherVal) {

        $this->assertEquals(gettype($val), gettype($otherVal));

        if (is_object($val))
            $this->assertEquals(get_class($val), get_class($otherVal));

        if (is_object($val) || is_array($val)) {

            foreach ($val as $name => $value) {

                $this->areEqual($val[$name], $otherVal[$name]);
            }
        }
   
    }

    private function encodeAndDecodePrimitive($val)
    {

        $origVal = $val;
        $xml = xmlrpc_encode_request(null, $origVal);

        $newVal = xmlrpc_decode_request($xml, $method);

        $this->areEqual($val, $newVal);
    }

    private function encodeAndDecode($val, $namespace, $type)
    {

        $origVal = clone $val;
        $xml = xmlrpc_encode_request(null, $origVal);

        $xmlrpcVal = xmlrpc_decode_request($xml, $method);

        $newVal =  $this->xmlrpc_das->decodeFromPHPArray($xmlrpcVal, $namespace, $type);

        $this->areEqual($val, $newVal);

    }

    public function testPrimitives()
    {
        $this->encodeAndDecodePrimitive(23);
        $this->encodeAndDecodePrimitive(23.45);
        $this->encodeAndDecodePrimitive(true);
        $this->encodeAndDecodePrimitive("String value");
    }


    public function testGenericType() 
    {

        $obj = $this->xmlrpc_das->createDataObject("", "GenericType");

        $obj->name = "pi";
        $obj->value = 3.14;

        $this->encodeAndDecode($obj, "", "GenericType");
    }

    public function testXsdType() 
    {

        $obj = $this->xmlrpc_das->createDataObject("XmlRpcTestNamespace", "SimpleXsdType");

        $obj->name = "pi";
        $obj->value = 3.14;

        $simpleObj = $obj;

        $this->encodeAndDecode($obj, "XmlRpcTestNamespace", "SimpleXsdType");

        $obj = $this->xmlrpc_das->createDataObject("XmlRpcTestNamespace", "ComplexXsdType");

        $obj->intValue = 234;
        $obj->doubleValue = 234.567;
        $obj->strValue = "Test Complex xsd based type";
        $obj->objValue = clone $simpleObj;
        $obj->objArrValue[] = clone $simpleObj;
        $obj->objArrValue[] = $this->xmlrpc_das->createDataObject("XmlRpcTestNamespace", "SimpleXsdType");
        $obj->intArrValue[] = 1;
        $obj->intArrValue[] = 2;
        $obj->intArrValue[] = 3;
        
        $this->encodeAndDecode($obj, "XmlRpcTestNamespace", "ComplexXsdType");

    }


    public function testXmlRpcType() 
    {

        $obj = $this->xmlrpc_das->createDataObject("", "SimpleType");

        $obj->intValue = 7;
        $obj->doubleValue = 8.9;
        $obj->strValue = "Test simple xmlrpc-based type";

        $simpleObj = $obj;

        $this->encodeAndDecode($obj, "", "SimpleType");

        $obj = $this->xmlrpc_das->createDataObject("", "ComplexType");

        $obj->intValue = 234;
        $obj->doubleValue = 234.567;
        $obj->strValue = "Test Complex xmlrpc based type";
        $obj->objValue = clone $simpleObj;
        $obj->objArrValue[] = clone $simpleObj;
        $obj->objArrValue[] = $this->xmlrpc_das->createDataObject("", "SimpleType");
        $obj->intArrValue[] = 1;
        $obj->intArrValue[] = 2;
        $obj->intArrValue[] = 3;

        $this->encodeAndDecode($obj, "", "ComplexType");
    }



    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_XmlRpcTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_XmlRpcTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_XmlRpcTest::main");
    SCA_XmlRpcTest::main();
}

?>
