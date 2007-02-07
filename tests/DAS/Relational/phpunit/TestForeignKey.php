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
 * Test case for Foreign Key class
 *
 */
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';

require_once 'SDO/DAS/Relational/ForeignKey.php';

class TestForeignKey extends PHPUnit_Framework_TestCase
{

	public function __construct($name) {
		parent::__construct($name);
	}

	public function testFKMustBeAnArray() {
		$table_metadata = array(
		'name' => 'company',
		'columns'=> array('id'),
		'PK' => 'id',
		'FK' => null
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_ForeignKey ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'not an array') != 0,'Wrong message issued: '.$msg);
	}

	public function testFKMustContainFrom() {
		$table_metadata = array(
		'name' => 'company',
		'columns'=> array('id'),
		'PK' => 'id',
		'FK' => array('fromXXX' => 'id', 'to' => 'employee')
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_ForeignKey ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'not contain a from field') != 0,'Wrong message issued: '.$msg);
	}

	public function testFKFromMustBeAValidColumn() {
		$table_metadata = array(
		'name' => 'company',
		'columns'=> array('id'),
		'PK' => 'id',
		'FK' => array('from' => 'idXXXX', 'to' => 'employee')
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_ForeignKey ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'not in the list of columns') != 0,'Wrong message issued: '.$msg);
	}

	public function testFKMustContainTo() {
		$table_metadata = array(
		'name' => 'company',
		'columns'=> array('id'),
		'PK' => 'id',
		'FK' => array('from' => 'id', 'toXXX' => 'employee')
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_ForeignKey ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'not contain a to field') != 0,'Wrong message issued: '.$msg);
	}

	public function testFKMustContainOnlyFromAndTo() {
		$table_metadata = array(
		'name' => 'company',
		'columns'=> array('id'),
		'PK' => 'id',
		'FK' => array('from' => 'id', 'to' => 'employee', 'extraneous' => 'catchmeifyoucan')
		);
		$exception_thrown = false;
		try {
			$das = new SDO_DAS_Relational_ForeignKey ($table_metadata);
		} catch (SDO_DAS_Relational_Exception $e) {
			$exception_thrown = true;
			$msg = $e->getMessage();
		}
		$this->assertTrue($exception_thrown,'Exception was never thrown');
		$this->assertTrue(strpos($msg,'only valid keys are from and to') != 0,'Wrong message issued: '.$msg);
	}

}

?>