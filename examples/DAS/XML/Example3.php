<?php
/**
 * Illustrate the calls that control the XML declaration
 */
   $xmldas = SDO_DAS_XML::create("letter.xsd");
   $document = $xmldas->loadFile("letter.xml");
   $document->setXMLVersion("1.1");
   $document->setEncoding("ISO-8859-1");
   print($xmldas->saveString($document));
?> 