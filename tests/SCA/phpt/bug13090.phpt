--TEST--
SDO test for alphabetical type order bug
--INI--
display_errors=off
--SKIPIF--
<?php 
if (!extension_loaded("sdo")) 
    echo "skip sdo not loaded"; 
else if (phpversion('sdo') <= '1.2.4')
    echo "skip test requires version > 1.2.4";
?>
--FILE--
<?php

$xsd = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema targetNamespace="http://example.com/model"
    elementFormDefault="qualified"
    attributeFormDefault="unqualified"
    version="1.3"
    xmlns="http://example.com/model"
    xmlns:xs="http://www.w3.org/2001/XMLSchema">

  <xs:simpleType name="lovType">
    <xs:restriction base="xs:int">
      <xs:totalDigits value="3"/>
    </xs:restriction>
  </xs:simpleType>

  <xs:simpleType name="lovCreationTypeType">
    <xs:restriction base="lovType"/>
  </xs:simpleType>

  <xs:simpleType name="lovZZCreationTypeType">
    <xs:restriction base="lovType"/>
  </xs:simpleType>

  <xs:element name="creationType" type="lovCreationTypeType">
  </xs:element>

  <xs:element name="creationTypeZZ" type="lovZZCreationTypeType">
  </xs:element>

  <xs:complexType name="batchRequestType">
    <xs:sequence>
      <xs:element ref="creationType"/>
      <xs:element ref="creationTypeZZ"/>
    </xs:sequence>
  </xs:complexType>

  <xs:element name="upl" type="batchRequestType">
  </xs:element>
</xs:schema>
EOF;

$schema_file = tempnam(NULL, 'xsd');
file_put_contents($schema_file, $xsd);

$xmldas = SDO_DAS_XML::create($schema_file);
unlink($schema_file);

$document = $xmldas->createDocument('http://example.com/model', 'upl');
$upl = $document->getRootDataObject();

$upl->creationTypeZZ = 1234;
$upl->creationType = 1234;

echo ($upl->creationTypeZZ == 1234) ."\n";
echo ($upl->creationType == 1234);
unset($upl);
?>
--EXPECT--
1
1