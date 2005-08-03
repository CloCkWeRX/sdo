<?php
/* 
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005.                                  |
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
$Id$
*/

require_once 'SDO/DAS/Relational/Action.php';

/*****************************************************************************************
* Encapsulate the ordered list of actions that we will need to perform against the database.
*
******************************************************************************************/

class SDO_DAS_Relational_Plan {

	private $steps; // ordered list of SDO_DAS_Relational_Action objects

	/**
	* add an action to the plan
	*/
	public function addAction(SDO_DAS_Relational_Action $action) 
	{
		$this->steps[] = $action;
	}

	/**
	* construct an empty plan
	*/
	public function __construct() 
	{
		$this->steps = array();
		if (SDO_DAS_Relational::DEBUG_BUILD_PLAN ) {
			echo "===============================\n";
			echo "Building plan as follows:\n";
		}
	}

	public function countSteps() 
	{
		return count($this->steps);
	}

	/**
	* print contents
	*/
	public function toString() 
	{
		$str = "[Plan: \n";
		foreach ($this->steps as $s) {
			$str .= '  ';
			$str .= $s->toString();
			$str .= "\n";
		}
		$str .= "]";
		return $str;
	}

	/**
	* execute the plan
	*/
	public function execute($dbh) 
	{
		if (SDO_DAS_Relational::DEBUG_BUILD_PLAN ) {
			echo "===============================\n";
			echo "About to execute the following plan:\n";
			echo $this->toString() . "\n";
		}
		if (SDO_DAS_Relational::DEBUG_EXECUTE_PLAN) {
			echo "===============================\n";
			echo "Executing plan as follows:\n";
		}
		$do_later = array();
		foreach ($this->steps as $s) {
			$spawned_steps = $s->execute($dbh);
			if ($spawned_steps != null) {
				$do_later = array_merge($do_later, $spawned_steps);
			}
		}
		foreach ($do_later as $s) {
			$s->execute($dbh);
		}
	}
}

?>