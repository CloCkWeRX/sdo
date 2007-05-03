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
class SCA_Annotation_TypesTest extends PHPUnit_Framework_TestCase {


    public function testEmptyTypesIsInvalid()
    {
        try {
            $instance            = new EmptyTypes();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("types",$e->getMessage());
            $this->assertContains("namespace",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testTypesWithOnlyNamespaceIsInvalid()
    {
        try {
            $instance            = new TypesWithOnlyNamespace();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("types",$e->getMessage());
            $this->assertContains("schema location",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testTypesWithValidNamespaceAndXsdIsValid()
    {
        $instance            = new TypesWithValidNamespaceAndXsd();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        $this->assertTrue(isset($service_description->xsd_types));
        $types = $service_description->xsd_types;
        $this->assertEquals(1,count($types));

        $this->assertEquals(
        array('http://Namespace','Anything.xsd'),
        $types[0]
        );
    }

    public function testTwoTypesWithSameNamespaceAndDifferentXsdsIsValid()
    {
        $instance            = new TwoTypesWithSameNamespaceAndDifferentXsds();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        $this->assertTrue(key_exists('xsd_types',$service_description));
        $types = $service_description->xsd_types;
        $this->assertEquals(2,count($types));

        $this->assertEquals(
        array('http://Namespace','Anything.xsd'),
        $types[0]
        );

        $this->assertEquals(
        array('http://Namespace','More.xsd'),
        $types[1]
        );
    }

    public function testParamWithInvalidNamespaceIsInvalid()
    {
        try {
            $instance            = new ParamWithInvalidNamespace();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("param",$e->getMessage());
            $this->assertContains("Namespace",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testReturnWithInvalidNamespaceIsInvalid()
    {
        try {
            $instance            = new ReturnWithInvalidNamespace();
            $reader              = new SCA_AnnotationReader($instance);
            $service_description = $reader->reflectService();
        }
        catch (SCA_RuntimeException $e) {
            $this->assertContains("return",$e->getMessage());
            $this->assertContains("Namespace",$e->getMessage());
            return;
        }
        $this->fail();
    }

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_Annotation_TypesTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_Annotation_TypesTest::main");
    SCA_Annotation_TypesTest::main();
}
?>
