--TEST--
SDO exception test
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php 
require_once "test.inc";
try {
    $file = __FILE__;
    $line = __LINE__ + 1;
    $bad = $company->bad;
} catch (SDO_Exception $e) {
    print "getMessage(): ".$e->getMessage()."\n";
    
    print "getFile(): ".($file == $e->getFile())."\n";
    
    print "getLine(): ".($line == $e->getLine())."\n";
}

?>
--EXPECT--
getMessage(): Cannot find property:bad
getFile(): 1
getLine(): 1
