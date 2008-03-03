<?php
require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA.php';

/**
 * Test for Tuscany JIRA 1566 http://issues.apache.org/jira/browse/TUSCANY-1566
 * 
 * When we generate XML for the small atom entry below, the author and name elements
 * should be in the atom namespace.
 * 
 * Although this is largely an SDO test, it depends on the Atom xsd that we 
 * ship with SCA. 
 * 
 * The generated XML should look much like this:
 * 
 <?xml version="1.0" encoding="UTF-8"?>
<tns:entry xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
           xmlns:tns="http://www.w3.org/2005/Atom">
  <tns:author>
    <tns:name>Caroline Maynard</tns:name>
  </tns:author>
</tns:entry>
 */

class Tuscany1566Test extends PHPUnit_Framework_TestCase {

    public function testElementsComeOutInTheRightNamespace()    {
        $location_of_sca_class = SCA_Helper::getFileContainingClass("SCA");
        $sca_dir               = dirname($location_of_sca_class);
        $location_of_atom_xsd  = $sca_dir . "/Bindings/atom/Atom1.0.xsd";

        $xmldas         = SDO_DAS_XML::create($location_of_atom_xsd);
        $document       = $xmldas->createDocument('http://www.w3.org/2005/Atom','entry');
        $entry          = $document->getRootDataObject();
        $author         = $entry->createDataObject('author');
        $author->name[] = "Caroline Maynard";
        $xml            = $xmldas->saveString($document,2);
        $this->assertContains("<tns:author>",$xml);
        $this->assertContains("<tns:name>",$xml);
    }

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Tuscany1566Test");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Tuscany1566Test::main");
    Tuscany1566Test::main();
}
?>
