<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";


include_once "SCA/SCA.php";
require_once 'SCA/Bindings/soap/ServiceDescriptionGenerator.php';
require_once 'SCA/Bindings/soap/Mapper.php';
require_once 'SCA/Bindings/soap/Proxy.php';

class SCA_Bindings_soap_MapperTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if ( ! class_exists('SCA_Bindings_soap_Proxy')) {
            $this->markTestSkipped("Cannot execute any SCA soap tests as the SCA soap binding is not loaded");
            return;
        }
        $xsd = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<schema xmlns="http://www.w3.org/2001/XMLSchema"
        targetNamespace="PersonNamespace"
        xmlns:AuthorNS="PersonNamespace">
  <complexType name="personType">
    <sequence>
      <element name="name" type="string"/>
      <element name="dob" type="string"/>
      <element name="pob" type="string"/>
    </sequence>
  </complexType>
</schema>
EOF;
        file_put_contents('person.xsd',$xsd);

        $php = <<<EOF
<?php
/**
 * @service
 * @binding.soap
 */
class TypeHandlerTest1 {
    /**
     * @param string \$a
     * @param float \$b
     * @param integer \$c
     * @param boolean \$d
     * @return string
     */
    public function fourargs() {}
}
?>
EOF;
        $class_file = './TypeHandlerTest1.php';
        file_put_contents($class_file, $php);
        $service_description = SCA::constructServiceDescription($class_file);
        $wsdl = SCA_Bindings_soap_ServiceDescriptionGenerator::generateDocumentLiteralWrappedWsdl($service_description);
        file_put_contents('TypeHandlerTest1.wsdl', $wsdl);

        $php = <<<EOF
<?php
/**
 * @service
 * @binding.soap
 * @types PersonNamespace person.xsd
 */
class TypeHandlerTest2 {
    /**
     * @param personType \$p1 PersonNamespace
     * @return personType PersonNamespace
     */
    public function myMethod() {}
}
?>
EOF;
        $class_file = './TypeHandlerTest2.php';
        file_put_contents($class_file, $php);
        $service_description = SCA::constructServiceDescription($class_file);
        $wsdl = SCA_Bindings_soap_ServiceDescriptionGenerator::generateDocumentLiteralWrappedWsdl($service_description);
        file_put_contents('TypeHandlerTest2.wsdl', $wsdl);
}

public function tearDown()
{
    unlink('./TypeHandlerTest1.php');
    unlink('TypeHandlerTest1.wsdl');
    unlink('./TypeHandlerTest2.php');
    unlink('TypeHandlerTest2.wsdl');
    unlink('person.xsd');
}

public function testToXmlGeneratesGoodXmlFromSdoWithScalars()
{
    $th = new SCA_Bindings_soap_Mapper('SoapClient');
    $th->setWSDLTypes('TypeHandlerTest1.wsdl');
    $request = $th->createDataObject('http://TypeHandlerTest1','fourargs');
    $request->a = 'hello';
    $request->b = 1.1;
    $request->c = 99;
    $request->d = true;
    $xml = $th->toXML($request);

    $this->assertContains('<?xml version="1.0" encoding="UTF-8"?>',$xml);
    $this->assertContains('<BOGUS',$xml);
    $this->assertContains('<tns2:a>hello</tns2:a>',$xml);
    /* there's a difference in the number of trailing zeros which seems to be platform-specific */
    $this->assertRegExp('?<tns2:b>1\.100e\+0*</tns2:b>?', $xml);
    $this->assertContains('<tns2:c>99</tns2:c>',$xml);
    $this->assertContains('<tns2:d>true</tns2:d>',$xml);
    $this->assertContains('</BOGUS',$xml);
}

