--TEST--
SDO getType test
--INI--
display_errors=off
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php 
include "test.inc";

$rdo = new SDO_Model_ReflectionDataObject($company);
$type = $rdo->getType();
print "$type->namespaceURI:$type->name";

?>
--EXPECT--
companyNS:CompanyType
