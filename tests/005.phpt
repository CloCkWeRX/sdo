--TEST--
SDO_DAS_XML load test
--INI--
display_errors=off
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php 
    $dirname = dirname($_SERVER['SCRIPT_FILENAME']);
    $xmldas = SDO_DAS_XML::create("${dirname}/company.xsd");
    $xdoc = $xmldas->loadFile("${dirname}/company.xml");
    $do = $xdoc->getRootDataObject();
    print_r($do);
?>
--EXPECT--
SDO_DataObjectImpl Object
(
    [departments] => SDO_DataObjectList Object
        (
            [0] => SDO_DataObjectImpl Object
                (
                    [employees] => SDO_DataObjectList Object
                        (
                            [0] => SDO_DataObjectImpl Object
                                (
                                    [name] => John Jones
                                    [SN] => E0001
                                )

                            [1] => SDO_DataObjectImpl Object
                                (
                                    [name] => Jane Doe
                                    [SN] => E0003
                                )

                            [2] => SDO_DataObjectImpl Object
                                (
                                    [name] => Al Smith
                                    [SN] => E0004
                                    [manager] => 1
                                )

                        )

                    [name] => Advanced Technologies
                    [location] => NY
                    [number] => 123
                )

        )

    [name] => MegaCorp
    [employeeOfTheMonth] => SDO_DataObjectImpl Object
        (
            [name] => Jane Doe
            [SN] => E0003
        )

)
