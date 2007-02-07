--TEST--
SDO_DAS_XML print test
--INI--
display_errors=off
--SKIPIF--
<?php if (!extension_loaded("sdo")) print "skip"; ?>
--FILE--
<?php 
    $dirname = dirname($_SERVER['SCRIPT_FILENAME']);
    $xmldas = SDO_DAS_XML::create("${dirname}/company.xsd");
    $xdoc = $xmldas->loadFile("${dirname}/company.xml");
    echo $xmldas;
?>
--EXPECT--
object(SDO_DAS_XML)#1 {
21 types have been defined. The types and their properties are::
1. commonj.sdo#BigDecimal
2. commonj.sdo#BigInteger
3. commonj.sdo#Boolean
4. commonj.sdo#Byte
5. commonj.sdo#Bytes
6. commonj.sdo#ChangeSummary
7. commonj.sdo#Character
8. commonj.sdo#DataObject
9. commonj.sdo#Date
10. commonj.sdo#Double
11. commonj.sdo#Float
12. commonj.sdo#Integer
13. commonj.sdo#Long
14. commonj.sdo#OpenDataObject
15. commonj.sdo#Short
16. commonj.sdo#String
17. commonj.sdo#URI
18. companyNS#CompanyType
    - departments (companyNS#DepartmentType)
    - name (commonj.sdo#String)
    - employeeOfTheMonth (companyNS#EmployeeType)
19. companyNS#DepartmentType
    - employees (companyNS#EmployeeType)
    - name (commonj.sdo#String)
    - location (commonj.sdo#String)
    - number (commonj.sdo#Integer)
20. companyNS#EmployeeType
    - name (commonj.sdo#String)
    - SN (commonj.sdo#String)
    - manager (commonj.sdo#Boolean)
21. companyNS#RootType
    - company (companyNS#CompanyType)
}