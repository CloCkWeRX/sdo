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
 * Scenario - Retrieve and update multiple companies
 *
 * Retrieve multiple company rows from the company table. 
 */

/**************************************************************
* GET AND INITIALISE A DAS WITH THE METADATA
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
* GET A DATABASE CONNECTION
***************************************************************/
$dbh = new PDO("mysql:dbname=COMPANYDB;host=localhost",DATABASE_USER,DATABASE_PASSWORD);

/**************************************************************
* ISSUE A QUERY TO OBTAIN A COMPANY OBJECT
***************************************************************/

try {
	$root = $das->executeQuery($dbh, 'select name, id from company' ,array('company.name','company.id'));
	$companies = $root['company'];
} catch (SDO_DAS_Relational_Exception $e) {
	echo "SDO_DAS_Relational_Exception raised when trying to retrieve data from the database.";
	echo "Probably something wrong with the SQL query.";
	echo "\n".$e->getMessage();
	exit();
}

foreach($companies as $company) {
	echo "got " . $company->name . "\n";
	$company->name = strrev($company->name);
}

$dbh = new PDO("mysql:dbname=COMPANYDB;host=localhost",DATABASE_USER,DATABASE_PASSWORD);
try {
	$das -> applyChanges($dbh, $root);
} catch (SDO_DAS_Relational_Exception $e) {
	echo "\nSDO_DAS_Relational_Exception raised when trying to apply changes.";
	echo "\nProbably something wrong with the data graph.";
	echo "\n".$e->getMessage();
	exit();
}

?>
