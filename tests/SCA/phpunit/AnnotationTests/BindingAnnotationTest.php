<?php
require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA_AnnotationRules.php';
require_once 'SCA/SCA_AnnotationReader.php';

require_once 'AnnotationTestClasses.php';
// careful - have to include SCA because the location of SCA.php
// is used to find the Bindings directory, and that is used in 
// checking valid bindings
require_once 'SCA/SCA.php';

/**
 * Test the annotations
 * We do this by driving either AnnotationReader::reflectService
 *   this will test the annotations that are needed for wsdl: binding, types, param, return
 * or AnnotationReader::reflectReferences
 *   this will test the references and their binding
 */
class SCA_Annotation_BindingTest extends PHPUnit_Framework_TestCase {


    public function testAWsServiceMustHaveABindingAnnotation()
    {
        try {
            $instance            = new NoBindingAnnotation();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("No valid @binding",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testRubbishBindingAnnotationIsSpotted()
    {
        try {
            $instance            = new RubbishBindingAnnotation();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("No valid @binding",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testOneValidBindingAnnotationIsOk()
    {
        $instance            = new OneValidBindingAnnotation();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        $this->assertContains("soap",$service_description->binding);
    }

    public function testTwoValidBindingAnnotationsIsOk()
    {
        $instance            = new TwoValidBindingAnnotations();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        $this->assertContains("soap",$service_description->binding);
        $this->assertContains("jsonrpc",$service_description->binding);
    }

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_Annotation_BindingTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_Annotation_BindingTest::main");
    SCA_Annotation_BindingTest::main();
}
?>
