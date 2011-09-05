<?php
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005, 2006.                            |
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
$Id: DatabaseHelper.php 220738 2006-09-28 19:25:00Z cem $
*/

/**
* Helper routines for use with the database.
*/

class SDO_DAS_Relational_DatabaseHelper
{
    /*
     * A basic wrapper for the call to the PDO statement execute() function
     * the added value is that a test for SQL errors is performed and a
     * row count is returned if all is well
     */
    public static function executeStatementTestForError($dbh, $pdo_stmt, $value_list, $stmt)
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

        $success = $pdo_stmt->execute($value_list);

        // Test if the SQL execute was successful
        if ( 0 == $success ) {
            // there is a problem so get the SQL error code and report it
            $msg = "\nEncountered an error when attempting to execute $stmt";
            $pdo_error_info = $pdo_stmt->errorInfo();
            $msg .= "\nThe error information returned from PDO::errorInfo() was:";
            $msg .= "\n  SQLSTATE: " . $pdo_error_info[0];
            $msg .= "\n  Driver-specific error code: " . $pdo_error_info[1];
            $msg .= "\n  Driver-specific error message: " . $pdo_error_info[2];
            $dbh->rollback();
            $msg .= "\nAll changes have been rolled back.";
            throw new SDO_DAS_Relational_Exception($msg);
        }

        $rows_affected = $pdo_stmt->rowCount();
        return $rows_affected;
    }

    /*
     * The original wrapper for the call to the PDO statement execute() function
     * This function is used when changes are being applied and so it
     * tests that some row updates have resulted from the changes. If not
     * an update collision may have occured.
    */
    public static function executeStatementTestForCollision($dbh,$stmt,$value_list)
    {
        // execute the SQL statement through PDO
        $pdo_stmt = $dbh->prepare($stmt);
        $rows_affected = SDO_DAS_Relational_DatabaseHelper::executeStatementTestForError($dbh, $pdo_stmt, $value_list, $stmt);

        // Test if the SQL execute resulted in any change rows
        if ($rows_affected == 0 )
        {
            $msg = "\nSDO/DAS/Relational.php::applyChanges resulted in no rows being affect when executing $stmt";
            $msg .= "\nThis may happen if the data that was retrieved and updated has been changed by another processs in the database in the meantime.";
            $dbh->rollback();
            $msg .= "\nAll changes have been rolled back.";
            throw new SDO_DAS_Relational_Exception($msg);
        }
    }
}

?>
