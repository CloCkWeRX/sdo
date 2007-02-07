--TEST--
SDO reflection test
--INI--
display_errors=off
--SKIPIF--
<?php if (!extension_loaded("sdo") ||
    phpversion('sdo') < '0.7.1') print "skip"; ?>
--FILE--
<?php 
include "test.inc";

$rdo = new SDO_Model_ReflectionDataObject($company);
print $rdo; 

?>
--EXPECT--
object(SDO_Model_ReflectionDataObject)#6 {
  - ROOT OBJECT
  - Type <dataObject> companyNS#CompanyType
  - Instance Properties[4] {
        commonj.sdo#String $name;
        companyNS#DepartmentType $departments[] {
            commonj.sdo#String $name;
            commonj.sdo#String $location;
            commonj.sdo#Integer $number;
            companyNS#EmployeeType $employees[] {
                commonj.sdo#String $name;
                commonj.sdo#String $SN;
                commonj.sdo#Boolean $manager;
            }
        }
        <reference> companyNS#EmployeeType $employeeOfTheMonth;
        companyNS#EmployeeType $CEO {
            commonj.sdo#String $name;
            commonj.sdo#String $SN;
            commonj.sdo#Boolean $manager;
        }
    }
}
