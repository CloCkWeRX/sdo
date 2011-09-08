--TEST--
SDO getType test
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php 
require_once "test.inc";

$rdo = new SDO_Model_ReflectionDataObject($company);
$type = $rdo->getType();
print "$type->namespaceURI:$type->name";

?>
--EXPECT--
companyNS:CompanyType
