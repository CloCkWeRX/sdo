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
 * Scenario - Retrieve one company 
 *
 * Retrieve one company row in the company table. 
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
 * Get a PDO database connection
 ***************************************************************/
$dbh = new PDO("mysql:dbname=companydb;host=localhost",DATABASE_USER,DATABASE_PASSWORD);

/**************************************************************
 * Issue a query to obtain the company data object
 ***************************************************************/

try {
	$root = $das->executeQuery($dbh,
			'select name, id from company where name="Acme"',
			array('company.name', 'company.id') );			
} catch (SDO_DAS_Relational_Exception $e) {
	echo "SDO_DAS_Relational_Exception raised when trying to retrieve data from the database.";
	echo "Probably something wrong with the SQL query.";
	echo "\n".$e->getMessage();
	exit();

}

$acme = $root['company'][0];
echo "obtained a company with name of Acme\n";

$acme->name = "MegaCorp";
$acme->name = "Acme";
$das->applyChanges($dbh,$root);

?>
