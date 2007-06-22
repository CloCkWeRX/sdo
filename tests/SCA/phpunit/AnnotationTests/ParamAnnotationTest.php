<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA.php';
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
class SCA_Annotation_ParamTest extends PHPUnit_Framework_TestCase {

    public function testEmptyParamIsInvalid()
    {
//        $this->markTestSkipped(
//        'We should give a helpful message, not just "Invalid syntax ...'
//        );
        try {
            $instance            = new ParamWithNoTypeOrName();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("param",$e->getMessage());
            $this->assertContains("must be followed by a type",$e->getMessage());
            return;
        }
        $this->fail();

    }

    public function testParamWithValidTypeButNoNameIsInvalid()
    {
        try {
            $instance            = new ParamWithValidTypeButNoName();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("param",$e->getMessage());
            $this->assertContains("must be followed by a type then a variable name",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testParamWithOnlyInvalidTypeIsInvalid()
    {
        try {
            $instance            = new ParamWithOnlyInvalidType();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("param",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testParamWithInvalidTypeAndValidNameIsInvalid()
    {
        try {
            $instance            = new ParamWithInvalidTypeAndValidName();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("param",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testParamWithValidTypeAndInvalidNameIsInvalid()
    {
        try {
            $instance            = new ParamWithValidTypeAndInvalidName();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("param",$e->getMessage());
            $this->assertContains("begin with a $",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testParamWithValidTypeAndNameIsValid()
    {
        $instance            = new ParamWithValidTypeAndName();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        $this->assertTrue(key_exists('myPublicMethod',$service_description->operations));
        $op_array = $service_description->operations;
        $method = $op_array['myPublicMethod'];

        $this->assertEquals(
        array(
        'parameters' => array(0=>array('annotationType'=>'@param','nillable'=>false,'type'=>'string','name' => 'a')),
        'return' => null),
        $method);
    }

    public function testParamWithChoiceOfTwoValidTypesAndNameIsInvalid()
    {
        // we do not allow e.g. string|float because we do not know which to allow in the wsdl
        // TODO we could permit this, generating a choice in the wsdl
        try {
        $instance            = new ParamWithChoiceOfTwoValidTypesAndName();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("param",$e->getMessage());
            $this->assertContains("may only have null",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testParamWithChoiceOfValidTypeOrNullAndNameIsValid()
    {
        $instance            = new ParamWithChoiceOfValidTypeOrNullAndName();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        $this->assertTrue(key_exists('myPublicMethod',$service_description->operations));
        $op_array = $service_description->operations;
        $method = $op_array['myPublicMethod'];

        $this->assertEquals(
        array(
        'parameters' => array(0=>array('annotationType'=>'@param','type'=>'string',
        'name' => 'a', 'nillable'=>true)),
        'return' => null),
        $method);
    }

    public function testParamWithFourValidScalarTypesIsValid()
    {
        $instance            = new ParamWithFourValidScalarTypes();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        $this->assertTrue(key_exists('myPublicMethod',$service_description->operations));
        $op_array = $service_description->operations;
        $method = $op_array['myPublicMethod'];

        $this->assertEquals(
        array(
        'parameters' => array(
        0=>array('annotationType'=>'@param','nillable'=>false,'type'=>'string','name' => 'a'),
        1=>array('annotationType'=>'@param','nillable'=>false,'type'=>'real','name' => 'b'),
        2=>array('annotationType'=>'@param','nillable'=>false,'type'=>'boolean','name' => 'c'),
        3=>array('annotationType'=>'@param','nillable'=>false,'type'=>'integer','name' => 'd')
        ),
        'return' => null),
        $method);
    }
    
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_Annotation_ParamTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_Annotation_ParamTest::main");
    SCA_Annotation_ParamTest::main();
}
?>
