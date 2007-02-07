--TEST--
SDO var_dump test
--INI--
display_errors=off
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php 
include "test.inc";

var_dump($company);

?>
--EXPECT--
object(SDO_DataObjectImpl)#2 (4) {
  ["name"]=>
  string(8) "MegaCorp"
  ["departments"]=>
  object(SDO_DataObjectList)#6 (1) {
    [0]=>
    object(SDO_DataObjectImpl)#3 (2) {
      ["name"]=>
      string(4) "Shoe"
      ["employees"]=>
      object(SDO_DataObjectList)#7 (1) {
        [0]=>
        object(SDO_DataObjectImpl)#4 (1) {
          ["name"]=>
          string(11) "Sarah Jones"
        }
      }
    }
  }
  ["employeeOfTheMonth"]=>
  object(SDO_DataObjectImpl)#4 (1) {
    ["name"]=>
    string(11) "Sarah Jones"
  }
  ["CEO"]=>
  object(SDO_DataObjectImpl)#5 (1) {
    ["name"]=>
    string(10) "Fred Smith"
  }
}
