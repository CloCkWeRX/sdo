--TEST--
SDO_DAS_XML test for element name containing hyphen
--INI--
display_errors=off
--SKIPIF--
<?php

  if (!extension_loaded('sdo'))
      print 'skip - sdo extension not loaded';
?>
--FILE--
<?php

$schema = <<<END_SCHEMA
<schema xmlns="http://www.w3.org/2001/XMLSchema">
<element name="topType">
<complexType>
<sequence>
<element name="with-hyphen" type="String"/>
<element name="with_underscore" type="String"/>
</sequence>
</complexType>
</element>
</schema>
END_SCHEMA;

$dirname = dirname($_SERVER['SCRIPT_FILENAME']);
$xsd_file = "${dirname}/TEMP.xsd";
file_put_contents($xsd_file, $schema);
$xmldas = SDO_DAS_XML::create($xsd_file);
unlink($xsd_file);

$xdoc = $xmldas->createDocument("topType");
$root = $xdoc->getRootDataObject();
$root->{'with-hyphen'} = "valueA";
$root->with_underscore = "valueB";

print $xdoc;
?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<topType xsi:type="topType" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><with-hyphen>valueA</with-hyphen><with_underscore>valueB</with_underscore></topType>