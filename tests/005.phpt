--TEST--
SDO_DAS_XML load test
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php 
    $xmldas = SDO_DAS_XML::create("company.xsd");
    $xdoc = $xmldas->loadFromFile("company.xml");
    $do = $xdoc->getRootDataObject();
    var_dump($do);
?>
--EXPECT--
object(SDO_DataObjectImpl)#4 (3) {
  ["departments"]=>
  object(SDO_DataObjectList)#10 (1) {
    [0]=>
    object(SDO_DataObjectImpl)#5 (4) {
      ["employees"]=>
      object(SDO_DataObjectList)#11 (3) {
        [0]=>
        object(SDO_DataObjectImpl)#6 (2) {
          ["name"]=>
          string(10) "John Jones"
          ["SN"]=>
          string(5) "E0001"
        }
        [1]=>
        object(SDO_DataObjectImpl)#9 (2) {
          ["name"]=>
          string(8) "Jane Doe"
          ["SN"]=>
          string(5) "E0003"
        }
        [2]=>
        object(SDO_DataObjectImpl)#8 (3) {
          ["name"]=>
          string(8) "Al Smith"
          ["SN"]=>
          string(5) "E0004"
          ["manager"]=>
          bool(true)
        }
      }
      ["name"]=>
      string(21) "Advanced Technologies"
      ["location"]=>
      string(2) "NY"
      ["number"]=>
      int(123)
    }
  }
  ["name"]=>
  string(8) "MegaCorp"
  ["employeeOfTheMonth"]=>
  object(SDO_DataObjectImpl)#9 (2) {
    ["name"]=>
    string(8) "Jane Doe"
    ["SN"]=>
    string(5) "E0003"
  }
}
