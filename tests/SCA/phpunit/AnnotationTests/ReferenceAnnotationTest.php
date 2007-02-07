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
class SCA_Annotation_ReferenceTest extends PHPUnit_Framework_TestCase {

    public function testReferenceWithNoBindingIsInvalid()
    {
        try {
            $instance   = new ReferenceWithNoBinding();
            $reader     = new SCA_AnnotationReader($instance);
            $references = $reader->reflectReferences();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("reference",$e->getMessage());
            $this->assertContains("binding",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testBindingWithNoReferenceIsInvalid()
    {
        try {
            $instance   = new BindingWithNoReference();
            $reader     = new SCA_AnnotationReader($instance);
            $references = $reader->reflectReferences();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("reference",$e->getMessage());
            $this->assertContains("binding",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testReferenceWithAnInvalidBindingIsInvalid()
    {
        try {
            $instance   = new ReferenceWithAnInvalidBinding();
            $reader     = new SCA_AnnotationReader($instance);
            $references = $reader->reflectReferences();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("reference",$e->getMessage());
            $this->assertContains("no valid @binding",$e->getMessage());
            return;
        }
        $this->fail();
    }


    public function testReferenceWithAnEmptyPhpBindingIsInvalid()
    {
        try {
            $instance   = new ReferenceWithAnEmptyPhpBinding();
            $reader     = new SCA_AnnotationReader($instance);
            $references = $reader->reflectReferences();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("binding",$e->getMessage());
            $this->assertContains("no following value",$e->getMessage());
            return;
        }
        $this->fail();
    }


    public function testReferenceWithAnEmptyWsBindingIsInvalid()
    {
        try {
            $instance   = new ReferenceWithAnEmptyWsBinding();
            $reader     = new SCA_AnnotationReader($instance);
            $references = $reader->reflectReferences();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("binding",$e->getMessage());
            $this->assertContains("no following value",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_Annotation_ReferenceTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_Annotation_ReferenceTest::main");
    SCA_Annotation_ReferenceTest::main();
}
?>
