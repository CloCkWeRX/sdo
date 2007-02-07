<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA_AnnotationRules.php';
require_once 'SCA/SCA_AnnotationReader.php';

require_once 'LocalProxyTestStringReverser.php';
require_once 'LocalProxyTestSDOReverser.php';

class SCA_LocalProxyTest extends PHPUnit_Framework_TestCase
{

    public function testLocalProxyPassesScalarByValue()
    {
        $instance = new LocalProxyTestStringReverser();
        $service  = SCA::getService('LocalProxyTestStringReverser.php');

        // if you call the instance directly it does pass-by-reference
        $string  = 'hello';
        $instance->reverseStringArgument($string);
        $this->assertEquals('olleh',$string);

        // if you call the instance as a service it does pass-by-value
        $string = 'hello';
        $service->reverseStringArgument($string);
        $this->assertEquals('hello',$string);
    }

    public function testLocalProxyPassesSdoByValue()
    {

        $das = SDO_DAS_XML::create(dirname(__FILE__) . '/person.xsd');
        $person = $das->createDataObject('PersonNamespace','personType');

        $person->name = 'William Shakespeare';
        $person->dob = 'April 1564, most likely 23rd';
        $person->pob = 'Stratford-upon-Avon, Warwickshire';

        // if you call the instance directly it does pass-by-reference
        $instance = new LocalProxyTestSDOReverser();
        $instance->reverseSDOArgument($person);
        $this->assertEquals(strrev('William Shakespeare'),$person->name);

        // if you call the instance as a service it does pass-by-value
        $person = $das->createDataObject('PersonNamespace','personType');
        $person->name = 'William Shakespeare';
        $person->dob = 'April 1564, most likely 23rd';
        $person->pob = 'Stratford-upon-Avon, Warwickshire';

        $service  = SCA::getService('./LocalProxyTestSDOReverser.php');
        $service->reverseSDOArgument($person);
        $this->assertEquals('William Shakespeare',$person->name);
    }

    public function testLocalProxyActsAsADataFactory()
    {
        $service  = SCA::getService('./LocalProxyTestSDOReverser.php');
        $person = $service->createDataObject('PersonNamespace','personType');
        $person->name = 'William Shakespeare';
        $person->dob = 'April 1564, most likely 23rd';
        $person->pob = 'Stratford-upon-Avon, Warwickshire';

        $this->assertTrue($person instanceof SDO_DataObjectImpl);
        $this->assertEquals('personType',$person->getTypename());
    }


    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_LocalProxyTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_LocalProxyTest::main");
    SCA_LocalProxyTest::main();
}

?>
