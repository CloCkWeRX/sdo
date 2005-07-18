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
| Author: Matthew Peters                                               |
+----------------------------------------------------------------------+

*/
/**
 * Test case for InsertAction class
 *
 */

require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/IncompleteTestError.php';

require_once 'SDO/DAS/Relational/InsertAction.php';
require_once 'SDO/DAS/Relational.php';

class TestInsertAction extends PHPUnit2_Framework_TestCase
{
	private $object_model;
	private $das;

	public function __construct($name) {
		parent::__construct($name);
	}

	public function setUp() {
		/*****************************************************************
		* METADATA DEFINING THE DATABASE
		******************************************************************/
		$company_table = array (
		'name' => 'company',
		'columns' => array('id', 'name',  'employee_of_the_month'),
		'PK' => 'id',
		'FK' => array (
		'from' => 'employee_of_the_month',
		'to' => 'employee',
		),
		);
		$department_table = array (
		'name' => 'department',
		'columns' => array('id', 'name', 'location' , 'number', 'co_id'),
		'PK' => 'id',
		'FK' => array (
		'from' => 'co_id',
		'to' => 'company',
		)
		);
		$employee_table = array (
		'name' => 'employee',
		'columns' => array('id', 'name', 'SN', 'manager', 'dept_id'),
		'PK' => 'id',
		'FK' => array (
		'from' => 'dept_id',
		'to' => 'department',
		)
		);
		$database_metadata = array($company_table, $department_table, $employee_table);

		/*******************************************************************
		* METADATA DEFINING SDO CONTAINMENT REFERENCES
		*******************************************************************/
		$department_reference = array( 'parent' => 'company', 'child' => 'department'); //optionally can specify property name
		$employee_reference = array( 'parent' => 'department', 'child' => 'employee');

		$SDO_reference_metadata = array($department_reference, $employee_reference);
		$app_root_type = 'company';
		$database_model = new SDO_DAS_Relational_DatabaseModel($database_metadata);
		$containment_references_model = new SDO_DAS_Relational_ContainmentReferencesModel($app_root_type,$SDO_reference_metadata);
		$this->object_model = new SDO_DAS_Relational_ObjectModel($database_model, $containment_references_model);
		$this->das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);
	}

	public function testConstruct() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
		$this->assertTrue(get_class($insert_action) == 'SDO_DAS_Relational_InsertAction','Construction of InsertAction failed');
	}

	public function testBasicConvertToString() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
		$str = $insert_action->toString();
		$this->assertTrue(strpos($str,'company') >0);
	}

	public function testSimpleConvertToSQL() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$company->name = 'acme';
		$insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
		$name_value_pairs = SDO_DAS_Relational_DataObjectHelper::getCurrentPrimitiveSettings($company,$this->object_model);
		$sql = $insert_action->toSQL($name_value_pairs);
		$this->assertTrue(strpos($sql,'name')>0);
		$this->assertTrue(strpos($sql,'acme')>0);
	}

	///////////////////////////////////////////////////////////
	// tests relating to nulls removed - not supported
	///////////////////////////////////////////////////////////
	
//	public function testNullDetectedAndConverted() {
//		$company = $this->das->create();
//		$company->name = 'acme';
//		throw new PHPUnit2_Framework_IncompleteTestError(); // awaiting null support
//		$company->id = null;
//		$insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
//		$name_value_pairs = $insert_action->getNameValuePairsFromObject();
//		$sql = $insert_action->toSQL($name_value_pairs);
//		$this->assertTrue(strpos($sql,'name')>0);
//		$this->assertTrue(strpos($sql,'acme')>0);
//		$this->assertTrue(strpos($sql,'id')>0);
//		$this->assertTrue(strpos($sql,'NULL')>0); // make sure the SQL contains NULL
//	}
//
//	public function testBlankNotConvertedToNull() {
//		$company = $this->das->create();
//		$company->name = '';
//		throw new PHPUnit2_Framework_IncompleteTestError(); // awaiting null support
//		// failing due to Bug 425 and use of == to compare to null
//		$insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
//		$sql = $insert_action->toSQL();
//		$this->assertTrue(strpos($sql,'name')>0);
//		$this->assertTrue(!strpos($sql,'NULL')); // check NULL not found
//	}
//
//	public function testZeroNotConvertedToNull() {
//		$company = $this->das->create();
//		$company->name = 'acme';
//		$company->id = 0;
//		throw new PHPUnit2_Framework_IncompleteTestError(); // awaiting null support
//		// failing due to Bug 425 and use of == to compare to null
//		$insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
//		$sql = $insert_action->toSQL();
//		$this->assertTrue(strpos($sql,'id')>0);
//		$this->assertTrue(!strpos($sql,'NULL')); // check NULL not found
//	}


///////////////////////////////////////////////////////////////
// there is currently no way a boolean could be assigned to a property so remove these two tests
///////////////////////////////////////////////////////////////

//
//	public function testBooleanTrueDetectedAndConvertedToOne() {
//		$company = $this->das->create();
//		$company->name = 'acme';
//		$company->id = true;
//		$insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
//		$sql = $insert_action->toSQL();
//		$this->assertTrue(strpos($sql,'name')>0);
//		$this->assertTrue(strpos($sql,'acme')>0);
//		$this->assertTrue(strpos($sql,'feeling_good')>0);
//		$this->assertTrue(strpos($sql,'"1"')>0); // make sure the SQL contains a "1"
//	}
//
//	public function testBooleanFalseDetectedAndConvertedToZero() {
//		// This test will fail until Bug 425 is resolved
//		// The issue is that we are currently using == to compare for NULL (would prefer to use ===)
//		// and (false == null) in PHP world
//
//		$company = $this->das->create();
//		$company->name = 'acme';
//		$company->id = false;
//		throw new PHPUnit2_Framework_IncompleteTestError(); // awaiting null support
//		$insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
//		$sql = $insert_action->toSQL();
//		$this->assertTrue(strpos($sql,'name')>0);
//		$this->assertTrue(strpos($sql,'acme')>0);
//		$this->assertTrue(strpos($sql,'feeling_good')>0);
//		$this->assertTrue(strpos($sql,'"0"')>0); // make sure the SQL contains a "0"
//	}

	//		throw new PHPUnit2_Framework_IncompleteTestError();


}

?>