public function testToXmlGeneratesGoodXmlFromSdoWithSdos()
{
    $th = new SCA_Bindings_soap_Mapper('SoapClient');
    $th->setWSDLTypes('TypeHandlerTest2.wsdl');
    $request = $th->createDataObject('http://TypeHandlerTest2','myMethod');
    $person = $request->createDataObject('p1');
    $person->name = 'William Shakespeare';
    $person->dob = 'April 1564, most likely 23rd';
    $person->pob = 'Stratford-upon-Avon, Warwickshire';
    $xml = $th->toXML($request);

    $this->assertContains('<?xml version="1.0" encoding="UTF-8"?>',$xml);
    $this->assertContains('<BOGUS',$xml);
    $this->assertContains('<tns2:p1>',$xml);
    $this->assertContains('<name>William Shakespeare</name>',$xml);
    $this->assertContains('<dob>April 1564, most likely 23rd</dob>',$xml);
    $this->assertContains('<pob>Stratford-upon-Avon, Warwickshire</pob>',$xml);
    $this->assertContains('</tns2:p1>',$xml);
    $this->assertContains('</BOGUS',$xml);

}

public function testToXmlHandlesNullsInSdo()
{
    $th = new SCA_Bindings_soap_Mapper('SoapClient');
    $th->setWSDLTypes('TypeHandlerTest1.wsdl');
    $request = $th->createDataObject('http://TypeHandlerTest1','fourargs');
    $request->a = null;
    $request->b = null;
    $request->c = null;
    $request->d = null;
    $xml = $th->toXML($request);

    $this->assertContains('<?xml version="1.0" encoding="UTF-8"?>',$xml);
    $this->assertContains('<BOGUS',$xml);
    $this->assertContains('<tns2:a xsi:nil="true"/>',$xml);
    $this->assertContains('<tns2:b xsi:nil="true"/>',$xml);
    $this->assertContains('<tns2:c xsi:nil="true"/>',$xml);
    $this->assertContains('<tns2:d xsi:nil="true"/>',$xml);
    $this->assertContains('</BOGUS',$xml);
}

public function testFromXmlGeneratesGoodSdoFromXml()
{

    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fourargs xmlns="http://TypeHandlerTest1" xsi:type="fourargs" xmlns:tns="http://TypeHandlerTest1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<a>hello</a>
<b>1.100e+000</b>
<c>99</c>
<d>true</d>
</fourargs>

XML;

    $th = new SCA_Bindings_soap_Mapper('SoapServer');
    $th->setWSDLTypes('TypeHandlerTest1.wsdl');
    $sdo = $th->fromXML($xml);
    $this->assertEquals('fourargs',$sdo->getTypename());
    $this->assertEquals('hello',$sdo->a);
    $this->assertEquals(1.1,$sdo->b,null,0.001);
    $this->assertEquals(99,$sdo->c);
    $this->assertEquals(true,$sdo->d);
}

public function testToXmlHandlesNullInXml()
{

    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fourargs xmlns="http://TypeHandlerTest1" xsi:type="fourargs" xmlns:tns="http://TypeHandlerTest1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<a xmlns="http://TypeHandlerTest1" xsi:nil="true"/>
<b xmlns="http://TypeHandlerTest1" xsi:nil="true"/>
<c xmlns="http://TypeHandlerTest1" xsi:nil="true"/>
<d xmlns="http://TypeHandlerTest1" xsi:nil="true"/>
</fourargs>

XML;

    $th = new SCA_Bindings_soap_Mapper('SoapServer');
    $th->setWSDLTypes('TypeHandlerTest1.wsdl');
    $sdo = $th->fromXML($xml);
    $this->assertEquals('fourargs',$sdo->getTypename());
    $this->assertEquals(null,$sdo->a);
    $this->assertEquals(null,$sdo->b);
    $this->assertEquals(null,$sdo->c);
    $this->assertEquals(null,$sdo->d);
}

public static function main()
{
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("SCA_Bindings_soap_MapperTest");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
}

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_Bindings_soap_MapperTest::main");
    SCA_Bindings_soap_MapperTest::main();
}

?>
