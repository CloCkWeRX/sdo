<?php
try {
   $xmldas = SDO_DAS_XML::create("letter.xsd");
   $xdoc = $xmldas->loadFromFile("letter.xml");
   $do = $xdoc->getRootDataObject();
   $do->date = "September 03, 2004";
   $do->firstName = "Anantoju";
   $do->lastName = "Madhu";
   $xmldas->saveDocumentToFile($xdoc, "letter-out.xml");
   echo "New file has been written:\n";
   print file_get_contents("letter-out.xml");
} catch (SDO_Exception $e) {
   print($e->getMessage());
}
?> 