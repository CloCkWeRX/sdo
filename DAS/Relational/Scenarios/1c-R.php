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

echo "executing scenario one-company-retrieve\n";


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
* Get a database connection
***************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);

/**************************************************************
* Issue a query to obtain a company data object
***************************************************************/

try {
echo "About to execute *******";
	$root = $das->executeQuery($dbh,
			"select name, id from company where name = 'Acme'",
// MySql is happy with the following query formation, DB2 is not
//			'select name, id from company where name = "Acme"', 
			array('company.name', 'company.id') );			
} catch (SDO_DAS_Relational_Exception $e) {
	echo "SDO_DAS_Relational_Exception raised when trying to retrieve data from the database.";
	echo "Probably something wrong with the SQL query.";
	echo "\n".$e->getMessage();
	exit();

}

foreach ($root['company'] as $company) {
	echo "Company obtained from the database has name = " . $company['name'] . " and id " . $company['id'] . "\n";
}

?>
