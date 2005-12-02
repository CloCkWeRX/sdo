<?php
try {
   $xmldas = SDO_DAS_XML::create("letter.xsd");
   try {
       $do = $xmldas->createDataObject("http://letterSchema", "FormLetter");
       $seq = $do->getSequence();
       $seq->insert("April 09, 2005", NULL, 'date');
       $seq->insert("Acme Inc. ", NULL, NULL);
       $seq->insert("United Kingdom. ");
       $seq->insert("Dear", NULL, NULL);
       $seq->insert("Tarun", NULL, "firstName");
       $seq->insert("Nayaraaa", NULL, "lastName");
       $do->lastName = "Nayar";
       $seq->insert("Please note that your order number ");
       $seq->insert(12345);
       $seq->insert(" has been dispatched today. Thanks for your business with us.");
       $model_reflection_object = new SDO_Model_ReflectionDataObject($do);
	   $type = $model_reflection_object->getType();
       // this has previously been shown with the Type as the third argument .
       // This generates XML with a root element name of FormLetter.
       // This works and will reload but is probably not what is wanted.
       // This should instead be the root element name 'letter'
       print($xmldas->saveDataObjectToString($do, $type->getNamespaceURI(), 'letter'));
   } catch (SDO_Exception $e) {
       print($e);
   }
} catch (SDO_TypeNotFoundException $e) {
   print("Type is not defined in the xsd file");
} catch (SDO_DAS_XML_ParserException $e) {
   print("Problem while parsing");
}
?> 