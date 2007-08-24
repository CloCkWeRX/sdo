--TEST--
SDO test for namespaces
--INI--
display_errors=off
--SKIPIF--
<?php 
if (!extension_loaded("sdo")) 
    echo "skip sdo not loaded"; 
?>
--FILE--
<?php

$person_xsd = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<schema xmlns="http://www.w3.org/2001/XMLSchema" 
    targetNamespace="http://www.test.com/info" 
    xmlns:info="http://www.test.com/info">
    <complexType name="nameType">
		<sequence>
			<element name="first" type="string"></element>
			<element name="last" type="string"></element>
		</sequence>
	</complexType>
	<complexType name="personType">
		<sequence>
			<element name="name" type="info:nameType"></element>
		</sequence>
	</complexType>	
</schema>
EOF;

$types_xsd = <<< EOF
    <xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" 
      xmlns:ns0="http://www.test.com/info"
      targetNamespace="http://Component"
      elementNameDefault="qualified">
      <xs:import schemaLocation="person.xsd" namespace="http://www.test.com/info"/>
      <xs:element name="add">
        <xs:complexType>
          <xs:sequence>
            <xs:element name="person" type="ns0:personType" nillable="true"/>
          </xs:sequence>
        </xs:complexType>
      </xs:element>
    </xs:schema>
EOF;

file_put_contents('person.xsd', $person_xsd);
file_put_contents('types.xsd', $types_xsd);

$xmldas = SDO_DAS_XML::create('types.xsd');

unlink('person.xsd');
unlink('types.xsd');

$xdoc   = $xmldas->createDocument('', 'add');
$add = $xdoc->getRootDataObject();

$person = $xmldas->createDataObject('http://www.test.com/info','personType');
$name = $person->createDataObject('name');
$name->first = "Will";
$name->last  = "Shakespeare";

$add->person = $person;

$xmlstr = $xmldas->saveString($xdoc, 2);
echo $xmlstr;


?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<tns:add xmlns:tns="http://Component" xmlns:tns2="http://www.test.com/info" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <person>
    <name>
      <first>Will</first>
      <last>Shakespeare</last>
    </name>
  </person>
</tns:add>