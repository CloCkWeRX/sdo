<?php
require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA.php';

class SCA_TargetTest extends PHPUnit_Framework_TestCase {


    public function testRelativeTargetCorrectlyResolvedForClient()
    {
        $service 		= SCA::getService('./hello.php','local');
        $this->assertContains("hello",$service->hello());
    }

    public function testRelativeTargetCorrectlyResolvedForComponentOneDeep()
    {
        $service 		= SCA::getService('./hello2.php','local');
        $this->assertContains("hello",$service->hello());
    }

    public function testRelativeTargetCorrectlyResolvedForComponentTwoDeep()
    {
        $service 		= SCA::getService('./hello3.php','local');
        $this->assertContains("hello",$service->hello());
    }

    public function testRelativeTargetCorrectlyResolvedForComponentThreeDeepAndOneDown()
    {
        $service 		= SCA::getService('./downone/hello4.php','local');
        $this->assertContains("hello",$service->hello());
    }

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_TargetTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_TargetTest::main");
    SCA_TargetTest::main();
}
?>