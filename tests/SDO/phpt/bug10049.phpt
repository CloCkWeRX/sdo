--TEST--
SDO test for copy object with property set to null
--INI--
display_errors=off
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php

$order_xsd = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<schema xmlns="http://www.w3.org/2001/XMLSchema"
 xmlns:ord="orderNS" targetNamespace="orderNS">

  <element name="order" type="ord:OrderType">
    <complexType name="OrderType">
      <sequence>
        <element name="customer">
          <complexType>
            <sequence>
            </sequence>
          </complexType>
        </element>
      </sequence>
    </complexType>
  </element>

</schema>
EOF;

$schema_file = tempnam(NULL, 'xsd');
file_put_contents($schema_file, $order_xsd);

$xmldas = SDO_DAS_XML::create($schema_file);
unlink($schema_file);

$order = $xmldas->createDataObject('orderNS','OrderType');
$order->customer = null;

$o = clone $order;
echo ($o == $order);

?>
--EXPECT--
1