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

class TestTable extends PHPUnit_Framework_TestCase
{

	public function __construct($name) {
		parent::__construct($name);
	}

	public function testNameMustBePresent() {
		$table_metadata = array(
		'columns'=> array('id'),
		'PK' => 'id'
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_Table ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'did not contain a table name') != 0,'Wrong message issued: '.$msg);
	}


	public function testNameMustBeAString() {
		$table_metadata = array(
		'name' => 1,
		'columns'=> array('id'),
		'PK' => 'id'
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_Table ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'was not a string') != 0,'Wrong message issued: '.$msg);
	}

	public function testColumnsMustBePresent() {
		$table_metadata = array(
		'name' => 'company',
		'PK' => 'id'
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_Table ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'did not contain a column list') != 0,'Wrong message issued: '.$msg);
	}

	public function testColumnListMustBeAnArray() {
		$table_metadata = array(
		'name' => 'company',
		'columns'=> 'id',
		'PK' => 'id'
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_Table ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'was not an array') != 0,'Wrong message issued: '.$msg);
	}

	public function testColumnNamesMustBeStrings() {
		$table_metadata = array(
		'name' => 'company',
		'columns'=> array(1,2,3,'id'),
		'PK' => 'id'
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_Table ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'was not a string') != 0,'Wrong message issued: '.$msg);
	}

	public function testColumnNamesMustBeUnique() {
		$table_metadata = array(
		'name' => 'company',
		'columns' => array('a', 'b', 'c', 'a'),
		'PK' => 'id',
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_Table ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'duplicate column') != 0,'Wrong message issued: '.$msg);
	}

	public function testPKMustBePresent() {
		$table_metadata = array(
		'name' => 'company',
		'columns'=> array('id'),
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_Table ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'did not contain a PK') != 0,'Wrong message issued: '.$msg);
	}

	public function testPKMustBeAString() {
		$table_metadata = array(
		'name' => 'company',
		'columns'=> array('id'),
		'PK' => 1
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_Table ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'PK name that was not a string') != 0,'Wrong message issued: '.$msg);
	}


	public function testPKNameMustBeOneOfTheColumns() {
		$table_metadata = array(
		'name' => 'company',
		'columns'=> array('id'),
		'PK' => 'xyz'
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_Table ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'not one of the columns') != 0,'Wrong message issued: '.$msg);
	}


	public function testOnlyFourValidSettingsForTable() {
		$table_metadata = array(
		'name' => 'company',
		'columns'=> array('id'),
		'PK' => 'id',
		'extraneous' => 'catch me if you can'	
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_Table ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'only valid keys') != 0,'Wrong message issued: '.$msg);
	}
}

?>