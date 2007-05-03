--TEST--
SDO test unstructured text in sequence is preserved when a DataObject is cloned
--INI--
display_errors=off
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php

$xsd = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<schema xmlns="http://www.w3.org/2001/XMLSchema"
xmlns:tns="http://www.example.org/test"
targetNamespace="http://www.example.org/test">

<complexType name="CloneType" mixed="true">
    <sequence>
        <element name="test"  type="string"/>
        <any namespace="##any"/>
    </sequence>
</complexType>

<element name="Clone" type="tns:CloneType"/>

</schema>
EOF;

$xml = <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<Clone xmlns="http://www.example.org/test"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://www.example.org/test clone.xsd ">
  abc
  <test>test</test>
  def
  <tests>test</tests>
  ghi
</Clone>
EOF;

$schema_file = tempnam(NULL, 'xsd');
file_put_contents($schema_file, $xsd);

$xmldas = SDO_DAS_XML::create($schema_file);
unlink($schema_file);

$root = $xmldas->loadString($xml)->getRootDataObject();
$seq1 = $root->getSequence();
$cloned = clone $root;
$seq2 = $cloned->getSequence();

echo(trim($seq1[0]) === "abc");
echo "\n";
echo ($seq1[0] == $seq2[0]);
echo "\n";
?>
--EXPECT--
1
1