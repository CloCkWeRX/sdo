<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA.php';

class SCA_WSDLTest extends PHPUnit_Framework_TestCase
{

    public function testComponentClassnameMustEqualFilename()
    {

        $php = <<<EOF
<?php
/**
 * @service
 * @binding.ws
 */
 class ClassNameDoesNotMatchTheFileName {
 }
?>
EOF;
        $class_file = SCA_Helper::getTempDir().'/Class1.php';
        file_put_contents($class_file, $php);
        try {
            $wsdl = SCA::generateWSDL($class_file);
        } catch (Exception $e) {
            $this->assertContains("Classname", $e->getMessage());
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
 * @binding.ws
 */
class Class2 {
    public function hello() {}
}
?>
EOF;
        $class_file = SCA_Helper::getTempDir().'/Class2.php';    
        file_put_contents($class_file, $php);
        $wsdl = SCA::generateWSDL($class_file);
        $this->assertContains('<xs:element name="hello">', $wsdl);
        $this->assertContains('<xs:element name="helloResponse">', $wsdl);
        unlink($class_file);
    }

    public function testScalarTypesGetGeneratedIntoWsdl()
    {

        $php = <<<EOF
<?php
/**
 * @service
 * @binding.ws
 */
class Class3 {
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
        $class_file = SCA_Helper::getTempDir().'/Class3.php';    
        file_put_contents($class_file, $php);
        $wsdl = SCA::generateWSDL($class_file);
        $this->assertContains('<xs:element name="a" type="xs:string" nillable="true"/>', $wsdl);
        $this->assertContains('<xs:element name="b" type="xs:float" nillable="true"/>', $wsdl);
        $this->assertContains('<xs:element name="c" type="xs:integer" nillable="true"/>', $wsdl);
        $this->assertContains('<xs:element name="d" type="xs:boolean" nillable="true"/>', $wsdl);
        $this->assertContains('<xs:element name="fourargsReturn" type="xs:string" nillable="true"/>', $wsdl);
        unlink($class_file);

    }

    public function testSdoTypesGetGeneratedIntoWsdl()
    {

        $php = <<<EOF
<?php
/**
 * @service
 * @binding.ws
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
        $class_file = SCA_Helper::getTempDir().'/Class4.php';  
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
        file_put_contents('person.xsd', $xsd);

        $wsdl = SCA::generateWSDL($class_file);
        $this->assertContains('<xs:import schemaLocation="person.xsd" namespace="PersonNamespace"/>', $wsdl);
        $this->assertContains('<xs:element name="p1" type="ns0:personType" nillable="true"/>', $wsdl);
        $this->assertContains('<xs:element name="takesSDOReturn" type="ns0:personType" nillable="true"/>', $wsdl);
        unlink($class_file);
        unlink('person.xsd');


}

public static function main()
{
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("SCA_WSDLTest");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
}

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_WSDLTest::main");
    SCA_WSDLTest::main();
}

?>