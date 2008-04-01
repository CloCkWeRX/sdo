--TEST--
SDO test xpath reference to nested open properties
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
$jungle_schema = <<<EOF
<schema xmlns="http://www.w3.org/2001/XMLSchema">
  <element name="jungle">
    <complexType>
      <sequence>
        <any minOccurs="0" maxOccurs="unbounded"/>
      </sequence>
    </complexType>
  </element>
</schema>
EOF;

$bear_schema = <<<EOF
<schema xmlns="http://www.w3.org/2001/XMLSchema">
  <complexType name="bearType">
     <sequence>
       <any minOccurs="0" maxOccurs="unbounded"/>
       <element name= "name" type="string"/>
       <element name= "weight" type="positiveInteger" />
     </sequence>
   </complexType>
</schema>
EOF;

$jungle_file = tempnam(NULL, 'jungle');
file_put_contents($jungle_file, $jungle_schema);
$bear_file = tempnam(NULL, 'bear');
file_put_contents($bear_file, $bear_schema);

$xmldas = SDO_DAS_XML::create(array($jungle_file, $bear_file));
unlink($jungle_file);
unlink($bear_file);

$xmldoc = $xmldas->createDocument();
$jungle = $xmldoc->getRootDataObject();
$mummy = $xmldas->createDataObject(NULL, 'bearType');
$baby = $xmldas->createDataObject(NULL, 'bearType');
$mummy->name = 'Mummy bear';
$mummy->weight = 700;
$baby->name = 'Baby bear';
$baby->weight = 100;

$jungle->bear = $mummy;
$mummy->bear = $baby;

echo $jungle->bear->bear->name;
echo "\n";

$bear1 = $jungle->bear;
$bear2 = $bear1->bear;

echo $bear2->name;
echo "\n";

try {
    echo $jungle['bear/bear/name'];
} catch (SDO_PropertyNotFoundException $e) {
    echo "This test fails with SDO_PropertyNotFoundException because of Tuscany-988 ";
    echo "(http://issues.apache.org/jira/browse/TUSCANY-988)\n";
}

?>
--EXPECT--
Baby bear
Baby bear
Baby bear
