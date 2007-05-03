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
class SCA_Annotation_MethodTest extends PHPUnit_Framework_TestCase {

    public function testAWsWithNoMethodsIsUnusualButOk()
    {
        $instance            = new NoMethods();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        $this->assertEquals(array(),$service_description->operations);
    }

    public function testAWsWithNoPublicMethodsIsUnusualButOk()
    {
        $instance            = new NoPublicMethods();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        $this->assertEquals(array(),$service_description->operations);
    }

    public function testAPublicMethodWithNoAnnotationsHasEmptyParametersAndReturn()
    {
        $instance            = new MethodHasNoAnnotations();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();

        $this->assertTrue(key_exists('myPublicMethod',$service_description->operations));
        $op_array = $service_description->operations;
        $method = $op_array['myPublicMethod'];

        $this->assertEquals(array('parameters' => array(), 'return' => null),$method);
    }

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_Annotation_MethodTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_Annotation_MethodTest::main");
    SCA_Annotation_MethodTest::main();
}

?>
