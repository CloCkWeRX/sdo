<?php 
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005,2007.                             |
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
 * Test case for DeleteAction class
 *
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';

require_once 'SDO/DAS/Relational/UpdateAction.php';
require_once 'SDO/DAS/Relational.php';

class TestDeleteAction extends PHPUnit_Framework_TestCase
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
		unset($root[0]);
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$this->assertTrue( $change_type == SDO_DAS_ChangeSummary::DELETION);
		$delete_action = new SDO_DAS_Relational_DeleteAction($this->object_model,$changed_company,$old_values);
		$this->assertTrue(get_class($delete_action) == 'SDO_DAS_Relational_DeleteAction','Construction of DeleteAction failed');
	}


	public function testOldEqualsString() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$company->name = 'Acme';
		$root->getChangeSummary()->beginLogging();
		unset($root[0]);
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$delete_action = new SDO_DAS_Relational_DeleteAction($this->object_model,$changed_company,$old_values);
		$sql = $delete_action->toSQL();
		$value_list = $delete_action->getValueList();
		$this->assertTrue($sql == 'DELETE FROM company WHERE name = ?;', "Generated SQL was not as expected: $sql");
		$this->assertTrue($value_list[0] == 'Acme', "Value list[0] should be 'Acme': $value_list");
		$this->assertTrue(count($value_list) == 1, 'Value list should contain only one entry');
	}

	public function testOldEqualsNull() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$company->name = null;
		$root->getChangeSummary()->beginLogging();
		unset($root[0]);
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[0];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$delete_action = new SDO_DAS_Relational_DeleteAction($this->object_model,$changed_company,$old_values);
		$sql = $delete_action->toSQL();
		$value_list = $delete_action->getValueList();
		$this->assertTrue($sql == 'DELETE FROM company WHERE name IS NULL;', "Generated SQL was not as expected: $sql");
		$this->assertTrue(count($value_list) == 0, 'Value list should be empty');
	}


	public function testNCRefOldEqualsObject() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$department = $company->createDataObject('department');
		$employee = $department->createDataObject('employee');
		$company->id = 1;
		$employee->id = 99;
		$company->employee_of_the_month = $employee;
		$root->getChangeSummary()->beginLogging();
		unset($root[0]);
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[2];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$delete_action = new SDO_DAS_Relational_DeleteAction($this->object_model,$changed_company,$old_values);
		$sql = $delete_action->toSQL();
		$value_list = $delete_action->getValueList();
		$this->assertTrue($sql == 'DELETE FROM company WHERE id = ? AND employee_of_the_month = ?;', "Generated SQL was not as expected: $sql");
		$this->assertTrue($value_list[0] == 1, "Value list[0] should be '1': $value_list[0]");
		$this->assertTrue($value_list[1] == 99, "Value list[1] should be '99': $value_list[1]");
		$this->assertTrue(count($value_list) == 2, 'Value list should contain only two entries');
	}

	public function testNCRefOldEqualsNull() {
		$root = $this->das->createRootDataObject();
		$company = $root->createDataObject('company');
		$department = $company->createDataObject('department');
		$employee = $department->createDataObject('employee');
		$company->employee_of_the_month = null;
		$root->getChangeSummary()->beginLogging();
		unset($root[0]);
		$change_summary			= $root->getChangeSummary();
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$changed_company = $changed_data_objects[2];
		$old_values = $change_summary->getOldValues($changed_company);
		$change_type = $change_summary->getChangeType($changed_company);
		$delete_action = new SDO_DAS_Relational_DeleteAction($this->object_model,$changed_company,$old_values);
		$sql = $delete_action->toSQL();
		$value_list = $delete_action->getValueList();
		$this->assertTrue($sql == 'DELETE FROM company WHERE employee_of_the_month IS NULL;', "Generated SQL was not as expected: $sql");
		$this->assertTrue(count($value_list) == 0, 'Value list should be empty');
	}
}

?>