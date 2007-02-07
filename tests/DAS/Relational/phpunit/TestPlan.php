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
 * Test case for Plan class
 *
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';

require_once 'SDO/DAS/Relational/Plan.php';

/********************************************************************************************
* Following exception and special UnitTestAction just
* created for use here. This is so we can add one of these UnitTestActions to the Plan and 
* execute it and all it will do is throw an exception, not try to write to the database
*********************************************************************************************/
class UnitTestException extends Exception {} // only defined for use here

class UnitTestAction extends SDO_DAS_Relational_Action { // one of these  is what we will add to the plan
	public function execute($dbh) {
		throw new UnitTestException(); // we throw an exception so we can tell this got called
	}
	public function toString() {
		return "unit test action";
	}
}

class TestPlan extends PHPUnit_Framework_TestCase
{

	public function __construct($name) {
		parent::__construct($name);
	}

	public function testConstruct() {
		$plan = new SDO_DAS_Relational_Plan();
		$this->assertTrue(get_class($plan) == 'SDO_DAS_Relational_Plan','Construction of Plan failed');
	}

	public function testInsertAnAction() {
		$plan = new SDO_DAS_Relational_Plan();
		$unit_test_action = new UnitTestAction();
		$plan->addAction($unit_test_action);
		$this->assertTrue($plan->countSteps() == 1,'Unit test action got lost');
	}

	public function testSimpleToString() {
		$plan = new SDO_DAS_Relational_Plan();
		$unit_test_action = new UnitTestAction();
		$plan->addAction($unit_test_action);
		$str = $plan->toString();
		$this->assertTrue(strpos($str,'unit test action') >0);
	}

	public function testExecuteRecursivelyCallsExecuteOnSteps() {
	    $dummy_pdo_dbh = null; 
		$plan = new SDO_DAS_Relational_Plan();
		$unit_test_action = new UnitTestAction();
		$plan->addAction($unit_test_action);
		$exception_thrown = false;
		try {
			$plan->execute($dummy_pdo_dbh);
		} catch (UnitTestException $e) {
			$exception_thrown = true;
		}
		$this->assertTrue($exception_thrown,'Unit test action did not get executed');
	}


	//		throw new PHPUnit2_Framework_IncompleteTestError();


}

?>