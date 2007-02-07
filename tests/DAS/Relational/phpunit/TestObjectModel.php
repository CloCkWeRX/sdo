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
 * Test case for ObjectModel class
 *
 */
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';

require_once 'SDO/DAS/Relational/ObjectModel.php';

class TestObjectModel extends PHPUnit_Framework_TestCase
{

    public function __construct($name) {
        parent::__construct($name);
    }

    public function testAppRootTypeNotInModel() {
        //      throw new PHPUnit2_Framework_IncompleteTestError();
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
        $database_metadata = array($company_table_metadata, $department_table_metadata);
        $department_reference = array('parent' => 'company', 'child' => 'department');
        $SDO_references_metadata = array($department_reference);
        $exception_thrown = false;
        try {
            $database_model = new SDO_DAS_Relational_DatabaseModel($database_metadata);
            $references_model = new SDO_DAS_Relational_ContainmentReferencesModel('not-a-valid-reference-or-table',$SDO_references_metadata, $database_model);
            $object_model = new SDO_DAS_Relational_ObjectModel($database_model, $references_model);
        } catch (SDO_DAS_Relational_Exception $e) {
            $exception_thrown = true;
            $msg = $e->getMessage();
        }
        $this->assertTrue($exception_thrown,'Exception was never thrown');
        $this->assertTrue(strpos($msg,'does not appear') != 0,'Wrong message issued: '.$msg);
    }

    public function testAnyParentMustBeATable() {
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
        $database_metadata = array($company_table_metadata, $department_table_metadata);

        $department_reference = array('parent' => 'not-a-valid-table', 'child' => 'department');
        $SDO_references_metadata = array($department_reference);

        $exception_thrown = false;
        try {
            $database_model = new SDO_DAS_Relational_DatabaseModel($database_metadata);
            $references_model = new SDO_DAS_Relational_ContainmentReferencesModel('company',$SDO_references_metadata, $database_model);
            $object_model = new SDO_DAS_Relational_ObjectModel($database_model, $references_model);
        } catch (SDO_DAS_Relational_Exception $e) {
            $exception_thrown = true;
            $msg = $e->getMessage();
        }
        $this->assertTrue($exception_thrown,'Exception was never thrown');
        $this->assertTrue(strpos($msg,'not specified in the database metadata') != 0,'Wrong message issued: '.$msg);
    }

    public function testNoSupportingFKFound() {
        $company_table_metadata = array(
        'name' => 'company',
        'columns'=> array('id'),
        'PK' => 'id'
        );
        $department_table_metadata = array(
        'name' => 'department',
        'columns'=> array('id'),
        'PK' => 'id',
        // deliberately comment this one out to remove the supportingFK     'FK' => array ('from' => 'id' , 'to' => 'company')
        );
        $database_metadata = array($company_table_metadata, $department_table_metadata);
        $department_reference = array('parent' => 'company', 'child' => 'department');
        $SDO_references_metadata = array($department_reference);
        $exception_thrown = false;
        try {
            $database_model = new SDO_DAS_Relational_DatabaseModel($database_metadata);
            $references_model = new SDO_DAS_Relational_ContainmentReferencesModel('company',$SDO_references_metadata, $database_model);
            $object_model = new SDO_DAS_Relational_ObjectModel($database_model,$references_model);
        } catch (SDO_DAS_Relational_Exception $e) {
            $exception_thrown = true;
            $msg = $e->getMessage();
        }
        $this->assertTrue($exception_thrown,'Exception was never thrown');
        $this->assertTrue(strpos($msg,'to support the reference') != 0,'Wrong message issued: '.$msg);
    }


}

?>
