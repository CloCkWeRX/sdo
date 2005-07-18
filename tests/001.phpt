--TEST--
Check for sdo presence
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php 
echo "sdo extension is available";
?>
--EXPECT--
sdo extension is available
