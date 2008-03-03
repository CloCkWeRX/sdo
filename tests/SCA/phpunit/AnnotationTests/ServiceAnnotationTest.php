<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA_AnnotationRules.php';
require_once 'SCA/SCA_AnnotationReader.php';

require_once 'AnnotationTestClasses.php';

/**
 * Test the annotations
 * We do this by driving either AnnotationReader::reflectService
 *   this will test the annotations that are needed for wsdl: binding, types, param, return
 * or AnnotationReader::reflectReferences
 *   this will test the references and their binding
 */
class SCA_Annotation_ServiceTest extends PHPUnit_Framework_TestCase {

    public function testAServiceMustHaveAServiceAnnotation()
    {
        try {
            $instance            = new NoServiceAnnotation();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains('NoServiceAnnotation',$e->getMessage());
            $this->assertContains('does not contain an @service annotation',$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testServiceInterfaceFromFirstInterface()
    {
        try {
            $instance            = new ServiceInterfaceWithTwoMethods();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
            $this->assertEquals(count($service_description->operations), 2, 
                                "Wrong number of operations on service description.");
        }
        catch (SCA_RuntimeException $e) {
            $this->fail();
        }
    }

    public function testServiceInterfaceFromSecondInterface()
    {
        try {
            $instance            = new ServiceInterfaceWithOneMethod();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
            $this->assertEquals(count($service_description->operations), 1, 
                                "Wrong number of operations on service description.");
        }
        catch (SCA_RuntimeException $e) {
            $this->fail();
        }
    }

    public function testServiceInterfaceFromNoInterfaces()
    {
        try {
            $instance            = new ServiceInterfaceWithFourMethods();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
            $this->assertEquals(count($service_description->operations), 4, 
                                "Wrong number of operations on service description.");
        }
        catch (SCA_RuntimeException $e) {
            $this->fail();
        }
    }

    public function testServiceWithInvalidInterfaces()
    {
        try {
            $instance            = new ServiceWithInvalidInterface();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains('Service interface',$e->getMessage());
            $this->assertContains('specified by @service does not match any interface implemented by',$e->getMessage());
            return;
        }
        $this->fail();
    }

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_Annotation_ServiceTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_Annotation_ServiceTest::main");
    SCA_Annotation_ServiceTest::main();
}
?>
