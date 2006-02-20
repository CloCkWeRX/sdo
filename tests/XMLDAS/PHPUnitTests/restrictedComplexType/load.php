<?php

   $xmldas = SDO_DAS_XML::create("name.xsd");
   print $xmldas;
   $xdoc = $xmldas->loadFile("name.xml");
   $do = $xdoc->getRootDataObject();

   var_dump($do);
	
   $str = $xmldas->saveString($xdoc,2);
	print $str;

   
	

$rdo = new SDO_Model_ReflectionDataObject($do);

$do_type = $rdo->getType();

$all_props = $do_type->getProperties();

foreach ($all_props as $do_property) {
	echo "property " . $do_property->getName() . "\n";
}

var_dump($do);
   
?> 