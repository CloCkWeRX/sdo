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
| Author: Matthew Peters                                               |
+----------------------------------------------------------------------+

*/
/**
 * Test case for UpdateAction class
 *
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';

require_once 'SDO/DAS/Relational/UpdateAction.php';
require_once 'SDO/DAS/Relational.php';

class TestUpdateAction extends PHPUnit_Framework_TestCase
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
		$this->das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);
		$this->object_model = $this->das->getObjectModel();
	}

	public function testConstructor_TakesObjectModelAndDataObjectAndOldValues() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$company->name = 'Acme';
		$root->getChangeSummary()->beginLogging();
		$company->name = 'MegaCorp';
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$this->assertTrue( $change_type == SDO_DAS_ChangeSummary::MODIFICATION);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$this->assertTrue(get_class($update_action) == 'SDO_DAS_Relational_UpdateAction','Construction of UpdateAction failed');
	}


	public function testOldEqualsStringAndNewEqualsDifferentString() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$company->name = 'Acme';
		$root->getChangeSummary()->beginLogging();
		$company->name = 'MegaCorp';
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$sql = $update_action->getSQL();
		$value_list = $update_action->getValueList();
		$this->assertTrue($sql == 'UPDATE company SET name = ? WHERE name = ?;', "Generated SQL was not as expected: $sql");
		$this->assertTrue($value_list[0] == 'MegaCorp', "Value list[0] should be 'MegaCorp': $value_list");
		$this->assertTrue($value_list[1] == 'Acme', "Value list[1] should be 'Acme': $value_list");
	}

	public function testNoUpdateWhenNewEqualsOld() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$company->name = 'Acme';
		$root->getChangeSummary()->beginLogging();
		$company->name = 'MegaCorp';
		$company->name = 'Acme';
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$sql = $update_action->getSQL();
		$value_list = $update_action->getValueList();
		$this->assertTrue(count($value_list) == 0, 'value list should be empty (no update required) when new equals old');
	}

	public function testOldEqualsNullAndNewEqualsString() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$company->name = null;
		$root->getChangeSummary()->beginLogging();
		$company->name = 'Acme';
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$sql = $update_action->getSQL();
		$value_list = $update_action->getValueList();
		$this->assertTrue($sql == 'UPDATE company SET name = ? WHERE name IS NULL;', "Generated SQL was not as expected: $sql");
		$this->assertTrue($value_list[0] == 'Acme', "Value list[0] should be 'Acme': $value_list");
	}

	public function testOldEqualsStringAndNewEqualsNull() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$company->name = 'Acme';
		$root->getChangeSummary()->beginLogging();
		$company->name = null;
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$sql = $update_action->getSQL();
		$value_list = $update_action->getValueList();
		$this->assertTrue($sql == 'UPDATE company SET name = ? WHERE name = ?;', "Generated SQL was not as expected: $sql");
		$this->assertTrue($value_list[0] === null, "Value list[0] should be null: $value_list");
		$this->assertTrue($value_list[1] == 'Acme', "Value list[1] should be 'Acme': $value_list");
	}

	public function testOldEqualsNullAndNewEqualsNullCausesNoUpdate() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$company->name = null;
		$root->getChangeSummary()->beginLogging();
		$company->name = null;
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$sql = $update_action->getSQL();
		$value_list = $update_action->getValueList();
		$this->assertTrue(count($value_list) == 0, 'settings for SET clause should be empty when both new and old are null');
	}

	public function testOldEqualsBlankAndNewEqualsNull() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$company->name = '';
		$root->getChangeSummary()->beginLogging();
		$company->name = null;
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$sql = $update_action->getSQL();
		$value_list = $update_action->getValueList();
		$this->assertTrue($sql == 'UPDATE company SET name = ? WHERE name = ?;', "Generated SQL was not as expected: $sql");
		$this->assertTrue($value_list[0] === null, "Value list[0] should be null: $value_list");
		$this->assertTrue($value_list[1] == '', "Value list[1] should be '': $value_list");
	}

	public function testOldEqualsNullAndNewEqualsBlank() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$company->name = null;
		$root->getChangeSummary()->beginLogging();
		$company->name = '';
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$sql = $update_action->getSQL();
		$value_list = $update_action->getValueList();
		$this->assertTrue($sql == 'UPDATE company SET name = ? WHERE name IS NULL;', "Generated SQL was not as expected: $sql");
		$this->assertTrue($value_list[0] == '', "Value list[0] should be '': $value_list");
		$this->assertTrue(count($value_list) == 1, "Value list should contain only one entry': $value_list");
	}


	public function testNCRefSetClauseContainsNewAndWhereClauseContainsOld() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$department = $company->createDataObject('department');
		$employee = $department->createDataObject('employee');
		$employee->id = 999;
		$employee2 = $department->createDataObject('employee');
		$employee2->id = 111;
		$company->employee_of_the_month = $employee;
		$root->getChangeSummary()->beginLogging();
		$company->employee_of_the_month = $employee2;
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$sql = $update_action->getSQL();
		$value_list = $update_action->getValueList();
		$this->assertTrue($sql == 'UPDATE company SET employee_of_the_month = ? WHERE employee_of_the_month = ?;', "Generated SQL was not as expected: $sql");
		$this->assertTrue($value_list[0] == 111, "Value list[0] should be primary key of e.o.t.m, 111: $value_list[0]");
		$this->assertTrue($value_list[1] == 999, "Value list[1] should be primary key of e.o.t.m, 999: $value_list[1]");
		$this->assertTrue(count($value_list) == 2, "Value list should contain only two entries': $value_list");
	}

	public function testNCRefNoUpdateWhenNewEqualsOld() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$department = $company->createDataObject('department');
		$employee = $department->createDataObject('employee');
		$employee2 = $department->createDataObject('employee');
		$company->employee_of_the_month = $employee;
		$root->getChangeSummary()->beginLogging();
		$company->employee_of_the_month = $employee2;
		$company->employee_of_the_month = $employee;
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$sql = $update_action->getSQL();
		$value_list = $update_action->getValueList();
		$this->assertTrue(count($value_list) == 0, 'settings for SET clause should be empty when new equals old');
	}

	public function testNCRefOldEqualsNullAndNewEqualsObject() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$department = $company->createDataObject('department');
		$employee = $department->createDataObject('employee');
		$employee->id = 999;
		$company->employee_of_the_month = null;
		$root->getChangeSummary()->beginLogging();
		$company->employee_of_the_month = $employee;
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$sql = $update_action->getSQL();
		$value_list = $update_action->getValueList();
		$this->assertTrue($sql == 'UPDATE company SET employee_of_the_month = ? WHERE employee_of_the_month IS NULL;', "Generated SQL was not as expected: $sql");
		$this->assertTrue($value_list[0] == 999, "Value list[0] should be primary key of e.o.t.m, 999: $value_list[0]");
		$this->assertTrue(count($value_list) == 1, "Value list should contain only one entry': $value_list");
	}

	public function testNCRefOldEqualsObjectAndNewEqualsNull() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$department = $company->createDataObject('department');
		$employee = $department->createDataObject('employee');
		$employee->id = 999;
		$company->employee_of_the_month = $employee;
		$root->getChangeSummary()->beginLogging();
		$company->employee_of_the_month = null;
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$update_action = new SDO_DAS_Relational_UpdateAction($this->object_model,$changed_company,$old_values);
		$sql = $update_action->getSQL();
		$value_list = $update_action->getValueList();
		$this->assertTrue($sql == 'UPDATE company SET employee_of_the_month = ? WHERE employee_of_the_month = ?;', "Generated SQL was not as expected: $sql");
		$this->assertTrue($value_list[0] === null, "Value list[0] should be null: $value_list[0]");
		$this->assertTrue($value_list[1] == 999, "Value list[1] should be primary key of e.o.t.m, 999: $value_list[1]");
		$this->assertTrue(count($value_list) == 2, "Value list should contain only two entries': $value_list");
	}

}

?>