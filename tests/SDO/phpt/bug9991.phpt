--TEST--
SDO (another) test for copy object with property set to null
--INI--
display_errors=off
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php

$xsd = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<schema xmlns="http://www.w3.org/2001/XMLSchema"
        targetNamespace="PersonNamespace"
        xmlns:AuthorNS="PersonNamespace">
  <complexType name="personType">
    <sequence>
      <element name="name" type="string"/>
      <element name="dob" type="string"/>
      <element name="pob" type="string"/>
    </sequence>
  </complexType>
</schema>
EOF;

$schema_file = tempnam(NULL, 'xsd');
file_put_contents($schema_file, $xsd);

$xmldas = SDO_DAS_XML::create($schema_file);
unlink($schema_file);

$person = $xmldas->createDataObject('PersonNamespace','personType');

$person->name = "William Shakespeare'";
$person->dob = null;
$person->pob = null;

$person2 = clone($person);

echo ($person->dob === $person2->dob);
?>
--EXPECT--
1