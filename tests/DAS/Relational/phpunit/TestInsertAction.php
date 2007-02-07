<?php 
/*
+----------------------------------------------------------------------+
| Copyright IBM Corporation 2005, 2007.                                |
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

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';

require_once 'SDO/DAS/Relational/InsertAction.php';
require_once 'SDO/DAS/Relational.php';

class TestInsertAction extends PHPUnit_Framework_TestCase
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
        $containment_references_model = new SDO_DAS_Relational_ContainmentReferencesModel($app_root_type,$SDO_reference_metadata, $database_model);
        $this->object_model = new SDO_DAS_Relational_ObjectModel($database_model, $containment_references_model);
        $this->das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);
    }

    public function testConstructor_TakesObjectModelAndDataObject() {
        $root = $this->das->createRootDataObject();
        $company = $root->createDataObject('company');
        $insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
        $this->assertTrue(get_class($insert_action) == 'SDO_DAS_Relational_InsertAction','Construction of InsertAction failed');
    }

    public function testToSQL_ContainsPropertyNameAndPlaceholder() {
        $root = $this->das->createRootDataObject();
        $root->getChangeSummary()->beginLogging();
        $company = $root->createDataObject('company');
        $company->name = 'acme';
        $insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
//      $insert_action->addFKToParentToSettings();
        $sql = $insert_action->toSQL();
        $insert_action->buildValueList();
        $value_list = $insert_action->getValueList();
        $this->assertTrue($sql == 'INSERT INTO company (name) VALUES (?);', "Generated SQL was incorrect: $sql");
        $this->assertTrue($value_list[0] == 'acme', 'First entry in value list should be "acme" but was ' . $value_list[0]);
    }

    public function testToSQL_ContainsPropertyNameAndPlaceholderWhenValueIsNull() {
        $root = $this->das->createRootDataObject();
        $root->getChangeSummary()->beginLogging();
        $company = $root->createDataObject('company');
        $company->name = 'acme';
        $company->employee_of_the_month = null;
        $insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
//      $insert_action->addFKToParentToSettings();
        $sql = $insert_action->toSQL();
        $insert_action->buildValueList();
        $value_list = $insert_action->getValueList();
        $this->assertTrue($sql == 'INSERT INTO company (name,employee_of_the_month) VALUES (?,?);', "Generated SQL was incorrect: $sql");
        $this->assertTrue($value_list[0] == 'acme', 'First entry in value list should be "acme" but was ' . $value_list[0]);
        $this->assertTrue($value_list[1] === null, 'Second entry in value list should be null but was ' . $value_list[1]);
    }

    public function testToSQL_ContainsPropertyNameAndPlaceholderWhenValueIsBlank() {
        $root = $this->das->createRootDataObject();
        $root->getChangeSummary()->beginLogging();
        $company = $root->createDataObject('company');
        $company->name = '';
        $insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
//      $insert_action->addFKToParentToSettings();
        $sql = $insert_action->toSQL();
        $insert_action->buildValueList();
        $value_list = $insert_action->getValueList();
        $this->assertTrue($sql == 'INSERT INTO company (name) VALUES (?);', "Generated SQL was incorrect: $sql");
        $this->assertTrue($value_list[0] === '', 'First entry in value list should be blank but was ' . $value_list[0]);
    }

    public function testToSQL_ContainsPropertyNameAndPlaceholderWhenValueIsZero() {
        $root = $this->das->createRootDataObject();
        $root->getChangeSummary()->beginLogging();
        $company = $root->createDataObject('company');
        $company->name = 0;
        $insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$company);
//      $insert_action->addFKToParentToSettings();
        $sql = $insert_action->toSQL();
        $insert_action->buildValueList();
        $value_list = $insert_action->getValueList();
        $this->assertTrue($sql == 'INSERT INTO company (name) VALUES (?);', "Generated SQL was incorrect: $sql");
        $this->assertTrue($value_list[0] ==='0', 'First entry in value list should be string zero (0) but was ' . $value_list[0]);
    }

    public function testToSQL_FK_created() {
        $root = $this->das->createRootDataObject();
        $root->getChangeSummary()->beginLogging();
        $company = $root->createDataObject('company');
        $department = $company->createDataObject('department');
        $department->name = 'shoe';
        $company->id = 1001;
        $insert_action = new SDO_DAS_Relational_InsertAction($this->object_model,$department);
        $insert_action->addFKToParentToSettings();
        $sql = $insert_action->toSQL();
        $insert_action->buildValueList();
        $value_list = $insert_action->getValueList();
        $this->assertTrue($sql == 'INSERT INTO department (name,co_id) VALUES (?,?);', "Generated SQL was incorrect: $sql");
        $this->assertTrue($value_list[0] ==='shoe', 'First entry in value list should be "shoe" ' . $value_list[0]);
        $this->assertTrue($value_list[1] ==='1001', 'Second entry in value list should be 1001 but was ' . $value_list[1]);
    }
}

?>
