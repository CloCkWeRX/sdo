<?php
try {
   $xmldas = SDO_DAS_XML::create("letter.xsd");
   $xdoc = $xmldas->loadFromFile("letter.xml");
   print("Encoding is set to : " . $xdoc->getEncoding() . "\n");
   print("XML Version : " . $xdoc->getXMLVersion() . "\n");
   $xdoc->setXMLVersion("1.1");
   print($xmldas->saveDocumentToString($xdoc));
} catch (SDO_TypeNotFoundException $e) {
   print("Type is not defined in the xsd file");
} catch (SDO_DAS_XML_ParserException $e) {
   print("Problem while parsing");
}
?> 