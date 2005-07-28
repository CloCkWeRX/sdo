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

echo "executing scenario one-company-delete-and-recreate-with-same-PK\n";


require_once 'SDO/DAS/Relational.php';
require_once 'company_metadata.inc.php';

/**
 * Scenario - Create one company 
 *
 * Create one company row in the company table.
 *
 * create table company (
 *   id integer auto_increment,
 *   name char(20),
 *   employee_of_the_month integer,
 *   primary key(id) ); 
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

$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);
$root = $das->executeQuery($dbh,
'select name, id from company where name="Acme"',array('company.name', 'company.id') );

$company = $root['company'][0];

$name = $company->name;
$id = $company->id;

unset($root['company']);

$new_company = $root -> createDataObject('company');

$new_company->name = $name;
$new_company->id = $id;

/**************************************************************
 * Get a PDO database connection
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
