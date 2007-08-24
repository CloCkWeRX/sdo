<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA_AnnotationRules.php';
require_once 'SCA/SCA.php';
require_once 'SCA/Bindings/soap/ServiceDescriptionGenerator.php';
require_once 'SCA/Bindings/soap/Proxy.php';

class SCA_Bindings_soap_ProxyTest extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        if ( ! class_exists('SCA_Bindings_soap_Proxy')) {
            $this->markTestSkipped("Cannot execute any SCA soap tests as the SCA soap binding is not loaded");
            return;
        }


        $php = <<<PHP
<?php

include_once "SCA/SCA.php";

/**
 * @service
 * @binding.soap
 * @types PersonNamespace person.xsd
 */
class SoapProxyTest
{
}

?>
PHP;
        file_put_contents(dirname(__FILE__) . '/SoapProxyTest.php',$php);
        $service_description = SCA::constructServiceDescription(dirname(__FILE__) . '/SoapProxyTest.php');

        $wsdl = SCA_Bindings_soap_ServiceDescriptionGenerator::generateDocumentLiteralWrappedWsdl($service_description);
        file_put_contents(dirname(__FILE__) . '/SoapProxyTest.wsdl',$wsdl);

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
        file_put_contents(dirname(__FILE__) . '/person.xsd',$xsd);
}

public function tearDown()
{
    unlink(dirname(__FILE__) . '/SoapProxyTest.php');
    unlink(dirname(__FILE__) . '/SoapProxyTest.wsdl');
    unlink(dirname(__FILE__) . '/person.xsd');
}

public function testSoapProxyActsAsADataFactory()
{
    $service  = SCA::getService('./SoapProxyTest.wsdl');
    $person = $service->createDataObject('PersonNamespace','personType');
    $this->assertTrue($person instanceof SDO_DataObjectImpl);
    $this->assertEquals('personType',$person->getTypename());
}

public static function main()
{
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("SCA_Bindings_soap_ProxyTest");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
}

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_Bindings_soap_ProxyTest::main");
    SCA_Bindings_soap_ProxyTest::main();
}

?>
