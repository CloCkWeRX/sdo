<?php
require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA.php';

class SCA_GetServicePathsTest extends PHPUnit_Framework_TestCase {


    public function testExceptionThrownForNullArgument()
    {
        try {
            $service 		= SCA::getService(null);
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("null argument",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testExceptionThrownForEmptyArgument()
    {
        try {
            $service 		= SCA::getService('');
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("empty argument",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testExceptionThrownForFileNotFound()
    {
        try {
            $service = SCA::getService('a_total_load_of_rubbish', 'local');
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("a_total_load_of_rubbish",$e->getMessage());
            $this->assertContains("could not be found",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testExceptionThrownForFileNotFoundByDotRelativePath()
    {
        try {
            $service = SCA::getService('./a_total_load_of_rubbish', 'local');
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("a_total_load_of_rubbish",$e->getMessage());
            $this->assertContains("could not be found",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testExceptionThrownForFileNotFoundByDotDotRelativePath()
    {
        try {
            $service = SCA::getService('../a_total_load_of_rubbish', 'local');
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("a_total_load_of_rubbish",$e->getMessage());
            $this->assertContains("could not be found",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testExceptionThrownForFileNotFoundByAbsolutePath()
    {
        try {
            $service = SCA::getService('C:\a_total_load_of_rubbish', 'local');
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("a_total_load_of_rubbish",$e->getMessage());
            $this->assertContains("could not be found",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testExceptionThrownForInvalidExtension()
    {
        file_put_contents(dirname(__FILE__) . "/temp.txt","hello");
        try {
            $service 		= SCA::getService('./temp.txt');
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("The right binding to use could not be inferred",$e->getMessage());
            unlink(dirname(__FILE__) . "/temp.txt");
            return;
        }
        $this->fail();
    }

    public function testExplicitUseOfHttpWrapperForWsdlIsUnderstood()
    {
        $this->markTestIncomplete('this test has started taking a full minute to time out. Needs rethinking');
        try {
            $service 		= @SCA::getService('http://localhost/yet_more_rubbish.wsdl');
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("yet_more_rubbish",$e->getMessage());
            $this->assertContains("could not be found",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_GetServicePathsTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_GetServicePathsTest::main");
    SCA_GetServicePathsTest::main();
}
?>