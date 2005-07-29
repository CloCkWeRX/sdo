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

*/

require_once 'SDO/DAS/Relational/DataObjectHelper.php';

/**
* represent an action that will need to be performed against the database
*
* abstract class that defines what is common amongst actions
*
*/

abstract class SDO_DAS_Relational_Action {

	protected $do;
	protected $object_model;

	public function __construct($object_model,$do) 
	{
		$this->object_model = $object_model;
		$this->do = $do;
	}

	abstract function execute($dbh);

	abstract function toString();


//	public function getNameValuePairsForPrimitiveProperties($do)
//	{
//		return SDO_DAS_Relational_DataObjectHelper::getCurrentPrimitiveSettings($do,$this->object_model);
//	}

	public function executeStatement($dbh,$stmt,$value_list)
	{
		if (SDO_DAS_Relational::DEBUG_EXECUTE_PLAN) {
			echo "executing the following SQL statement:\n" . $stmt . "\n";
			echo "using the following list of values:\n";
			foreach ($value_list as $value) {
				echo "   $value\n";
			}
		}
		$pdo_stmt = $dbh->prepare($stmt);
		$rows_affected = $pdo_stmt->execute($value_list);
		if ($rows_affected != 1) {
			$pdo_error_info = $pdo_stmt->errorInfo();
			$msg = "\nSDO/DAS/Relational.php::applyChanges encountered an error when attempting to execute $stmt";
			if ($rows_affected == 0) {
				$msg .= "\nPDO reported no rows affected by the SQL statement.";
				$msg .= "\nThis may happen if the data that was retrieved and updated has been changed by another processs in the database in the meantime.";
			}
			$msg .= "\nThe error information returned from PDO::errorInfo() was:";
			$msg .= "\n  SQLSTATE: " . $pdo_error_info[0];
			$msg .= "\n  Driver-specific error code: " . $pdo_error_info[1];
			$msg .= "\n  Driver-specific error message: " . $pdo_error_info[2];
			$dbh->rollback();
			$msg .= "\nAll changes have been rolled back.";
			throw new SDO_DAS_Relational_Exception($msg);
		}
	}
}

?>