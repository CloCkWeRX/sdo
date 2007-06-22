<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA_AnnotationRules.php';
require_once 'SCA/SCA_AnnotationReader.php';

require_once 'SCA/SCA.php';
require_once 'AnnotationTestClasses.php';

/**
 * Test the annotations
 * We do this by driving either AnnotationReader::reflectService
 *   this will test the annotations that are needed for wsdl: binding, types, param, return
 * or AnnotationReader::reflectReferences
 *   this will test the references and their binding
 */
class SCA_Annotation_ReturnTest extends PHPUnit_Framework_TestCase {

    public function testEmptyReturnIsInvalid()
    {
        try {
            $instance            = new EmptyReturn();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("return must be followed by a type",$e->getMessage());
            return;
        }
        $this->fail();

    }

    public function testReturnWithInvalidTypeIsInvalid()
    {
        try {
            $instance            = new ReturnWithInvalidType();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("return",$e->getMessage());
            $this->assertContains("rubbish",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testReturnWithValidTypeIsValid()
    {
        $instance            = new ReturnWithValidType();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        $this->assertTrue(key_exists('myPublicMethod',$service_description->operations));
        $op_array = $service_description->operations;
        $method = $op_array['myPublicMethod'];
        $return_array = $method['return'];

        $this->assertEquals(
        array(
        0 => array('annotationType'=>'@return','nillable'=>false,'type'=>'string')
        ),
        $return_array);
    }

    public function testReturnWithChoiceOfTwoValidTypesAndNameIsInvalid()
    {
        // we do not allow e.g. string|float because we do not know which to allow in the wsdl
        // TODO we could permit this, generating a choice in the wsdl
        try {
        $instance            = new ReturnWithChoiceOfTwoValidTypes();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("return",$e->getMessage());
            $this->assertContains("may only have null",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testReturnWithChoiceOfValidTypeOrNullIsValid()
    {
        $instance            = new ReturnWithChoiceOfValidTypeOrNull();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        $this->assertTrue(key_exists('myPublicMethod',$service_description->operations));
        $op_array = $service_description->operations;
        $method = $op_array['myPublicMethod'];
        $return_array = $method['return'];

        $this->assertEquals(
        array(
        0 => array('annotationType'=>'@return','type'=>'string','nillable'=>true)
        ),
        $return_array);
    }


    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_Annotation_ReturnTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_Annotation_ReturnTest::main");
    SCA_Annotation_ReturnTest::main();
}
?>
