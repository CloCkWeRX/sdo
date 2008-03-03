<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA_AnnotationRules.php';
require_once 'SCA/SCA.php';
require_once 'SCA/Bindings/soap/ServiceDescriptionGenerator.php';
require_once 'SCA/Bindings/soap/Proxy.php';

/**
 * The essential point about this test is that the namespace for the complex 
 * type used as an argument begins with a scheme aaa:// to push it to the front
 * of the alphabet. 
 *
 */
class Bug12193Test extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        if ( ! class_exists('SCA_Bindings_soap_Proxy')) {
            $this->markTestSkipped("Cannot execute any SCA soap tests as the SCA soap binding is not loaded");
            return;
        }

        $php = <<<PHP
<?php
include 'SCA/SCA.php';

/**
 * @service
 * @binding.soap
 * @types aaa://PersonNamespace person.xsd
*/
class HelloPersonService
{
  /**
    * @param personType \$person aaa://PersonNamespace
    * @return string
    */ 
    public function hello(\$person)     
    {
        return "hello \$person->name, you were born in \$person->pob on \$person->dob" ;
    }
}
?>
PHP;
        file_put_contents(dirname(__FILE__) . '/HelloPersonService.php',$php);
        $service_description = SCA::constructServiceDescription(dirname(__FILE__) . '/HelloPersonService.php');

        $wsdl = SCA_Bindings_soap_ServiceDescriptionGenerator::generateDocumentLiteralWrappedWsdl($service_description);
        file_put_contents(dirname(__FILE__) . '/HelloPersonService.wsdl',$wsdl);

        $xsd = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<schema xmlns="http://www.w3.org/2001/XMLSchema"
        targetNamespace="aaa://PersonNamespace"
        xmlns:PersonNamespace="aaa://PersonNamespace">
  <complexType name="personType">
    <sequence>
      <element name="name" type="string"/>
      <element name="dob" type="string"/>
      <element name="pob" type="string"/>
    </sequence>
  </complexType>
</schema>
EOF;
        file_put_contents(dirname(__FILE__) . '/person.xsd',$xsd);


}

public function tearDown()
{
    unlink(dirname(__FILE__) . '/HelloPersonService.php');
    unlink(dirname(__FILE__) . '/HelloPersonService.wsdl');
    unlink(dirname(__FILE__) . '/person.xsd');
}

public function testCanCreateOperationSdoRegardlessOfAlphabeticOrder()
{
    $service  = SCA::getService('./HelloPersonService.wsdl');
    $person = $service->createDataObject('aaa://PersonNamespace','personType');
    $person->name = 'William Shakespeare';
    $arguments = array($person);
    $method_name = 'hello';
    //  before the fix to 12193, the following call threw 
    // SDO_UnsupportedOperationException: createDocument - cannot find element hello
    $operation_sdo = $service->getSoapOperationSdo($method_name, $arguments);
    $this->assertEquals('http://HelloPersonService',$operation_sdo->getTypeNamespaceURI());

}

public static function main()
{
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("Bug12193Test");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
}

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Bug12193Test::main");
    Bug12193Test::main();
}

?>
