--TEST--
SDO test for reading nillable elements
--INI--
display_errors=off
--SKIPIF--
<?php 
if (!extension_loaded("sdo")) 
    echo "skip sdo not loaded"; 
echo "skip bug8694 is bogus";
?>
--FILE--
<?php

$xsd = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<schema xmlns="http://www.w3.org/2001/XMLSchema"
 xmlns:tns="http://www.apache.org/tuscany/interop"
 targetNamespace="http://www.apache.org/tuscany/interop">
 <element name="RootElement39">
    <complexType>
      <sequence>
        <element name="ElementWithNillable" type="string"
         maxoccurs="unbounded" nillable="true"/>  
      </sequence>
    </complexType>
  </element>
</schema>
EOF;

$xml = <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<tns:RootElement39 xmlns:tns="http://www.apache.org/tuscany/interop"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.apache.org/tuscany/interop/interop39.xsd">
	<ElementWithNillable/>
        <ElementWithNillable xsi:nil="true"/> 
</tns:RootElement39>
EOF;

$schema_file = tempnam(NULL, 'xsd');
file_put_contents($schema_file, $xsd);

$xmldas = SDO_DAS_XML::create($schema_file);
unlink($schema_file);

$root = $xmldas->loadString($xml)->getRootDataObject();
?>
--EXPECT--
1
1