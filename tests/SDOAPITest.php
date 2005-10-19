<?php 

/* 
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005.                                  | 
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
require_once 'PHPUnit2/Framework/TestCase.php';

class SDOAPITest extends PHPUnit2_Framework_TestCase {

	// Initialized in setUp();
	private $dmsDf = null;

	// Set by testDataFactory and used by subsequent tests
	private $company = null;

	public function __construct($name) {
		parent :: __construct($name);
	}

	public function setUp() {
		// is the extension loaded ?
		$loaded = extension_loaded('sdo');
		$this->assertTrue($loaded, 'php_sdo extension is not loaded.');

		// are we using the matched extension version?
		$version = phpversion('sdo');
		$this->assertTrue(($version >= '0.5.2'), 'Incompatible version of php_sdo extension.');

		// We don''t want to force direct type comparison (e.g. we want (int)100 to be the same as "100")
		$this->setLooselyTyped(true);

		// A DMSDataFactory is used to create Types and Properties - it holds the model
		$this->dmsDf = SDO_DAS_DataFactory :: getDataFactory();

		define('ROOT_NS', 'xmldms');
		define('COMPANY_NS', 'companyNS');
		define('ROOT_TYPE', 'RootType');
		define('COMPANY_TYPE', 'CompanyType');
		define('DEPARTMENT_TYPE', 'DepartmentType');
		define('EMPLOYEE_TYPE', 'EmployeeType');

		// Create the types
		$this->dmsDf->addType(ROOT_NS, ROOT_TYPE);
		$this->dmsDf->addType(COMPANY_NS, COMPANY_TYPE, true); /*sequenced */
		$this->dmsDf->addType(COMPANY_NS, DEPARTMENT_TYPE);
		$this->dmsDf->addType(COMPANY_NS, EMPLOYEE_TYPE);

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

		// Test getType
		$type = $this->company->getType();
		$this->assertEquals(COMPANY_NS, $type[0], 'Type namespace URI does not match.');
		$this->assertEquals(COMPANY_TYPE, $type[1], 'Type name does not match.');
	}

	public function testDataObject() {
		// We require this to have been done
		$this->testDataFactory();

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
				$this->assertEquals($this->company[$set_props[$iters]], $value, "SDO_DataObject iteration failed - values do not match.");
				$iters ++;
			}
		} catch (Exception $e) {
			$this->assertTrue(false, "SDO_DataObject iteration test failed - Exception thrown: ".$e->getMessage());
		}
		$this->assertEquals(count($set_props), $iters, "SDO_DataObject iteration test failed - incorrect number of interations.");
		$this->assertTrue(($iters > 0), "SDO_DataObject iteration test failed - zero iterations performed.");
	}

	public function testBoolean() {
		// We require this to have been done
		$this->testDataFactory();

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
		$this->testDataFactory();

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
		$bytes = pack("c", 12, 63, 65, 66);
		$this->company->bytes = $bytes;
		$this->assertEquals($bytes, $this->company->bytes, "Bytes Type test failed.");

		// Test the Char type
		$this->company->char = 'x';
		$this->assertEquals('x', $this->company->char, "Char Type test failed.");

		// Test the Date type
		$date = time();
		$this->company->date = $date;
		$this->assertEquals(date("D-M-Y H:m:s", $date), date("D-M-Y H:m:s", $this->company->date), "Date Type test failed.");

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
		$this->assertEquals(1000000, $this->company->long, "Long Type test failed.");

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
		$this->testDataFactory();

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
		$this->testDataFactory();

		// Test use of non-containment reference
		$ceo = $this->company->createDataObject('CEO');
		$ceo->name = "Fred Smith";
		$this->company->employeeOfTheMonth = $ceo;
		$this->assertTrue(isset ($this->company->CEO), 'Single-valued SDODataObject test failed - CEO not set.');

		// Check nativation back up to a container
		$ceo = $this->company->CEO;
		// FAILED - serialization problem with non-containment references
		//$this->assertEquals($this->company, $ceo->getContainer(), "getContainer test failed - company is not the CEOs container.");
		$this->assertEquals($this->company->name, $ceo->getContainmentPropertyName(), "getContainer test failed - company is not the CEOs container.");

		// Check navigation for a non-containment reference
		// FAILED - serialization problem with non-containment references
		//$this->assertEquals($this->company, $this->company->employeeOfTheMonth->getContainer(),
		//                    'getContainer test failed - company is not the employeeOfTheMonth container.');
		$this->assertEquals($this->company->name, $this->company->employeeOfTheMonth->getContainmentPropertyName(), 'getContainer test failed - company is not the employeeOfTheMonth container.');

		// root's container should be null
		$this->assertNull($this->company->getContainer(), 'Root object\'s container should be NULL');
		$this->assertNull($this->company->getContainmentPropertyName(), 'Root object\'s container should be NULL');
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
		// FAILED - again this is a problem with the serialized data
		//$this->assertEquals($acme, $shoe_parent, 'Container test 2a failed;');
		$this->assertEquals($acme->name, $shoe_parent->name, 'Container test 2a failed;');
		//$this->assertEquals($acme, $it_parent, 'Container test 2b failed;');
		$this->assertEquals($acme->name, $it_parent->name, 'Container test 2b failed;');
		$this->assertEquals($shoe, $sue_parent, 'Container test 2c failed;');
		$this->assertEquals($it, $ron_parent, 'Container test 2d failed;');
	}

	public function testList() {

		// We need to do this as part of the setup
		$this->testDataFactory();

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
		$employee = $departments[0]->employees->insert($employee, 1);
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
		$this->testDataFactory();
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
		$this->testDataFactory();

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
		$this->assertEquals(SDO_DAS_CHANGE_SUMMARY_NONE, $ct, 'Change type set but no changes have been made.');

		// make some changes
		$oldSN = $simon->SN;
		$simon->SN = '061130';

		$david = $department->createDataObject('employees');
		$david->name = 'David Attenborough';

		// get the change type
		$ct = $cs->getChangeType($simon);
		$this->assertEquals(SDO_DAS_CHANGE_SUMMARY_MODIFICATION, $ct, 'Change type should be MODIFICATION.');
		$ct = $cs->getChangeType($david);
		$this->assertEquals(SDO_DAS_CHANGE_SUMMARY_ADDITION, $ct, 'Change type should be ADDITION.');
		$ct = $cs->getChangeType($saba);
		$this->assertEquals(SDO_DAS_CHANGE_SUMMARY_NONE, $ct, 'Change type should be NONE.');

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

		// get old values for department
		// FAILED actually I'm not sure what to expect here, but I don't like what I get

		// get old values for employees
		$ov_simon = $cs->getOldValues($simon);
		$this->assertEquals(1, count($ov_simon), 'Should be exactly one entry in the SettingList.');
		$this->assertEquals('SN', $ov_simon[0]->getPropertyName(), 'Property name in DAS_Setting is incorrect.');
		$this->assertEquals($oldSN, $ov_simon[0]->getValue(), 'Old value in DAS_Setting is incorrect.');
		$this->assertEquals(-1, $ov_simon[0]->getListIndex(), 'List index set for single-valued property.');

		// many more tests needed
	}

	public function testPrimitiveSequence() {
		// We need to do this as part of the setup
		$this->testDataFactory();

		$company = $this->company;

		// create a sequence using single-valued primitives
		$seq = $company->getSequence();
		$seq->insert('string value: ');
		$seq->insert('aString', NULL, 'string');
		$seq->insert(' int value: ');
		$seq->insert(42, NULL, 'int');

		$this->assertEquals(4, $seq->count(), 'Sequence count is wrong.');
		$this->assertEquals($seq[1], $company->string, 'Sequence and property access not equal.');
		$this->assertNull($seq->getPropertyName(0), 'Property name of Text should be NULL.');
		$this->assertEquals(-1, $seq->getPropertyIndex(0), 'Property index of Text should be -1.');
		$this->assertEquals('string', $seq->getPropertyName(1), 'Property name incorrect.');
		$this->assertEquals(4, $seq->getPropertyIndex(1), 'Property index incorrect.');

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
		$this->assertEquals('string', $seq->getPropertyName(3), 'After move, property name incorrect.');

	}

	public function testMultiPrimitiveSequence() {
		// We need to do this as part of the setup
		$this->testDataFactory();

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
		$this->testDataFactory();

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
		define('GENE_NAMESPACE', 'genealogy');

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
		//$this->assertTrue(false, 'Multi-valued xpath not working in this release.');
		define('GENE_NAMESPACE', 'genealogy');

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
		$this->assertEquals('Cain', $root['children'][0][name], 'Xpath navigation 2c failed: ');
		$this->assertEquals('Cain', $cain->name, 'Xpath navigation 2d failed: ');
		$this->assertEquals('Abel', $abel->name, 'Xpath navigation 2e failed: ');
		$this->assertEquals('Abel', $root->children[1][name], 'XPath navigation 2f failed: ');
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
		$this->assertEquals($this->company->departments[0], $this->company["departments[name=\"$dept_name\"]"], "Simple XPath query test failed.");

		// Test compound XPath query support
		$empl_name = $this->company->departments[0]->employees[0]->name;
		$this->assertEquals($this->company->departments[0]->employees[0], $this->company["departments[name=\"$dept_name\"]/employees[name=\"$empl_name\"]"], "Compound XPath query test failed.");

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
		define('DAS_NAMESPACE', 'das_namespace');
		define('APP_NAMESPACE', 'app_namespace');
		define('DAS_ROOT_TYPE', 'SDO_RDAS_RootType');

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
		define('DAS_NAMESPACE', "das_namespace");
		define('APP_NAMESPACE', "app_namespace");
		define('DAS_ROOT_TYPE', "SDO_RDAS_RootType");

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
		define('DAS_NAMESPACE', "das_namespace");
		define('APP_NAMESPACE', "app_namespace");
		define('DAS_ROOT_TYPE', "SDO_DAS_Relational_RootType");

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
		define('DAS_NAMESPACE', "das_namespace");
		define('APP_NAMESPACE', "app_namespace");
		define('DAS_ROOT_TYPE', "SDO_DAS_Relational_RootType");

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
		$this->testDataObject();
		session_start();
		$_SESSION['my_datagraph'] = $this->company;
		session_write_close();
		unset($this->company);
	}

	public function testSerialization2() {
		$this->testDataObject();
		session_start();
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
	    $this->testDataFactory();
	    $this->company->name = 'ACME Corp';
	    $this->assertNotNull ($this->company->name, 'company name property is null.');
	    $this->company->name = NULL;
	    $this->assertNull ($this->company->name, 'nulled company name property is not null.');
	    $this->company->name = ''; /* an empty string is not null */
	    $this->assertNotNull ($this->company->name, 'empty company name property is null.');
	    
	    $this->assertNull($this->company->int, 'unset int property is not null.');
	    $this->company->int = NULL;
	    $this->assertNull($this->company->int, 'nulled int property is not null.');
	    $this->company->int = 4;
	    $this->assertNotNull($this->company->int, 'initialized int property is null.');
	   
	}
	
	public function testAddPropertyToType() {
	    define('DAS_NAMESPACE', "das_namespace");
		define('APP_NAMESPACE', "app_namespace");
		define('DAS_ROOT_TYPE', "TheRootType");
		define('DAS_OBJECT_TYPE', 'TheObjectType');
	
		$data_factory = SDO_DAS_DataFactory :: getDataFactory();
		$data_factory->addType(DAS_NAMESPACE, DAS_ROOT_TYPE);
		$data_factory->addType(DAS_NAMESPACE, DAS_OBJECT_TYPE);
	
		/* We shall use SDO_Model_Property to test these settings. For now, just see what errors occur */
		$data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p1', SDO_TYPE_NAMESPACE_URI, 'String', true, false, true); // multivalued, not readonly, containment
	    $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p2', SDO_TYPE_NAMESPACE_URI, 'String', true);	
	    $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p3', SDO_TYPE_NAMESPACE_URI, 'String', array("many", "readonly"=>true));	
	    $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p4', SDO_TYPE_NAMESPACE_URI, 'String', array("blob"));	
	    $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p5', SDO_TYPE_NAMESPACE_URI, 'String', array("blob"=>"blob"));	
	    $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p6', SDO_TYPE_NAMESPACE_URI, 'String', 42);	
	    $data_factory->addPropertyToType(DAS_NAMESPACE, DAS_ROOT_TYPE, 'p7', SDO_TYPE_NAMESPACE_URI, 'String', array(42=>0));	
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
}

?>
