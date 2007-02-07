<?php
/**
 * Load, update, and save an XML document
 */
try {
   $xmldas = SDO_DAS_XML::create("letter.xsd");
   $document = $xmldas->loadFile("letter.xml");
   $root_data_object = $document->getRootDataObject();
   $root_data_object->date = "September 03, 2004";
   $root_data_object->firstName = "Anantoju";
   $root_data_object->lastName = "Madhu";
   $xmldas->saveFile($document, "letter-out.xml");
   echo "New file has been written:\n";
   print file_get_contents("letter-out.xml");
} catch (SDO_Exception $e) {
   print($e->getMessage());
}
?> 