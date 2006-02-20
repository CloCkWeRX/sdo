--TEST--
Check for sdo_das_xml presence
--INI--
display_errors=off
--SKIPIF--
<?php if (!extension_loaded("sdo_das_xml")) print "skip"; ?>
--FILE--
<?php 
echo "sdo_das_xml extension is available";
?>
--EXPECT--
sdo_das_xml extension is available
