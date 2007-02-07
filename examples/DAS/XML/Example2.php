<?php
/**
 * Create an XML document from scratch
 */
try {
   $xmldas = SDO_DAS_XML::create("letter.xsd");
   try {
       $doc = $xmldas->createDocument();
       $rdo = $doc->getRootDataObject();
       $seq = $rdo->getSequence();
       $seq->insert("April 09, 2005", NULL, 'date');
       $seq->insert("Acme Inc. ", NULL, NULL);
       $seq->insert("United Kingdom. ");
       $seq->insert("Dear", NULL, NULL);
       $seq->insert("Tarun", NULL, "firstName");
       $seq->insert("Nayaraaa", NULL, "lastName");
       $rdo->lastName = "Nayar";
       $seq->insert("Please note that your order number ");
       $seq->insert(12345);
       $seq->insert(" has been dispatched today. Thanks for your business with us.");
       print($xmldas->saveString($doc));
   } catch (SDO_Exception $e) {
       print($e);
   }
} catch (SDO_Exception $e) {
   print("Problem creating an XML document: " . $e->getMessage());
}
?> 