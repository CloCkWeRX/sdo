<?php

/**
 * Illustrate open types and the use of the addTypes() method
 */

$xmldas = SDO_DAS_XML::create();
$xmldas->addTypes("jungle.xsd"); // this is an open type i.e. the xsd specifies it can contain "any" type
$xmldas->addTypes('animalTypes.xsd');

$baloo 			= $xmldas->createDataObject('','bearType');
$baloo->name 	= "Baloo";
$baloo->weight 	= 800;

$bagheera 		= $xmldas->createDataObject('','pantherType');
$bagheera->name = "Bagheera";
$bagheera->colour = 'inky black';

$kaa 			= $xmldas->createDataObject('','snakeType');
$kaa->name 		= "Kaa";
$kaa->length 	= 25;

$document 		= $xmldas->createDocument();
$do 			= $document->getRootDataObject();
$do->bear 		= $baloo;
$do->panther 	= $bagheera;
$do->snake 		= $kaa;

print($xmldas->saveString($document,2));


?>			
