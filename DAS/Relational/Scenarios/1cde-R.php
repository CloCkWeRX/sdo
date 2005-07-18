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

/**************************************************************
 * Scenario - Retrieve and update all company, department, employee records
 ***************************************************************/

/**************************************************************
 * Get a Data Access Service
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
 * Issue a query - get all companies in the database
 ***************************************************************/
$dbh = new PDO("mysql:dbname=COMPANYDB;host=localhost",DATABASE_USER,DATABASE_PASSWORD);

try {
	$root = $das->executeQuery($dbh,
	'select c.id, c.name, d.id, d.name, e.id, e.name from company c, department d, employee e where e.dept_id = d.id and d.co_id = c.id',
	array('company.id','company.name','department.id','department.name','employee.id','employee.name'));
} catch (SDO_DAS_Relational_Exception $e) {
	echo "SDO_DAS_Relational_Exception raised when trying to retrieve data from the database.";
	echo "Probably something wrong with the SQL query.";
	echo "\n".$e->getMessage();
	exit();
}

/**************************************************************
 * List what we found
 ***************************************************************/
echo "Names in the database look as follows:\n";
foreach($root['company'] as $company) {
	echo $company->name . "\n";
	foreach($company['department'] as $department) {
		echo "  " . $department->name . "\n";
		foreach ($department['employee'] as $employee) {
			echo "    " . $employee->name . "\n";
		}
	}
}

?>
