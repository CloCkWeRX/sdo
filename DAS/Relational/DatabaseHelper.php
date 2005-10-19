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

/**
* Helper routines for use with the database.
*/

class SDO_DAS_Relational_DatabaseHelper
{
	public static function executeStatement($dbh,$stmt,$value_list)
	{
		if (SDO_DAS_Relational::DEBUG_EXECUTE_PLAN) {
			echo "executing the following SQL statement:\n" . $stmt . "\n";
			echo "using the following list of values:\n";
			foreach ($value_list as $value) {
	  			ob_start();
	  			var_dump($value);
  				$content = ob_get_contents();
  				ob_end_clean();
				echo "   $content";
			}
		}
		$pdo_stmt = $dbh->prepare($stmt);
		// TODO - FIXME
		// The following line of code really should be:
		//$pdo_stmt->execute($value_list);
		//$rows_affected = $pdo_stmt->rowCount();
		// but this is not working at 20050916 - see http://pecl.php.net/bugs/bug.php?id=5433
		// the following line works but only because boolean true gets interpreted at integer 1 !!!		
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