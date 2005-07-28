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

require_once 'SDO/DAS/Relational.php';
require_once 'company_metadata.inc.php';

/**
 * Scenario - Create one company and one department 
 *
 */

/**************************************************************
 * Get and initialise a DAS with the metadata
 ***************************************************************/
try {
    $das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);
} catch (SDO_DAS_Relational_Exception $e) {
    echo "SDO_DAS_Relational_Exception raised when trying to create the DAS.";
    echo "Probably something wrong with the metadata.";
    echo "\n".$e->getMessage();
    exit();
}

/**************************************************************
 * Issue a query to obtain the company data object
 ***************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);

try {
	$root = $das->executeQuery($dbh,
	'select c.id, c.name, d.id, d.name from company c, department d where d.co_id = c.id',
	array('company.id','company.name','department.id','department.name'));
} catch (SDO_DAS_Relational_Exception $e) {
	echo "SDO_DAS_Relational_Exception raised when trying to retrieve data from the database.";
	echo "Probably something wrong with the SQL query.";
	echo "\n".$e->getMessage();
	exit();
}
foreach($root['company'] as $acme) {
	echo "Looked for Acme and found company with name = " . $acme->name . " and id " . $acme->id . "\n";
}
$shoe = $acme['department'][0];
	echo "Looked for Shoe department and found department with name = " . $shoe->name . " and id " . $shoe->id . "\n";

$shoe->name = 'Footwear';
$it = $acme->createDataObject('department');
$it->name = 'IT';
echo "Changed a department and added one and wrote both back - should now have two departments\n";

/**************************************************************
 * Get a database connection
 ***************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);

/**************************************************************
 * Write the changes out
 ***************************************************************/
try {
    $das -> applyChanges($dbh, $root);
    echo "\nCompany Acme has been written to the database";

} catch (SDO_DAS_Relational_Exception $e) {
    echo "\nSDO_DAS_Relational_Exception raised when trying to apply changes.";
    echo "\nProbably something wrong with the data graph.";
    echo "\n".$e->getMessage();
    exit();
}


?>
