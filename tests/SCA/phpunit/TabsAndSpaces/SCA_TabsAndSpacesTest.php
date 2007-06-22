<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA_AnnotationRules.php';
require_once 'SCA/SCA_AnnotationReader.php';

require_once 'TabsAndSpaces.php';

class SCA_TabsAndSpacesTest extends PHPUnit_Framework_TestCase {

    public function testTabsAndSpacesAreOk()
    {
        $instance            = new TabsAndSpaces();
        $reader              = new SCA_AnnotationReader($instance);
        $service_description = $reader->reflectService();
        $this->assertTrue(key_exists('spaces',$service_description->operations));
        $this->assertTrue(key_exists('tabs',$service_description->operations));
        
        $spaces_op = $service_description->operations['spaces'];
        $parm = $spaces_op['parameters'][0];
        $this->assertEquals(
        array(
        'annotationType' => '@param',
        'nillable' => false,
        'description' => 'the ticker symbol',
        'name' => 'ticker',
        'type' => 'string'
        ),
        $parm);
        
        $tabs_op = $service_description->operations['tabs'];
        $parm = $tabs_op['parameters'][0];
        $this->assertEquals(
        array(
        'annotationType' => '@param',
        'nillable' => false,
        'description' => 'the ticker symbol',
        'name' => 'ticker',
        'type' => 'string'
        ),
        $parm);

        $references          = $reader->reflectReferences();
        $this->assertEquals(
        array(
        'spaces' => 'spaces.php',
        'tabs' => 'tabs.wsdl'),$references);
    }
    
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_TabsAndSpacesTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_TabsAndSpacesTest::main");
    SCA_TabsAndSpacesTest::main();
}

?>
