--TEST--
Check for sdo presence
--INI--
display_errors=off
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php 
echo "sdo extension is available";
?>
--EXPECT--
sdo extension is available
