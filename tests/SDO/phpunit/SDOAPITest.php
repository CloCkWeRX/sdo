<?php

/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005, 2007.                            |
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+
|                                                                      |
| Licensed under the Apache License, Version 2.0 (the "License"); you  |
| may not use this file except in compliance with the License. You may |
| obtain a copy of the License at                                      |
| http://www.apache.org/licenses/LICENSE-2.0                           |
|                                                                      |
| Unless required by applicable law or agreed to in writing, software  |
| distributed under the License is distributed on an "AS IS" BASIS,    |
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
| implied. See the License for the specific language governing         |
| permissions and limitations under the License.                       |
+----------------------------------------------------------------------+
| Author: Graham Charters                                              |
|         Caroline Maynard                                             |
|         Matthew Peters                                               |
+----------------------------------------------------------------------+

*/
require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";


if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'SDOAPITest::main');
}


define('ROOT_NS', 'xmldms');
define('COMPANY_NS', 'companyNS');
define('ROOT_TYPE', 'RootType');
define('COMPANY_TYPE', 'CompanyType');
define('DEPARTMENT_TYPE', 'DepartmentType');
define('EMPLOYEE_TYPE', 'EmployeeType');

define('GENE_NAMESPACE', 'genealogy');

define('DAS_NAMESPACE', "das_namespace");
define('APP_NAMESPACE', "app_namespace");
define('DAS_ROOT_TYPE', 'SDO_RDAS_RootType');
define('DAS_OBJECT_TYPE', 'SDO_RDAS_ObjectType');

class SDOAPITest extends PHPUnit_Framework_TestCase {

    // Initialized in setUp();
    private $dmsDf = null;

