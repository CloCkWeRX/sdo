<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA.php';
require_once 'SCA/Bindings/soap/ServiceDescriptionGenerator.php';

class SCA_Bindings_soap_WSDLTest extends PHPUnit_Framework_TestCase
{

    public function testComponentClassnameMustEqualFilename()
    {

        $php = <<<EOF
<?php
/**
 * @service
 * @binding.soap
 */
 class ClassNameDoesNotMatchTheFileName {
 }
?>
EOF;
        $class_file = './Class1.php';
        file_put_contents($class_file, $php);
        try {
            $service_description = SCA::constructServiceDescription($class_file);
            $wsdl = SCA_Bindings_soap_ServiceDescriptionGenerator::generateDocumentLiteralWrappedWsdl($service_description);
        } catch (Exception $e) {
            $this->assertContains("Classname",$e->getMessage());
            unlink($class_file);
            return;
        }
        $this->fail();
    }

    public function testPublicMethodGeneratesElementsForMessage()
    {

        $php = <<<EOF
<?php
/**
 * @service
 * @binding.soap
 */
class Class2 {
    public function hello() {}
}
?>
EOF;
        $class_file = './Class2.php';
        file_put_contents($class_file, $php);
        $service_description = SCA::constructServiceDescription($class_file);
        $wsdl = SCA_Bindings_soap_ServiceDescriptionGenerator::generateDocumentLiteralWrappedWsdl($service_description);
        $this->assertContains('<xs:element name="hello">',$wsdl);
        $this->assertContains('<xs:element name="helloResponse">',$wsdl);
        unlink($class_file);
    }

    public function testScalarTypesGetGeneratedIntoWsdl()
    {

        $php = <<<EOF
<?php
/**
 * @service
 * @binding.soap
 */
class Class3 {
    /**
     * @param string|null \$a
     * @param float \$b
     * @param integer \$c
     * @param boolean \$d
     * @return string|null
     */
    public function fourargs() {}
}
?>
EOF;
        $class_file = './Class3.php';
        file_put_contents($class_file, $php);
        $service_description = SCA::constructServiceDescription($class_file);
        $wsdl = SCA_Bindings_soap_ServiceDescriptionGenerator::generateDocumentLiteralWrappedWsdl($service_description);
        $this->assertContains('<xs:element name="a" type="xs:string" nillable="true"/>',$wsdl);
        $this->assertContains('<xs:element name="b" type="xs:float"/>',$wsdl);
        $this->assertContains('<xs:element name="c" type="xs:integer"/>',$wsdl);
        $this->assertContains('<xs:element name="d" type="xs:boolean"/>',$wsdl);
        $this->assertContains('<xs:element name="fourargsReturn" type="xs:string" nillable="true"/>',$wsdl);
        unlink($class_file);

    }

    public function testSdoTypesGetGeneratedIntoWsdl()
    {

        $php = <<<EOF
<?php
/**
 * @service
 * @binding.soap
 * @types PersonNamespace person.xsd
 */
class Class4 {
    /**
     * @param personType \$p1 PersonNamespace
     * @return personType PersonNamespace
     */
    public function takesSDO() {}
}
?>
EOF;
        $class_file = './Class4.php';
        file_put_contents($class_file, $php);


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

        $service_description = SCA::constructServiceDescription($class_file);
        $wsdl = SCA_Bindings_soap_ServiceDescriptionGenerator::generateDocumentLiteralWrappedWsdl($service_description);
        $this->assertContains('<xs:import schemaLocation="person.xsd" namespace="PersonNamespace"/>',$wsdl);
        $this->assertContains('<xs:element name="p1" type="ns0:personType"/>',$wsdl);
        $this->assertContains('<xs:element name="takesSDOReturn" type="ns0:personType"/>',$wsdl);
        unlink($class_file);
        unlink('person.xsd');


}

    public function testMagicMethodsDontGetGeneratedIntoWsdl()
    {
        $service_description = SCA::constructServiceDescription(dirname(__FILE__) . "/Class5.php");
        $wsdl = SCA_Bindings_soap_ServiceDescriptionGenerator::generateDocumentLiteralWrappedWsdl($service_description);        
        $this->assertNotContains('<operation name="__construct">',$wsdl);
        $this->assertNotContains('<operation name="__destruct">',$wsdl);
        $this->assertNotContains('<operation name="__call">',$wsdl);
        $this->assertNotContains('<operation name="__get">',$wsdl);
        $this->assertNotContains('<operation name="__set">',$wsdl);
        $this->assertNotContains('<operation name="__isset">',$wsdl);
        $this->assertNotContains('<operation name="__unset">',$wsdl);
        $this->assertNotContains('<operation name="__sleep">',$wsdl);
        $this->assertNotContains('<operation name="__wakeup">',$wsdl);
        $this->assertNotContains('<operation name="__toString">',$wsdl);
        $this->assertNotContains('<operation name="__set_state">',$wsdl);
        $this->assertNotContains('<operation name="__clone">',$wsdl);
        $this->assertNotContains('<operation name="__autoload">',$wsdl);  
    }

public static function main()
{
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("SCA_Bindings_soap_WSDLTest");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
}

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_Bindings_soap_WSDLTest::main");
    SCA_Bindings_soap_WSDLTest::main();
}

?>