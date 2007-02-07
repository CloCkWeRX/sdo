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
 * Test case for DataObjectHelper class
 *
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';

require_once 'SDO/DAS/Relational/DataObjectHelper.php';
require_once 'SDO/DAS/Relational.php';

class TestDataObjectHelper extends PHPUnit_Framework_TestCase
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

    public function testGetPrimitiveSettings_NVPairsContainsNullWhenValueIsNull() {
        $root = $this->das->createRootDataObject();
        $company = $root->createDataObject('company');
        $company->name = null;
        $name_value_pairs = SDO_DAS_Relational_DataObjectHelper::getCurrentPrimitiveSettings($company,$this->object_model);
        $this->assertTrue($name_value_pairs['name'] === NULL,"Value list did not contain NULL");
    }

    public function testGetPrimitiveSettings_NVPairsContainsBlankWhenValueIsBlank() {
        $root = $this->das->createRootDataObject();
        $company = $root->createDataObject('company');
        $company->name = '';
        $name_value_pairs = SDO_DAS_Relational_DataObjectHelper::getCurrentPrimitiveSettings($company,$this->object_model);
        $this->assertTrue($name_value_pairs['name'] === '',"Value list contained ->" . $name_value_pairs['name'] . "<- and not blank ('')");
    }

    public function testGetPrimitiveSettings_NVPairsContainsZeroWhenValueIsZero() {
        $root = $this->das->createRootDataObject();
        $company = $root->createDataObject('company');
        $company->name = 0;
        $name_value_pairs = SDO_DAS_Relational_DataObjectHelper::getCurrentPrimitiveSettings($company,$this->object_model);
        $this->assertTrue($name_value_pairs['name'] === '0',"Value list contained ->" . $name_value_pairs['name'] . "<- and not blank ('')");
    }

}

?>