    // Set by testDataFactory and used by subsequent tests
    private $company = null;

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";
        $suite  = new PHPUnit_Framework_TestSuite("SDOAPITest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }


    public function __construct($name) {
        parent :: __construct($name);
    }

    public function setUp() {
        // is the extension loaded ?
        $loaded = extension_loaded('sdo');
        $this->assertTrue($loaded, 'php_sdo extension is not loaded.');

        // are we using the matched extension version?
        $version = phpversion('sdo');
        $this->assertTrue(($version >= '1.1.2'), 'Incompatible version of php_sdo extension.');

        // We don''t want to force direct type comparison (e.g. we want (int)100 to be the same as "100")
        //		$this->setLooselyTyped(true);

        // A DMSDataFactory is used to create Types and Properties - it holds the model
        $this->dmsDf = SDO_DAS_DataFactory :: getDataFactory();

        // Create the types
        $this->dmsDf->addType(ROOT_NS, ROOT_TYPE);
        $this->dmsDf->addType(COMPANY_NS, COMPANY_TYPE, array('sequenced'=>true));
        $this->dmsDf->addType(COMPANY_NS, DEPARTMENT_TYPE);
        $this->dmsDf->addType(COMPANY_NS, EMPLOYEE_TYPE);

        $this->dmsDf->addType(COMPANY_NS, 'OpenType', array('open'=>true));
        $this->dmsDf->addType(COMPANY_NS, 'OpenSeqType', array('open'=>true, 'sequenced'=>true));


        // add properties to the root type
        $this->dmsDf->addPropertyToType(ROOT_NS, ROOT_TYPE, 'company', COMPANY_NS, COMPANY_TYPE);

        // add properties to the company type
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'name', SDO_TYPE_NAMESPACE_URI, 'String');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'departments', COMPANY_NS, DEPARTMENT_TYPE, array('many'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'employeeOfTheMonth', COMPANY_NS, EMPLOYEE_TYPE, array('containment'=>false));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'CEO', COMPANY_NS, EMPLOYEE_TYPE);
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'cs', SDO_TYPE_NAMESPACE_URI, 'ChangeSummary');

        // add properties to the department type
        $this->dmsDf->addPropertyToType(COMPANY_NS, DEPARTMENT_TYPE, 'name', SDO_TYPE_NAMESPACE_URI, 'String');
        $this->dmsDf->addPropertyToType(COMPANY_NS, DEPARTMENT_TYPE, 'location', SDO_TYPE_NAMESPACE_URI, 'String');
        $this->dmsDf->addPropertyToType(COMPANY_NS, DEPARTMENT_TYPE, 'number', SDO_TYPE_NAMESPACE_URI, 'Integer');
        $this->dmsDf->addPropertyToType(COMPANY_NS, DEPARTMENT_TYPE, 'employees', COMPANY_NS, EMPLOYEE_TYPE, array('many'=>true));

        // add properties to employee type
        $this->dmsDf->addPropertyToType(COMPANY_NS, EMPLOYEE_TYPE, 'name', SDO_TYPE_NAMESPACE_URI, 'String');
        $this->dmsDf->addPropertyToType(COMPANY_NS, EMPLOYEE_TYPE, 'SN', SDO_TYPE_NAMESPACE_URI, 'String');
        $this->dmsDf->addPropertyToType(COMPANY_NS, EMPLOYEE_TYPE, 'manager', SDO_TYPE_NAMESPACE_URI, 'Boolean');

        // add some more properties to the company type for all the different base types
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'string', SDO_TYPE_NAMESPACE_URI, 'String');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'bool', SDO_TYPE_NAMESPACE_URI, 'Boolean');
        //$this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'bigD', SDO_TYPE_NAMESPACE_URI, 'BigDecimal');
        //$this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'bigI', SDO_TYPE_NAMESPACE_URI, 'BigInteger');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'byte', SDO_TYPE_NAMESPACE_URI, 'Byte');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'bytes', SDO_TYPE_NAMESPACE_URI, 'Bytes');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'char', SDO_TYPE_NAMESPACE_URI, 'Character');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'date', SDO_TYPE_NAMESPACE_URI, 'Date');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'double', SDO_TYPE_NAMESPACE_URI, 'Double');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'float', SDO_TYPE_NAMESPACE_URI, 'Float');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'int', SDO_TYPE_NAMESPACE_URI, 'Integer');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'long', SDO_TYPE_NAMESPACE_URI, 'Long');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'short', SDO_TYPE_NAMESPACE_URI, 'Short');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'uri', SDO_TYPE_NAMESPACE_URI, 'URI');

        /* properties with defaults */
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'boolD', SDO_TYPE_NAMESPACE_URI, 'Boolean', array('default'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'uriD', SDO_TYPE_NAMESPACE_URI, 'URI',  array('default'=>'DEFAULT'));

        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'Mstring', SDO_TYPE_NAMESPACE_URI, 'String', array('many'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'Mbool', SDO_TYPE_NAMESPACE_URI, 'Boolean', array('many'=>true));
        //$this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'MbigD', SDO_TYPE_NAMESPACE_URI, 'BigDecimal', array('many'=>true));
        //$this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'MbigI', SDO_TYPE_NAMESPACE_URI, 'BigInteger', array('many'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'Mbyte', SDO_TYPE_NAMESPACE_URI, 'Byte', array('many'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'Mbytes', SDO_TYPE_NAMESPACE_URI, 'Bytes', array('many'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'Mchar', SDO_TYPE_NAMESPACE_URI, 'Character', array('many'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'Mdate', SDO_TYPE_NAMESPACE_URI, 'Date', array('many'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'Mdouble', SDO_TYPE_NAMESPACE_URI, 'Double', array('many'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'Mfloat', SDO_TYPE_NAMESPACE_URI, 'Float', array('many'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'Mint', SDO_TYPE_NAMESPACE_URI, 'Integer', array('many'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'Mlong', SDO_TYPE_NAMESPACE_URI, 'Long', array('many'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'Mshort', SDO_TYPE_NAMESPACE_URI, 'Short', array('many'=>true));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'Muri', SDO_TYPE_NAMESPACE_URI, 'URI', array('many'=>true));

    }

    public function tearDown() {
        // Can add test case cleanup here.  PHPUnit2_Framework_TestCase will automatically call it
    }

    // Test the creation of a data factory.
    public function testDataFactory() {
        // PHPUnit calls setUp() so the data factory should already
        // be set up with the model of Types and Properties

        // Would be nice to introspect the model but we don't support this,
        // so we will just build an instance instead

        // Test creation of a data object
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);
        $this->assertTrue(isset ($this->company), 'SDODataObject creation failed');

        // Test invalid namespace
        try {
            $this->company = $this->dmsDf->create('company', COMPANY_TYPE);
            $this->assertTrue(false, 'Create with invalid namespace succeeded');
        } catch (SDO_TypeNotFoundException $e) {
        }

        // Test invalid typename
        try {
            $this->company = $this->dmsDf->create(COMPANY_NS, 'DuffType');
            $this->assertTrue(false, 'Create with invalid type name succeeded');
        } catch (SDO_TypeNotFoundException $e) {
        }
    }

    public function testDataObject() {
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);

        // check the type
        $this->assertEquals(COMPANY_TYPE, $this->company->getTypeName(), 'getTypeName() failed.');
        $this->assertEquals(COMPANY_NS, $this->company->getTypeNamespaceURI(), 'getTypeNamespaceURI() failed.');

        // Test array operator and setting a property
        $this->company[0] = 'Acme';
        $this->assertEquals('Acme', $this->company[0], 'Property set/get through [] failed.');

        // Test array operator and setting a property
        $this->company->name = 'MegaCorp';
        $this->assertEquals('MegaCorp', $this->company->name, 'PropertyAccess test failed.');

        // Test creation of a child data object
        $department = $this->company->createDataObject('departments');
        $department->name = 'Shoe';
        $this->assertTrue(isset ($department), 'Child SDODataObject creation failed (department)');

        // Test creation of a child data object
        $employee = $department->createDataObject('employees');
        $this->assertTrue(isset ($employee), 'Child SDODataObject creation failed (employee)');
        $employee->name = "Sarah Jones";
        $this->company->employeeOfTheMonth = $employee;
        $this->assertTrue(isset ($this->company->employeeOfTheMonth), 'Non-containment test failed - employeeOfTheMonth not set.');
        $this->assertEquals($this->company->employeeOfTheMonth, $this->company->departments[0]->employees[0], 'Non-containment reference test failed.');

        // Test use of non-containment reference
        try {
            $eotm = $this->company->createDataObject('employeeOfTheMonth');
            $this->assertTrue(false, "Failed to throw exception in create non-contained DataObject test.");
        } catch (SDO_UnsupportedOperationException $e) {
        } catch (Exception $e) {
            $this->assertTrue(false, "Incorrect exception thrown in create non-contained DataObject test: ".$e->getMessage());
        }
        $ceo = $this->company->createDataObject('CEO');
        $ceo->name = "Fred Smith";
        $this->company->employeeOfTheMonth = $ceo;
        $this->assertTrue(isset ($this->company->CEO), 'Single-valued SDODataObject test failed - CEO not set.');

        // Test iteration over an SDO_DataObject
        // Note that the for loop returns all properties, regardless of whether they are set,
        // whereas foreach iterates over set properties only.
        $set_props = array();
        for ($i = 0; $i < count($this->company); $i++) {
            if (isset($this->company[$i])) {
                $set_props[] = $i;
            }
        }

        $iters = 0;
        try {
            foreach ($this->company as $name => $value) {
                // HACK Since the introduction of default values, foreach cannot differentiate
                // between a set property and an unset property with a default value.
                if (isset($this->company[$name])) {
                    $this->assertEquals($this->company[$set_props[$iters]], $value, "SDO_DataObject iteration failed - values do not match.");
                    $iters ++;
                } else { // HACK
                    $this->assertContains($name, array('boolD','uriD'), 'SDO_DataObject iteration failed - foreach returned property which was neither set nor defaulted.');
                }
            }
        } catch (Exception $e) {
            $this->assertTrue(false, "SDO_DataObject iteration test failed - Exception thrown: ".$e->getMessage());
        }
        $this->assertEquals(count($set_props), $iters, "SDO_DataObject iteration test failed - incorrect number of interations.");
        $this->assertTrue(($iters > 0), "SDO_DataObject iteration test failed - zero iterations performed.");

        /* set a non-existent property */
        try {
            $this->company->nonexistent = 11;
            $this->assertTrue(false, 'SDODataObject setting an invalid property succeeded.');
        } catch (SDO_PropertyNotFoundException $e) {
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, "Incorrect exception thrown for SDODataObject Setting an invalid property: ".$e->getMessage());
        }

        /* get a non-existent property */
        try {
            $value = $this->company->nonexistent;
            $this->assertTrue(false, 'SDODataObject getting an invalid property succeeded.');
        } catch (SDO_PropertyNotFoundException $e) {
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, "Incorrect exception thrown for SDODataObject Getting an invalid property: ".$e->getMessage());
        }
    }

    public function testBoolean() {
        // We require this to have been done
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);

        // Test the Boolean type
        $this->company->bool = true;
        $this->assertEquals(true, $this->company->bool, "Boolean Type test failed.");
        $this->company->bool = 1;
        $this->assertEquals(true, $this->company->bool, "Boolean Type test failed.");
        $this->company->bool = "1";
        $this->assertEquals(true, $this->company->bool, "Boolean Type test failed.");
        $this->company->bool = 1.0;
        $this->assertEquals(true, $this->company->bool, "Boolean Type test failed.");
        $this->company->bool = false;
        $this->assertEquals(false, $this->company->bool, "Boolean Type test failed.");
        $this->company->bool = 0;
        $this->assertEquals(false, $this->company->bool, "Boolean Type test failed.");
        $this->company->bool = "0";
        $this->assertEquals(false, $this->company->bool, "Boolean Type test failed.");
        $this->company->bool = 0.0;
        $this->assertEquals(false, $this->company->bool, "Boolean Type test failed.");

    }

    public function testTypes() {

        // We require this to have been done
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);

        // Test the String type
        $this->company->string = "An SDO String";
        $this->assertEquals("An SDO String", $this->company->string, "String Type test failed.");

        // Test the BigDecimal type - NOT SUPPORTED AT THE MOMENT
        //$this->company->bigD = 234346124652435.1235124632563245123234;
        //$this->assertEquals(234346124652435.1235124632563245123234, $this->company->bigD , "BigDecimal Type test failed.");

        // Test the BigInteger type - NOT SUPPORTED AT THE MOMENT
        //$this->company->bigI = 2343461246524351235124632563245123234;
        //$this->assertEquals(2343461246524351235124632563245123234, $this->company->bigI , "BigInteger Type test failed.");

        // Test the Byte type
        $this->company->byte = 127;
        $this->assertEquals(127, $this->company->byte, "Byte Type test failed.");

        // Test the Byte type (this should wrap)
        $this->company->byte = 128;
        $this->assertEquals(-128, $this->company->byte, "Byte Type test failed.");

        // Test the Bytes type
        $bytes = pack('C*', 12, 63, 65, 66);
        $this->company->bytes = $bytes;
        $this->assertEquals($bytes, $this->company->bytes, "Bytes Type test failed.");

        // Test the Char type
        $this->company->char = 'x';
        $this->assertEquals('x', $this->company->char, "Char Type test failed.");

        // Test the Date type
        $date = time();
        $this->company->date = $date;
        $this->assertEquals(gmdate("D-M-Y H:m:s", $date), gmdate("D-M-Y H:m:s", $this->company->date), "Date Type test failed.");

        // Test the Double type
        $this->company->double = pi();
        $this->assertEquals(pi(), $this->company->double, "Double Type test failed.");

        // Test the Float type
        $this->company->float = pi();
        $this->assertFalse(($this->company->float == pi()), "Float Type test failed.");

        $floatPI = 3.141592;
        $this->company->float = $floatPI;
        //FAILED - bogus test ?
        //$this->assertEquals($floatPI, $this->company->float, "Float Type test failed.");
        $this->assertFalse(($floatPI == $this->company->float), "Float Type test failed.");

        // Test the Integer type
        $this->company->int = 100;
        $this->assertEquals(100, $this->company->int, "Integer Type test failed.");

        // Test the Long type
        $this->company->long = 1000000;
        /* Can't use assertEquals here because PHPUnit2 takes the type into account for comparisons.
        * SDO Longs are returned as Strings, because they may overflow a PHP Integer.
        */
        $this->assertTrue(1000000 == $this->company->long, "Long Type test failed.");

        // Test the Short type
        $this->company->short = 100;
        $this->assertEquals(100, $this->company->short, "Short Type test failed.");

        // Test the URI type
        $uri = 'http://www.company.com/a/b/c.d';
        $this->company->uri = $uri;
        $this->assertEquals($uri, $this->company->uri, "URI Type test failed.");

    }

    /* test multi-valued primitives */
    public function testMTypes() {
        // We require this to have been done
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);

        $mstring = $this->company->Mstring;

        // Test the MString type
        $mstring[] = 'An SDO String';
        $this->assertEquals('An SDO String', $mstring[0], "MString Type test failed.");

        // replace at specific index
        $mstring[0] = 'A different SDO String';
        $this->assertEquals('A different SDO String', $mstring[0], "MString Type test failed.");
        try {
            $mstring[10] = 'String at bad index';
        } catch (SDO_IndexOutOfBoundsException $e) {
        } catch (Exception $e) {
            $this->assertTrue(false, 'Incorrect exception for SDOList Insert at invalid index;'.$e->getMessage());
        }

        try {
            // insert at specific index
            $mstring->insert('Inserted new first string', 0);
            // insert without index (same as append)
            $mstring->insert('Inserted new last string');
        } catch (Exception $e) {
            $this->assertTrue(false, "SDO_List insert failed - Exception thrown: ".$e->getMessage());
        }

        foreach ($mstring as $index => $value) {
            $this->assertEquals($mstring[$index], $value, 'SDO_List iteration failed - values do not match;');
        }
        $this->assertEquals(count($mstring), (1 + $index), 'SDO_List iteration test failed - incorrect number of interations;');
        $this->assertTrue(($index > 0), 'SDO_List iteration test failed - zero iterations performed.');

        $oldsize = $mstring->count();
        unset ($mstring[1]);
        $this->assertEquals(($oldsize -1), count($mstring), 'SDO_List count incorrect after unsetting an element;');
    }

    public function testNavigation() {
        // Run this to initialize the data object
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);

        // Test use of non-containment reference
        $ceo = $this->company->createDataObject('CEO');
        $ceo->name = "Fred Smith";
        $this->company->employeeOfTheMonth = $ceo;
        $this->assertTrue(isset ($this->company->CEO), 'Single-valued SDODataObject test failed - CEO not set.');

        // Check nativation back up to a container
        $ceo = $this->company->CEO;
        $this->assertEquals($this->company, $ceo->getContainer(), "getContainer test failed - company is not the CEOs container.");

        // Check navigation for a non-containment reference
        $this->assertEquals($this->company, $this->company->employeeOfTheMonth->getContainer(),
        'getContainer test failed - company is not the employeeOfTheMonth container.');

        // root's container should be null
        $this->assertNull($this->company->getContainer(), 'Root object\'s container should be NULL');
    }

    public function testNavigation2() {
        // Matthews's test for bug 430
        $acme = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);
        $acme->name = "Acme";
        $shoe = $acme->createDataObject('departments');
        $it = $acme->createDataObject('departments');
        $shoe->name = 'Shoe';
        $shoe->location = 'A-block';
        $it->name = 'IT';
        $sue = $shoe->createDataObject('employees');
        $sue->name = 'Sue';
        $ron = $it->createDataObject('employees');
        $ron->name = 'Ron';
        $acme->employeeOfTheMonth = $ron;
        $shoe_parent = $shoe->getContainer();
        $it_parent = $it->getContainer();
        $sue_parent = $sue->getContainer();
        $ron_parent = $ron->getContainer();
        $this->assertEquals($acme, $shoe_parent, 'Container test 2a failed;');
        $this->assertEquals($acme->name, $shoe_parent->name, 'Container test 2a failed;');
        $this->assertEquals($acme, $it_parent, 'Container test 2b failed;');
        $this->assertEquals($acme->name, $it_parent->name, 'Container test 2b failed;');
        $this->assertEquals($shoe, $sue_parent, 'Container test 2c failed;');
        $this->assertEquals($it, $ron_parent, 'Container test 2d failed;');
        $this->assertEquals($it, $acme->employeeOfTheMonth->getContainer(), 'Container test 2e failed;');
    }

    public function testList() {

        // We need to do this as part of the setup
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);

        $department = $this->company->createDataObject('departments');
        $departments = $this->company->departments;

        // Create a new employee to insert
        $employee = $this->dmsDf->create(COMPANY_NS, EMPLOYEE_TYPE);
        $employee->name = 'Aristotle';
        try {
            $departments->insert($employee);
            $this->assertTrue(false, 'SDOList Insert of invalid type succeeded.');
        } catch (SDO_InvalidConversionException $e) {
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, "Incorrect exception thrown for SDOList Insert of invalid type: ".$e->getMessage());
        }

        // Try to add the employee to the first department
        $num_employees = count($departments[0]->employees);
        $departments[0]->employees->insert($employee);
        $this->assertEquals($employee, $departments[0]->employees[$num_employees], "SDOList append through insert failed");

        // Test creating new employee directly in the list (should be appended)
        $num_employees = count($departments[0]->employees);
        $employee = $departments[0]->createDataObject('employees');
        $this->assertEquals($employee, $departments[0]->employees[$num_employees], "createDataObject for many-value DO property failed.");

        // Test appending an employee through empty []
        $num_employees = count($departments[0]->employees);
        $employee = $this->dmsDf->create(COMPANY_NS, EMPLOYEE_TYPE);
        $employee->name = 'Mr Jums';
        $departments[0]->employees[] = $employee;
        $this->assertEquals($employee, $departments[0]->employees[$num_employees], "SDOList append through [] failed.");

        // Test insert at a specific index
        $employee = $this->dmsDf->create(COMPANY_NS, EMPLOYEE_TYPE);
        $employee->name = 'Rum Tum Tugger';
        $departments[0]->employees->insert($employee, 1);
        $this->assertEquals($employee, $departments[0]->employees[1], "SDOList insert at specified index failed.");

        // Test insert out of range
        $employee = $this->dmsDf->create(COMPANY_NS, EMPLOYEE_TYPE);
        $employee->name = 'Victoria';
        try {
            $departments[0]->employees->insert($employee, 100);
            $this->assertTrue(false, 'SDOList Insert out of range failed to throw an exception.');
        } catch (SDO_IndexOutOfBoundsException $e) {
        } catch (Exception $e) {
            $this->assertTrue(false, 'Incorrect Exception thrown in insert test:'.$e->getMessage());
        }

        // Test overwriting an element
        $employee = $this->dmsDf->create(COMPANY_NS, EMPLOYEE_TYPE);
        $employee->name = 'Old Deuteronomy';
        $departments[0]->employees[0] = $employee;
        $this->assertEquals($employee->name, $departments[0]->employees[0]->name, 'SDOList DataObject overwrite assignment test failed.');

        // Test iteration over an SDO_List
        $iters = 0;
        try {
            foreach ($departments[0]->employees as $key => $value) {
                $this->assertEquals($departments[0]->employees[$iters], $value, "SDO_List iteration failed - values do not match.");
                $iters ++;
            }
        } catch (Exception $e) {
            $this->assertTrue(false, "SDO_List iteration test failed - Exception thrown: ".$e->getMessage());
        }
        $this->assertEquals(count($departments[0]->employees), $iters, "SDO_List iteration test failed - incorrect number of interations.");
        $this->assertTrue(($iters > 0), "SDO_List iteration test failed - zero iteration performed.");

        /* test unsetting a list element */
        $emp3 = $departments[0]->employees[3];
        $emps = $departments[0]->employees->count();
        unset ($departments[0]->employees[2]);
        $this->assertEquals(($emps -1), ($departments[0]->employees->count()), 'SDO_List count incorrect after unsetting an element');
        $this->assertEquals($emp3, $departments[0]->employees[2], 'SDO_List not reordered after unsetting an element');

        /* Test unsetting the whole list */
        unset ($departments[0]->employees);
        $this->assertEquals(0, ($departments[0]->employees->count()), 'SDO_List count incorrect after unsetting the whole list');

    }

    public function testListEquality() {
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);
        $department1 = $this->company->createDataObject('departments');
        $department1->name = 'Duplicate';
        $department2 = $this->company->createDataObject('departments');
        $department2->name = $department1->name; // Create a new employee to insert
        $employee = $this->dmsDf->create(COMPANY_NS, EMPLOYEE_TYPE);
        $employee->name = 'Alan';
        $department1->employees->insert($employee);
        $department2->createDataObject('employees');
        $department2->employees[0]->name = 'Alan';
        $employee = $this->dmsDf->create(COMPANY_NS, EMPLOYEE_TYPE);
        $employee->name = 'Bertha';
        $department1->employees[] = $employee;
        $department2->createDataObject('employees');
        $department2->employees[1]->name = $employee->name;
        $this->assertTrue($department1 == $department2, 'Similar lists fail ==');
        $this->assertFalse($department1 === $department2, 'Similar but non-identical lists pass ===');
    }

    public function testChangeSummary() {
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);

        $department = $this->company->createDataObject('departments');
        $department->name = 'Zoology';
        $simon = $department->createDataObject('employees');
        $simon->name = 'Simon King';
        $saba = $department->createDataObject('employees');
        $saba->name = 'Saba Douglas-Hamilton';

        // Get change summary for the company
        $cs = $department->getChangeSummary();
        $this->assertFalse(is_null($cs), 'ChangeSummary is null.');

        // Turn on logging
        $cs->beginLogging();
        $this->assertTrue($cs->isLogging(), 'Logging is not turned on.');

        // get the change type
        $ct = $cs->getChangeType($simon);
        $this->assertEquals(SDO_DAS_ChangeSummary::NONE, $ct, 'Change type set but no changes have been made.');

        // make some changes
        $simon->SN = '061130';

        $david = $department->createDataObject('employees');
        $david->name = 'David Attenborough';

        // get the change type
        $ct = $cs->getChangeType($simon);
        $this->assertEquals(SDO_DAS_ChangeSummary::MODIFICATION, $ct, 'Change type should be MODIFICATION.');
        $ct = $cs->getChangeType($david);
        $this->assertEquals(SDO_DAS_ChangeSummary::ADDITION, $ct, 'Change type should be ADDITION.');
        $ct = $cs->getChangeType($saba);
        $this->assertEquals(SDO_DAS_ChangeSummary::NONE, $ct, 'Change type should be NONE.');

        // get changed objects
        $changed = $cs->getChangedDataObjects();
        $this->assertEquals(3, $changed->count(), 'Wrong number of entries in the ChangedDataObjects list.');

        // ChangedDataObjectList is read-only
        try {
            unset ($changed[0]);
            $this->assertTrue(false, 'Unsetting an element of a ChangedDataObjectList succeeded');
        } catch (SDO_UnsupportedOperationException $e) {
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, 'Incorrect Exception thrown:'.$e->getMessage());
        }

        // get old values for employees
        $ov_simon = $cs->getOldValues($simon);
        $this->assertEquals(1, count($ov_simon), 'Should be exactly one entry in the SettingList.');
        $this->assertEquals('SN', $ov_simon[0]->getPropertyName(), 'Property name in DAS_Setting is incorrect.');
        $this->assertFalse($ov_simon[0]->isSet(), 'Old value in DAS_Setting is incorrect.');
        $this->assertEquals(-1, $ov_simon[0]->getListIndex(), 'List index set for single-valued property.');

        // many more tests needed
    }

    public function testPrimitiveSequence() {
        // We need to do this as part of the setup
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);

        $company = $this->company;

        // create a sequence using single-valued primitives
        $seq = $company->getSequence();
        $seq->insert('string value: ');
        $seq->insert('aString', NULL, 'string');
        $seq->insert(' int value: ');
        $seq->insert(42, NULL, 'int');

        $this->assertEquals(4, $seq->count(), 'Sequence count is wrong.');
        $this->assertEquals($seq[1], $company->string, 'Sequence and property access not equal.');
        $this->assertNull($seq->getProperty(0), 'Property of Text should be NULL.');
        $this->assertEquals('string', $seq->getProperty(1)->getName(), 'Property name incorrect.');

        // modify the sequence
        $seq[0] = 'new'.$seq[0];
        $seq[1] = strrev($seq[1]);
        $seq[2] = ' new'.$seq[2];
        $seq[3] = - $seq[3];

        $this->assertEquals(4, $seq->count(), 'After modify, sequence count is wrong.');
        $this->assertEquals($seq[1], $company->string, 'After modify, sequence and property access not equal.');

        // re-arrange the sequence
        $seq->move(3, 0);
        $seq->move(3, 0);
        $this->assertEquals(4, $seq->count(), 'After move, sequence count is wrong.');
        $this->assertEquals($seq[3], $company->string, 'After move, sequence and property access not equal.');
        $this->assertEquals('string', $seq->getProperty(3)->getName(), 'After move, property name incorrect.');

    }

    public function testMultiPrimitiveSequence() {
        // We need to do this as part of the setup
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);

        $company = $this->company;

        // create a sequence using multi-valued primitives
        $seq = $company->getSequence();
        for ($i = 0; $i < 5; $i ++) {
            $seq->insert("Mstring{$i}: ");
            $seq->insert("string{$i}", NULL, 'Mstring');
        }
        $this->assertEquals(2 * $i, count($seq), 'Sequence count is wrong.');
        try {
            $this->assertEquals($seq[1], $company->Mstring[0], 'Sequence and property access not equal.');
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, "Many-valued primitive sequence failed. Exception thrown: ".$e->getMessage());
        }

        // Test iteration over a sequence
        $iters = 0;
        try {
            foreach ($seq as $key => $value) {
                $this->assertEquals($seq[$iters], $value, "SDO_Sequence iteration failed - values do not match.");
                $iters ++;
            }
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, "SDO_Sequence iteration test failed - Exception thrown: ".$e->getMessage());
        }
        $this->assertEquals(count($seq), $iters, "SDO_Sequence iteration test failed - incorrect number of interations.");
        $this->assertTrue(($iters > 0), "SDO_Sequence iteration test failed - zero iteration performed.");
    }

    public function testDataObjectSequence() {
        // We need to do this as part of the setup
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);

        $company = $this->company;

        // create a sequence using single-valued data objects
        $seq = $company->getSequence();
        $ceo = $this->dmsDf->create(COMPANY_NS, EMPLOYEE_TYPE);
        $ceo->name = "Fred Smith";
        $seq->insert('The CEO is ');
        $seq->insert($ceo, NULL, 'CEO');

        $this->assertEquals($company->CEO, $seq[1], 'Sequence and property access not equal.');

    }

    public function testXPath1() {
        // single-valued primitives */

        $data_factory = SDO_DAS_DataFactory :: getDataFactory();
        $data_factory->addType(GENE_NAMESPACE, 'person');
        $data_factory->addPropertyToType(GENE_NAMESPACE, 'person', 'name', SDO_TYPE_NAMESPACE_URI, 'String');
        $data_factory->addPropertyToType(GENE_NAMESPACE, 'person', 'child', GENE_NAMESPACE, 'person');

        $root = $data_factory->create(GENE_NAMESPACE, 'person');
        $root['name'] = 'Eve';
        $root->createDataObject('child');
        $root['child/name'] = 'Cain';

        $this->assertEquals('Cain', $root->child->name, 'XPath navigation 1a failed: ');
        $this->assertEquals('Cain', $root['child/name'], 'XPath navigation 1b failed: ');
    }

    public function testXPath2() {
        // multi-valued primitives */

        $data_factory = SDO_DAS_DataFactory :: getDataFactory();
        $data_factory->addType(GENE_NAMESPACE, 'person');
        $data_factory->addPropertyToType(GENE_NAMESPACE, 'person', 'name', SDO_TYPE_NAMESPACE_URI, 'String');
        $data_factory->addPropertyToType(GENE_NAMESPACE, 'person', 'address_line', SDO_TYPE_NAMESPACE_URI, 'String', array('many'=>true));
        $data_factory->addPropertyToType(GENE_NAMESPACE, 'person', 'children', GENE_NAMESPACE, 'person', array('many'=>true, 'containment'=>true));

        $root = $data_factory->create(GENE_NAMESPACE, 'person');
        $root['name'] = 'Eve';
        $root['address_line[1]'] = 'The Garden';
        $root['address_line.1'] = 'Paradise';
        $root->createDataObject('children');
        $root->createDataObject('children');
        $root['children[1]/name'] = 'Cain';
        $root['children.1/name'] = 'Abel';
        $cain = $root['children.0'];
        $abel = $root['children[2]'];

        $this->assertEquals('The Garden', $root['address_line.0'], 'Xpath navigation 2a failed: ');
        $this->assertEquals('Paradise', $root['address_line[2]'], 'Xpath navigation 2b failed: ');
        $this->assertEquals('Cain', $root['children'][0]['name'], 'Xpath navigation 2c failed: ');
        $this->assertEquals('Cain', $cain->name, 'Xpath navigation 2d failed: ');
        $this->assertEquals('Abel', $abel->name, 'Xpath navigation 2e failed: ');
        $this->assertEquals('Abel', $root->children[1]['name'], 'XPath navigation 2f failed: ');
        $this->assertEquals('Abel', $root['children.1/name'], 'XPath navigation 2g failed: ');
        $this->assertEquals('Abel', $root['children[2]/name'], 'XPath navigation 2h failed: ');
    }

    public function testXPath() {
        // We need to do this as part of the setup
        $this->testDataObject();

        // Test simple dotted form by getting the first department
        $dept = $this->company['departments.0'];
        $this->assertEquals($this->company->departments[0], $dept, "Dotted XPath get (e.g. departments.0) failed.");

        // Test simple square brackets form by getting the first department
        $dept = $this->company['departments[1]'];
        $this->assertEquals($this->company->departments[0], $dept, "Square brackets XPath get (e.g. departments[1]) failed.");

        // Test simple dotted form by setting the second department
        $dept = $this->company->createDataObject('departments');
        $dept->name = 'IT';
        $newDept = $this->dmsDf->create(COMPANY_NS, DEPARTMENT_TYPE);
        $newDept->name = 'Advanced Technologies';
        $this->company['departments.1'] = $newDept;
        $this->assertEquals($this->company->departments[1], $newDept, "Dotted XPath set (e.g. departments.1) failed.");

        // Test simple square brackets form by setting the third department
        $dept = $this->company->createDataObject('departments');
        $dept->name = 'HR';
        $newDept = $this->dmsDf->create(COMPANY_NS, DEPARTMENT_TYPE);
        $newDept->name = 'Human Resources';
        $this->company['departments[3]'] = $newDept;
        $this->assertEquals($this->company->departments[2], $newDept, "Square brackets XPath set (e.g. departments[1]) failed.");

        // Test navigation down containment references
        $this->assertEquals($this->company->departments[0]->employees[0]->name, $this->company['departments.0/employees.0/name'], "XPath containment tree navigation failed.");

        // Test navigation down a non-containment reference
        $this->assertEquals($this->company->employeeOfTheMonth->name, $this->company['employeeOfTheMonth/name'], "XPath non-containment tree navigation failed.");

        // Test simple XPath query
        $dept_name = $this->company->departments[0]->name;
        $this->assertEquals($this->company->departments[0], $this->company["departments[name='$dept_name']"], "Simple XPath query test failed.");

        // Test compound XPath query support
        $empl_name = $this->company->departments[0]->employees[0]->name;
        $this->assertEquals($this->company->departments[0]->employees[0], $this->company["departments[name='$dept_name']/employees[name='$empl_name']"], "Compound XPath query test failed.");

        // Test failure case for XPath dotted index form
        try {
            $dept = $this->company['departments.1000'];
            $this->assertTrue(false, "Failed to throw exception in XPath dotted form out of bounds test.");
        } catch (SDO_IndexOutOfBoundsException $e) {
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, "Incorrect exception thrown in XPath dotted form out of bounds test: ".$e->getMessage());
        }

        // Test failure case for XPath square brackets index form
        try {
            $dept = $this->company['departments[1001]'];
            $this->assertTrue(false, "Failed to throw exception in XPath square brackets form out of bounds test.");
        } catch (SDO_IndexOutOfBoundsException $e) {
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, "Incorrect exception thrown in XPath square brackets form out of bounds test: ".$e->getMessage());
        }

        // Test bogus compound XPath navigation
        try {
            $empl = $this->company['departments.0/banana'];
            $this->assertTrue(false, "Failed to throw exception in XPath invalid property test.");
        } catch (SDO_PropertyNotFoundException $e) {
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, 'Incorrect exception thrown in XPath invalid property test:'.$e->getMessage());
        }

        // Test bogus XPath query
        try {
            $dept = $this->company['departments[name="Bogus Department"]'];
            $this->assertTrue(false, "Failed to throw exception in XPath invalid query test.");
        } catch (SDO_IndexOutOfBoundsException $e) {
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, 'Incorrect exception thrown in XPath invalid query test:'.$e->getMessage());
        }

    }

    public function testBug448() {

        $data_factory = SDO_DAS_DataFactory :: getDataFactory();
        $data_factory->addType(DAS_NAMESPACE, DAS_ROOT_TYPE);

        $data_factory->addType(APP_NAMESPACE, 'company');
        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'company', APP_NAMESPACE, 'company', array('many'=>true)); // multivalued, not readonly, containment

        $data_factory->addPropertyToType(APP_NAMESPACE, 'company', 'name', SDO_TYPE_NAMESPACE_URI, 'String'); // singlevalued, not readonly, non-containment
        $data_factory->addPropertyToType(APP_NAMESPACE, 'company', 'id', SDO_TYPE_NAMESPACE_URI, 'Integer'); // singlevalued, not readonly, non-containment

        $root = $data_factory->create(DAS_NAMESPACE, DAS_ROOT_TYPE);
        $acme = $root->createDataObject('company');

        $my_integer = 100;
        $acme['name'] = $my_integer;
        $my_new_integer = 1 + $my_integer; // has my_integer been freed?
        // if we get here, the Fatal error did not occur

        $my_string = 'ACME Corp';
        $acme['id'] = $my_string;
        $my_new_string = strrev($my_string);
        // if we get here, the Fatal error did not occur

    }

    public function testBug437() {

        $data_factory = SDO_DAS_DataFactory :: getDataFactory();
        $data_factory->addType(DAS_NAMESPACE, DAS_ROOT_TYPE);

        $data_factory->addType(APP_NAMESPACE, 'company');
        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'company', APP_NAMESPACE, 'company', array('many'=>true)); // multivalued, not readonly, containment

        $root = $data_factory->create(DAS_NAMESPACE, DAS_ROOT_TYPE);
        $acme = $root->createDataObject('company');

        $data_factory = SDO_DAS_DataFactory :: getDataFactory();
        $data_factory->addType(DAS_NAMESPACE, DAS_ROOT_TYPE);

        $data_factory->addType(APP_NAMESPACE, 'company');
        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'company', APP_NAMESPACE, 'company', array('many'=>true)); // multivalued, not readonly, containment

        $root2 = $acme->getContainer();
    }

    public function testBug459() {

        $data_factory = SDO_DAS_DataFactory :: getDataFactory();
        $data_factory->addType(DAS_NAMESPACE, DAS_ROOT_TYPE);
        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'cs', SDO_TYPE_NAMESPACE_URI, 'ChangeSummary');

        $data_factory->addType(APP_NAMESPACE, 'company');

        $data_factory->addPropertyToType(APP_NAMESPACE, 'company', 'name', SDO_TYPE_NAMESPACE_URI, 'String'); //singlevalued, not readonly, non-containment

        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'company', APP_NAMESPACE, 'company', array('many'=>true)); //multivalued, not readonly, containment

        $root = $data_factory->create(DAS_NAMESPACE, DAS_ROOT_TYPE);

        $acme = $root->createDataObject('company');
        $megacorp = $root->createDataObject('company');
        $acme2 = $root->createDataObject('company');

        $acme['name'] = 'Acme';
        $megacorp['name'] = 'MegaCorp';
        $acme2['name'] = 'Acme';

        $this->assertTrue($acme == $acme, 'Object is not equal to itself');
        $this->assertTrue($acme != $megacorp, 'Object is equal to a different object');
        $this->assertTrue($acme == $acme2, 'Object is not equal to an equivalent object');
    }

    public function testBug455() {
        $data_factory = SDO_DAS_DataFactory :: getDataFactory();
        $data_factory->addType(DAS_NAMESPACE, DAS_ROOT_TYPE);
        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'cs', SDO_TYPE_NAMESPACE_URI, 'ChangeSummary');

        $data_factory->addType(APP_NAMESPACE, 'company');
        $data_factory->addType(APP_NAMESPACE, 'department');
        $data_factory->addType(APP_NAMESPACE, 'employee');

        $data_factory->addPropertyToType(APP_NAMESPACE, 'company', 'name', SDO_TYPE_NAMESPACE_URI, 'String'); //singlevalued, not readonly, non-containment
        $data_factory->addPropertyToType(APP_NAMESPACE, 'department', 'name', SDO_TYPE_NAMESPACE_URI, 'String'); //singlevalued, not readonly, non-containment
        $data_factory->addPropertyToType(APP_NAMESPACE, 'employee', 'name', SDO_TYPE_NAMESPACE_URI, 'String'); // singlevalued, not readonly, non-containment

        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'company', APP_NAMESPACE, 'company', array('many'=>true)); // multivalued, not readonly, containment
        $data_factory->addPropertyToType(APP_NAMESPACE, 'company', 'department', APP_NAMESPACE, 'department', array('many'=>true)); // multivalued, not readonly, containment
        $data_factory->addPropertyToType(APP_NAMESPACE, 'department', 'employee', APP_NAMESPACE, 'employee', array('many'=>true)); // multivalued, not readonly, containment

        $root = $data_factory->create(DAS_NAMESPACE, DAS_ROOT_TYPE);

        $acme = $root->createDataObject('company');
        $acme['name'] = 'Acme';

        $shoe = $acme->createDataObject('department');
        $shoe->name = 'Shoe';
        $it = $acme->createDataObject('department');
        $it->name = 'IT';

        $sue = $shoe->createDataObject('employee');
        $sue->name = "Sue";
        $billy = $it->createDataObject('employee');
        $billy->name = "Billy";

        // Want to swap Sue and Billy so, while holding them in variables, should be able to unset from department then reinsert

        unset ($shoe['employee']);
        unset ($it['employee']);

        $container = $billy->getContainer();
        $this->assertNull($container, 'DataObject whose property has been unset still has a container');

        $shoe['employee']->insert($billy);
        $it['employee'][] = $sue;
        $this->assertEquals($shoe->name, $billy->getContainer()->name, 'Inserted DataObject has wrong container');
        $this->assertEquals($it->name, $sue->getContainer()->name, 'Appended DataObject has wrong container');
    }

    public function testSerialization1() {
        // sesion_start(); /* This is done within phpunit */
        $this->testDataObject();
        $_SESSION['my_datagraph'] = $this->company;
        session_write_close();
        unset($this->company);
    }

    public function testSerialization2() {
        // sesion_start(); /* This is done within phpunit */
        $this->testDataObject();
        $company2 = $_SESSION['my_datagraph'];
        $this->assertEquals($this->company->name, $company2->name, 'unserializing failed.');
        $this->assertEquals($this->company->departments[0]->name, $company2->departments[0]->name, 'unserializing failed.');
        $this->assertEquals($this->company->CEO->name, $company2->CEO->name, 'unserializing failed.');
        $this->assertEquals($this->company->departments[0]->employees[0]->name, $company2->departments[0]->employees[0]->name, 'unserializing failed.');
        $this->assertEquals($this->company->employeeOfTheMonth->name, $company2->employeeOfTheMonth->name, 'unserializing failed.');

        // FAILED because char type becomes string after round trip
        //$this->testDataObject();
        //$this->assertTrue($company2 == $this->company, 'values not equal after serialization round-trip');
    }

    public function testSetUnset() {
        $this->testDataObject();

        // multi-valued DataObject property
        $departments = $this->company->departments;
        $dept_count = count($departments);
        $this->assertTrue(isset($departments[0]), 'first department is not set.');
        $this->assertFalse(isset($departments[$dept_count]), 'invalid department is set (1).');

        // add a new department
        $new_dept = $this->company->createDataObject('departments');
        $new_dept->name = 'Gubbins';
        $this->assertTrue(isset($departments[$dept_count]), 'new department is set.');
        $this->assertFalse(isset($departments[1 + $dept_count]), 'invalid department is set (2).');

        // unset the first one
        unset($this->company->departments[0]);
        $this->assertEquals($dept_count, $departments->count(), 'wrong department count (local ref).');
        $this->assertEquals($dept_count, $this->company->departments->count(), 'wrong department count (full ref).');
        $this->assertEquals($new_dept, $departments[0], 'list order wrong after element unset (local ref).');
        $this->assertEquals($new_dept, $this->company->departments[0], 'list order wrong after element unset (full ref).');

        // single valued Integer property
        $this->assertFalse(isset($this->company->int), 'uninitialized Integer property satisfies isset().');
        $this->company->int = 4;
        $this->assertTrue(isset($this->company->int), 'set Integer property fails isset().');
        $this->assertFalse(empty($this->company->int), 'non-zero Integer property satisfies empty().');
        $this->company->int = 0;
        $this->assertTrue(isset($this->company->int), 'set Integer property fails isset().');
        $this->assertTrue(empty($this->company->int), 'zero-valued Integer property fails empty().');
        unset($this->company->int);
        $this->assertFalse(isset($this->company->int), 'unset Integer property satisfies isset().');

        // multi-valued Integer property
        $this->assertFalse(isset($this->company->Mint[0]), 'uninitialized Integer list property satisfies isset().');
        $this->assertEquals(0, $this->company->Mint->count(), 'uninitialized Integer list has non-zero count.');
        $this->company->Mint->insert(4);
        $this->assertEquals(1, $this->company->Mint->count(), 'Integer list has wrong count.');
        $this->assertTrue(isset($this->company->Mint[0]), 'set Integer list property fails isset().');
        $this->assertFalse(empty($this->company->Mint[0]), 'non-zero Integer list property satisfies empty().');
        $this->company->Mint[0] = 0;
        $this->assertTrue(isset($this->company->Mint[0]), 'set Integer list property fails isset().');
        $this->assertTrue(empty($this->company->Mint[0]), 'zero-valued Integer list property fails empty().');
        unset($this->company->Mint[0]);
        $this->assertFalse(isset($this->company->Mint[0]), 'unset Integer list property satisfies isset().');
        $this->assertEquals(0, count($this->company->Mint), 'unset Integer list has non-zero count.');
    }

    public function testNull() {
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);
        $this->company->name = 'ACME Corp';
        $this->assertNotNull ($this->company->name, 'company name property is null.');
        $this->company->name = NULL;
        $this->assertNull ($this->company->name, 'nulled company name property has value "'.$this->company->name.'" ');
        $this->company->name = ''; /* an empty string is not null */
        $this->assertNotNull ($this->company->name, 'empty company name property is null.');

        $this->company->int = NULL;
        $this->assertNull($this->company->int, 'nulled int property has value "'.$this->company->int.'" ');
        $this->company->int = 4;
        $this->assertNotNull($this->company->int, 'initialized int property is null.');
    }

    public function testAddPropertyToType() {

        $data_factory = SDO_DAS_DataFactory :: getDataFactory();
        $data_factory->addType(DAS_NAMESPACE, DAS_ROOT_TYPE);
        $data_factory->addType(DAS_NAMESPACE, DAS_OBJECT_TYPE);

        /* We shall use SDO_Model_Property to test these settings. For now, just see what errors occur */
        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p1', SDO_TYPE_NAMESPACE_URI, 'String', array('many'=>true));
        //	    $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p2', SDO_TYPE_NAMESPACE_URI, 'String', true);
        //	    $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p3', SDO_TYPE_NAMESPACE_URI, 'String', array("many", "readonly"=>true));
        //	    $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p4', SDO_TYPE_NAMESPACE_URI, 'String', array("blob"));
        //	    $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p5', SDO_TYPE_NAMESPACE_URI, 'String', array("blob"=>"blob"));
        //	    $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p6', SDO_TYPE_NAMESPACE_URI, 'String', 42);
        //	    $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p7', SDO_TYPE_NAMESPACE_URI, 'String', array(42=>0));
        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p8', SDO_TYPE_NAMESPACE_URI, 'String', array("many"=>42));
        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p9', SDO_TYPE_NAMESPACE_URI, 'String', array("many"=>0));
        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p10', SDO_TYPE_NAMESPACE_URI, 'String', array("many"=>NULL));

        /* try some default values */
        try {
            $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'defmi', SDO_TYPE_NAMESPACE_URI, 'Integer', array('many'=>true,'default'=>5));
            $this->assertTrue(false, "Failed to throw exception in multivalued setDefault test.");
        } catch (SDO_UnsupportedOperationException $e) {
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, 'Incorrect exception thrown in multivalued setDefault test:'.$e->getMessage());
        }

        try {
            $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'defsdo', DAS_NAMESPACE, DAS_OBJECT_TYPE, array('default'=>5));
            $this->assertTrue(false, "Failed to throw exception in DataObject setDefault test.");
        } catch (SDO_UnsupportedOperationException $e) {
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, 'Incorrect exception thrown in DataObject setDefault test:'.$e->getMessage());
        }

        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'defsi', SDO_TYPE_NAMESPACE_URI, 'Integer', array('default'=>5));

        $root = $data_factory->create(DAS_NAMESPACE, DAS_ROOT_TYPE);
        $this->assertEquals($root->defsi, 5, 'Default value not set for uninitialized property');
        $root->defsi = 10;
        $this->assertEquals($root->defsi, 10, 'Assignment to property with default value failed');
        unset($root->defsi);
        $this->assertEquals($root->defsi, 5, 'Default value not set for property which has been unset');
    }

    public function testClone() {
        $this->testDataObject();
        $employees = $this->company->departments[0]->employees;
        $old_employee = $employees[0];
        $new_employee = clone($old_employee);
        $this->assertEquals($new_employee, $old_employee, 'Cloned DataObject not equal to original');
        $this->assertNull($new_employee->getContainer(), 'Cloned DataObject should have no container');
        $emp_count = count($employees);
        $new_employee->name = 'Dolly the Sheep';
        $employees[] = $new_employee;
        $this->assertTrue($employees[0] != $employees[$emp_count], 'Cloned DataObject not independent of original');
        $this->assertEquals($employees[$emp_count], $new_employee, 'Append of cloned object failed');
    }

    public function testBug45771() {

        $data_factory = SDO_DAS_DataFactory :: getDataFactory();
        $data_factory->addType(DAS_NAMESPACE, DAS_ROOT_TYPE);
        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'cs', SDO_TYPE_NAMESPACE_URI, 'ChangeSummary');

        $data_factory->addType(APP_NAMESPACE, 'company');
        $data_factory->addType(APP_NAMESPACE, 'department');

        // add company to the root type
        $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'company', APP_NAMESPACE, 'company', array('many'=>true));

        $data_factory->addPropertyToType(APP_NAMESPACE, 'company', 'name', SDO_TYPE_NAMESPACE_URI, 'String');
        $data_factory->addPropertyToType(APP_NAMESPACE, 'company', 'department', APP_NAMESPACE, 'department', array('many'=>true));

        $data_factory->addPropertyToType(APP_NAMESPACE, 'department', 'name', SDO_TYPE_NAMESPACE_URI, 'String');
        $data_factory->addPropertyToType(APP_NAMESPACE, 'department', 'location', SDO_TYPE_NAMESPACE_URI, 'String');
        $data_factory->addPropertyToType(APP_NAMESPACE, 'department', 'postcode', SDO_TYPE_NAMESPACE_URI, 'String');


        $root = $data_factory->create(DAS_NAMESPACE, DAS_ROOT_TYPE);

        $acme = $root->createDataObject('company');
        $shoe = $acme->createDataObject('department');
        $shoe->name = 'Shoe';
        $shoe->location = null;

        $root->getChangeSummary()->beginLogging();
        $shoe->name = null;
        $shoe->location = 'A-block';
        $shoe->postcode = 'SO21 2JN';

        $change_summary	= $root->getChangeSummary();
        $changed_data_objects = $change_summary->getChangedDataObjects();
        $this->assertEquals(1, $changed_data_objects->count(), 'Wrong number of changed objects');
        $settings = $change_summary->getOldValues($changed_data_objects[0]);
        $this->assertEquals(3, $settings->count(), 'Wrong number of old values');
        $this->assertTrue($settings[0]->isset(), 'Incorrect isset flag when new value is null');
        $this->assertEquals('Shoe', $settings[0]->getValue(), 'Wrong old value when new value is null');
        $this->assertTrue($settings[1]->isset(), 'Incorrect isset flag when old value is null');
        $this->assertNull($settings[1]->getValue(), 'Wrong old value when old value is null');
        // tracker 46236
        $this->assertFalse($settings[2]->isset(), 'Incorrect isset flag when old value is unset');
    }

    public function testReflection() {
        $this->testDataObject();

        $rdo = new SDO_Model_ReflectionDataObject($this->company);

        $do_type = $rdo->getType();
        $this->assertEquals(COMPANY_NS, $do_type->namespaceURI, 'Wrong reflected value for type namespaceURI');
        $this->assertEquals(COMPANY_TYPE, $do_type->getName(), 'Wrong reflected value for type name');
        $this->assertTrue($do_type->isInstance($this->dmsDf->create(COMPANY_NS, COMPANY_TYPE)),
        'Two objects of the same type should be type-equal');
        $this->assertFalse($do_type->isInstance($this->company->departments[0]),
        'Two objects of the different types should not be type-equal');
        $this->assertFalse($do_type->isDataType(), 'Unexpected value for SDO_Model_Type->isDataType()');
        $this->assertTrue($do_type->isSequencedType(), 'Unexpected value for SDO_Model_Type->isSequencedType()');
        $this->assertFalse($do_type->isOpenType(), 'Unexpected value for SDO_Model_Type->isOpenType()');
        $this->assertNull($do_type->getBaseType(), 'Unexpected value for SDO_Model_Type->getBaseType()');

        $do_property = $do_type->getProperty(0);
        $this->assertEquals($do_property, $do_type->getProperty($do_property->name),
        'Reflected properties should be equal');
        $do_property = $do_type->getProperty('departments');
        $this->assertEquals($do_property->getName(), 'departments', 'Unexpected value for SDO_Model_Property->getName()');
        $this->assertEquals($do_property->getContainingType(), $do_type,
        'Unexpected value for SDO_Model_Property->getContainingType()');
        $this->assertFalse($do_property->isReadOnly(), 'Unexpected value for SDO_Model_Property->isReadOnly()');
        $this->assertTrue($do_property->isMany(), 'Unexpected value for SDO_Model_Property->isMany()');
        $this->assertTrue($do_property->isContainment(), 'Unexpected value for SDO_Model_Property->isContainment()');
        $this->assertNull($do_property->getOpposite(), 'Unexpected value for SDO_Model_Property->getOpposite()');

        $eotm_property = $do_type->getProperty('employeeOfTheMonth');
        $this->assertFalse($eotm_property->isMany(), 'Unexpected value for SDO_Model_Property->isMany()');
        $this->assertFalse($eotm_property->isContainment(), 'Unexpected value for SDO_Model_Property->isContainment()');

        $all_props = $do_type->getProperties();
        $this->assertEquals(count($all_props), count($this->company),
        'Different number of properties in reflected data object');

        /* Create a dataobject using a SDO_Model_Property */
        $department_property = $do_type->getProperty('departments');
        $department2 = $this->company->createDataObject($department_property);
        $department2->name = 'IT';
        $this->assertEquals($department_property->getType()->getName(), $department2->getTypeName(),
        'type name is different from reflected type name');
        $this->assertEquals($department_property->getType()->getNamespaceURI(), $department2->getTypeNamespaceURI(),
        'type namespace URI is different from reflected type namespace URI');
        $this->assertEquals($department2->getContainer(), $this->company,
        'getContainer() test failed for object created using reflected property');
    }

    public function testDefaultValue() {
        $this->company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);
        $this->assertFalse(isset($this->company->boolD), 'isset is true for uninitialized Boolean property with default value');
        $this->assertTrue($this->company->boolD, 'Unexpected default value for uninitialized Boolean property with default value');

        $this->company->boolD = false;
        $this->assertTrue(isset($this->company->boolD), 'isset is false for set Boolean property with default value');
        $this->assertFalse($this->company->boolD, 'Unexpected value for set Boolean property with default value');

        unset($this->company->boolD);
        $this->assertFalse(isset($this->company->boolD), 'isset is true for unset Boolean property with default value');
        $this->assertTrue($this->company->boolD, 'Unexpected value for unset Boolean property with default value');
    }

    public function testOpenType() {
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'open', COMPANY_NS, 'OpenType');
        $this->dmsDf->addPropertyToType(COMPANY_NS, 'OpenType', 'name', SDO_TYPE_NAMESPACE_URI, 'String');

        $company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);
        $company->name = "ACME Corp";

        $open = $company->createDataObject('open');
        $rdo = new SDO_Model_ReflectionDataObject($open);
        $prop_count = count($rdo->getInstanceProperties());
        $this->assertEquals($prop_count, count($rdo->getType()->getProperties()),
        'count of instance properties differs from count of type properties');
        $this->assertTrue($rdo->getType()->isOpenType(), 'isOpen flag not set');

        $old_prop_count = $prop_count;
        $open->name = 'OpenName';
        $open->stringA = "StringValue";
        $prop_count++;
        $open->numberA = 42;
        $prop_count++;
        $open->object = $this->dmsDf->create(COMPANY_NS, EMPLOYEE_TYPE);
        $prop_count++;
        $open->object->name='Alien';
        $open['stringB'] = $open['stringA'];
        $prop_count++;
        $open['numberB'] = $open['numberA'];
        $prop_count++;
        $this->assertEquals(42, $open->numberB,
        'wrong value for number property of open type');
        $this->assertEquals('StringValue', $open->stringB,
        'wrong value for string property of open type');

        $rdo = new SDO_Model_ReflectionDataObject($open);
        $this->assertEquals($prop_count, count($rdo->getInstanceProperties()),
        'wrong number of instance properties after adding open types');
        /* ... but the Type should not have changed */
        $this->assertEquals($old_prop_count, count($rdo->getType()->getProperties()),
        'count of instance properties differs from count of type properties');

        unset ($open->numberA);
        $prop_count--;
        $this->assertEquals($prop_count, count($rdo->getInstanceProperties()),
        'wrong number of instance properties after unsetting open type');

        $this->assertTrue(isset($open->numberB), 'valid instance property of open type fails isset()');
        $this->assertFalse(isset($open->numberA), 'unset instance property of open type satisfies isset()');
        $this->assertFalse(isset($open->numberC), 'non-existent instance property of open type satisfies isset()');

        /* get a non-existent property from an open type */
        try {
            $value = $open->nonexistent;
            $this->assertTrue(false, 'SDODataObject getting an invalid property from an open type succeeded.');
        } catch (SDO_PropertyNotFoundException $e) {
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, "Incorrect exception thrown for SDODataObject Getting an invalid property from an open type: ".$e->getMessage());
        }
    }

    public function testOpenSeqType() {
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'openSeq', COMPANY_NS, 'OpenSeqType');
        $this->dmsDf->addPropertyToType(COMPANY_NS, 'OpenSeqType', 'name', SDO_TYPE_NAMESPACE_URI, 'String');

        $company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);
        $company->name = "ACME Corp";

        $open = $company->createDataObject('openSeq');

        $seq = $open->getSequence();

        /* first add an existing property */
        $seq->insert('Sequence name=>');
        $seq->insert('OpenSeqName', NULL, 'name');
        $this->assertEquals('OpenSeqName', $open->name, 'sequence and property access not equal;');

        /* now add an unknown string property to the open type */
        $seq->insert(' stringA=>');
        $seq->insert('StringValue', NULL, 'stringA');
        $this->assertEquals('StringValue', $open->stringA, 'sequence and property access for open string type not equal;');

        /*  ... an unknown number property ... */
        $seq->insert(' numberA=>');
        $seq->insert(42, NULL, 'numberA');
        $this->assertEquals(42, $open->numberA, 'sequence and property access for open int type not equal;');

        /* ... and an unknown data object property */
        $employee = $this->dmsDf->create(COMPANY_NS, EMPLOYEE_TYPE);
        $seq->insert($employee, NULL, 'object');
        $employee->name = 'Alien';
        $this->assertEquals('Alien', $open->object->name, 'sequence and property access for open object type not equal;');
    }

    public function testDerivedType() {
        $this->dmsDf->addType(COMPANY_NS, 'DerivedStringType', array('basetype'=>array(SDO_TYPE_NAMESPACE_URI, 'String')));
        $this->dmsDf->addType(COMPANY_NS, 'BaseObjectType', array('abstract'=>true));
        $this->dmsDf->addType(COMPANY_NS, 'DerivedObjectType', array('basetype'=>array(COMPANY_NS, 'BaseObjectType')));
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'derivedString', COMPANY_NS, 'DerivedStringType');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'derivedObject', COMPANY_NS, 'DerivedObjectType');
        $this->dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'abstractObject', COMPANY_NS, 'BaseObjectType');

        $company = $this->dmsDf->create(COMPANY_NS, COMPANY_TYPE);
        $company->name = "ACME Corp";
        $company->derivedString = 'Special string';

        $do = $this->dmsDf->create(COMPANY_NS, 'DerivedObjectType');
        try {
            $this->dmsDf->create(COMPANY_NS, 'BaseObjectType');
            $this->assertTrue(false, 'Succeeded in instantiating an abstract type.');
        } catch (SDO_UnsupportedOperationException $e) {
        } catch (SDO_Exception $e) {
            $this->assertTrue(false, 'Incorrect exception thrown from creating an object of an abstract type:'.$e->getMessage());
        }

        /* It's OK to assign an object of a derived type to a property of an abstract type */
        $company->abstractObject = $do;

        $rcompany = new SDO_Model_ReflectionDataObject($company);
        $special_type = $rcompany->getType()->getProperty('derivedString')->getType();
        $base_type = $special_type->getBaseType();
        $this->assertEquals(SDO_TYPE_NAMESPACE_URI, $base_type->getNamespaceURI(), 'incorrect namespaceURI for base type');
        $this->assertEquals('String', $base_type->getName(), 'incorrect name for base type');
        $this->assertTrue($base_type->isDataType(), 'incorrect data type for base type');

        $rdo = new SDO_Model_ReflectionDataObject($do);
        $derived_type = $rdo->getType();
        $this->assertFalse($derived_type->isAbstractType(), 'non-abstract type passes isAbstractType()');
        $this->assertTrue($derived_type->isInstance($do), 'object of derived type fails DerivedType::isInstance()');

        $base_type = $derived_type->getBaseType();
        $this->assertEquals(COMPANY_NS, $base_type->getNamespaceURI(), 'incorrect namespaceURI for base type');
        $this->assertEquals('BaseObjectType', $base_type->getName(), 'incorrect name for base type');
        $this->assertFalse($base_type->isDataType(), 'incorrect data type for base type');
        $this->assertTrue($base_type->isAbstractType(), 'abstract type fails isAbstractType()');
        $this->assertTrue($base_type->isInstance($do), 'object of derived type fails BaseType::isInstance()');

    }
}
if (PHPUnit_MAIN_METHOD == 'SDOAPITest::main') {
    SDOAPITest::main();
}

?>
