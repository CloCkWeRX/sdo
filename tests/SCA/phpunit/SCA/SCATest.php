<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA.php';
class SCA_Test extends PHPUnit_Framework_TestCase
{


    public function testScaActsAsADataFactory()
    {
        $service  = SCA::getService('./SCATestService.php','local');
        $person = $service->reply();
        $this->assertTrue($person instanceof SDO_DataObjectImpl);
        $this->assertEquals('personType',$person->getTypename());
    }


    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_Test");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_LocalProxyTest::main");
    SCA_Test::main();
}

?>
