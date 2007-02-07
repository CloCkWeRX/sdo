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
 * Test case for DatabaseModel class
 *
 */
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';

require_once 'SDO/DAS/Relational.php';
require_once 'SDO/DAS/Relational/DatabaseModel.php';

class TestDatabaseModel extends PHPUnit_Framework_TestCase
{

	public function __construct($name) {
		parent::__construct($name);
	}

	public function testForeignKeysPointToValidTables() {
		
		$company_table_metadata = array(
		'name' => 'company',
		'columns'=> array('id'),
		'PK' => 'id'
		);
		$department_table_metadata = array(
		'name' => 'department',
		'columns'=> array('id'),
		'PK' => 'id',
		'FK' => array ('from' => 'id' , 'to' => 'XXXXcompany')
		);
		$database_metadata = array($company_table_metadata, $department_table_metadata);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational ($database_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'not a valid table name') != 0,'Wrong message issued: '.$msg);
	}


}

//		throw new PHPUnit2_Framework_IncompleteTestError();

?>