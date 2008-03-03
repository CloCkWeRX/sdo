<?php
require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA.php';

/**
 * Test for PECL bug 11774 http://pecl.php.net/bugs/bug.php?id=11774
 * whch became
 * Tuscany JIRA 1564 http://issues.apache.org/jira/browse/TUSCANY-1566
 * 
 * When we generate XML for the small atom entry below, the author and name elements
 * should be in the atom namespace.
 * 
 * Strictly speaking this is a pure SDO test and could be moved there.
 * 
 * Expected output from the XMLDAS
 * 
 <?xml version="1.0" encoding="UTF-8"?>
<request-list xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
   <request kind="collectionInfo" 
            xsi:type="collectionInfo">
        <collection>Blah</collection>
    </request>
</request-list>
 */

class Bug11774Test extends PHPUnit_Framework_TestCase {

    public function setUp() {
                                $xsd = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">

<xsd:element name="request" type="requestType"/>

<xsd:complexType name="requestType" abstract="true"/>

<xsd:complexType name="collectionInfo">
  <xsd:complexContent>
    <xsd:extension base="requestType">
      <xsd:sequence minOccurs="0" maxOccurs="unbounded">
        <xsd:element name="collection"/>
      </xsd:sequence>
      <xsd:attribute name="kind" type="xsd:string"
fixed="collectionInfo"/>
    </xsd:extension>
  </xsd:complexContent>
</xsd:complexType>

<xsd:element name="request-list">
  <xsd:complexType>
     <xsd:sequence>
        <xsd:element ref="request" minOccurs="0"
maxOccurs="unbounded"/>
     </xsd:sequence>
  </xsd:complexType>
</xsd:element>

</xsd:schema>
EOF;
        file_put_contents(dirname(__FILE__) . '/Bug11774.xsd',$xsd);
    }
    
    public function tearDown() {
        unlink(dirname(__FILE__) . '/Bug11774.xsd');
    }
    
    public function testXsiTypesGenerated()    {

        $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . "/Bug11774.xsd");
        $doc = $xmldas->createDocument('', 'request-list');
        $rdo = $doc->getRootDataObject();
        $request = $xmldas->createDataObject('', 'collectionInfo');
        $request->collection->insert('Blah');
        $request->kind = 'collectionInfo';
        $rdo->request->insert($request);
        $xml = $xmldas->saveString($doc);


        $this->assertContains('xsi:type="collectionInfo"',$xml);
    }

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Bug11774Test");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Bug11774Test::main");
    Bug11774Test::main();
}
?>
