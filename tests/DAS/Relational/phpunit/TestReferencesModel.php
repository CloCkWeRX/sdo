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
 * Test case for ReferencesModel class
 *
 */
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';

require_once 'SDO/DAS/Relational/ContainmentReferencesModel.php';

class TestReferencesModel extends PHPUnit_Framework_TestCase
{


    public function __construct($name) {
        parent::__construct($name);
    }
    
    
    public function testActiveTypesCorrectlyDetermined() {
        $company_table_metadata = array(
        'name' => 'company',
        'columns'=> array('id'),
        'PK' => 'id'
        );
        $department_table_metadata = array(
        'name' => 'department',
        'columns'=> array('id'),
        'PK' => 'id',
        'FK' => array ('from' => 'id' , 'to' => 'company')
        );
        $employee_table_metadata = array(
        'name' => 'employee',
        'columns'=> array('id'),
        'PK' => 'id',
        'FK' => array ('from' => 'id' , 'to' => 'department')
        );
        $database_metadata = array($company_table_metadata, $department_table_metadata, $employee_table_metadata);
        $database_model = new SDO_DAS_Relational_DatabaseModel($database_metadata);

        $department_reference = array('parent' => 'company', 'child' => 'department');
        $employee_reference = array('parent' => 'department', 'child' => 'employee');

        $SDO_references_metadata = array($department_reference, $employee_reference);


        // try a model rooted on company
        $references_model = new SDO_DAS_Relational_ContainmentReferencesModel('company',$SDO_references_metadata, $database_model);
        $this->assertTrue($references_model->getActiveTypes() == array('company', 'department', 'employee'),
        'Expected three active types to be found'
        );

        // try a model rooted on department
        $references_model = new SDO_DAS_Relational_ContainmentReferencesModel('department',$SDO_references_metadata, $database_model);
        $this->assertTrue($references_model->getActiveTypes() == array('department', 'employee'),
        'Expected two active types to be found'
        );

        // try a model rooted on employee
        $references_model = new SDO_DAS_Relational_ContainmentReferencesModel('employee',$SDO_references_metadata, $database_model);
        $this->assertTrue($references_model->getActiveTypes() == array('employee'),
        'Expected one active types to be found'
        );

    }

    public function testNoCycles() {
        $company_table_metadata = array(
        'name' => 'company',
        'columns'=> array('id'),
        'PK' => 'id',
        'FK' => array('from' => 'id', 'to' => 'employee')
        );
        $department_table_metadata = array(
        'name' => 'department',
        'columns'=> array('id'),
        'PK' => 'id',
        'FK' => array ('from' => 'id' , 'to' => 'company')
        );
        $employee_table_metadata = array(
        'name' => 'employee',
        'columns'=> array('id'),
        'PK' => 'id',
        'FK' => array ('from' => 'id' , 'to' => 'department')
        );
        $database_metadata = array($company_table_metadata, $department_table_metadata, $employee_table_metadata);

        $department_reference = array('parent' => 'company', 'child' => 'department');
        $employee_reference = array('parent' => 'department', 'child' => 'employee');
        $cyclic_reference = array('parent' => 'employee', 'child' => 'company');
        $SDO_references_metadata = array($department_reference, $employee_reference,$cyclic_reference);

        $exception_thrown = false;
        try {
            $database_model = new SDO_DAS_Relational_DatabaseModel($database_metadata);
            $references_model = new SDO_DAS_Relational_ContainmentReferencesModel('company',$SDO_references_metadata, $database_model);
        } catch (SDO_DAS_Relational_Exception $e) {
            $exception_thrown = true;
            $msg = $e->getMessage();
        }
        $this->assertTrue($exception_thrown,'Exception was never thrown');
        $this->assertTrue(strpos($msg,'cycle') != 0,'Wrong message issued: '.$msg);
    }



}

?>
