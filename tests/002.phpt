--TEST--
SDO getType test
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php 
include "test.inc";

$type = $company->getType();
print "$type[0]:$type[1]";

?>
--EXPECT--
companyNS:CompanyType