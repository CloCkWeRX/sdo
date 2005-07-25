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

/*************************************************************************************
* Empty out the two tables
*************************************************************************************/
$dbh = new PDO("mysql:dbname=companydb;host=localhost",DATABASE_USER,DATABASE_PASSWORD);
$count = $dbh->exec('DELETE FROM company');
$count = $dbh->exec('DELETE FROM department');


/**************************************************************
* Create company with name Acme and one department, Shoe
***************************************************************/
$dbh = new PDO("mysql:dbname=companydb;host=localhost",DATABASE_USER,DATABASE_PASSWORD);
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);

$root = $das -> createRootDataObject();

$acme = $root -> createDataObject('company');
$acme -> name = "Acme";

$shoe = $acme->createDataObject('department');
$shoe->name = 'Shoe';

$das -> applyChanges($dbh, $root);
echo "\nCompany Acme has been written to the database\n";

/**************************************************************
* Retrieve the company and Shoe department, then delete Shoe and add IT
***************************************************************/
$dbh = new PDO("mysql:dbname=companydb;host=localhost",DATABASE_USER,DATABASE_PASSWORD);
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);

$root = $das->executeQuery($dbh,
'select c.id, c.name, d.id, d.name from company c, department d where d.co_id = c.id',
array('company.id','company.name','department.id','department.name'));

$acme = $root['company'][0];
echo "Looked for Acme and found company with name = " . $acme->name . " and id " . $acme->id . "\n";
$shoe = $acme['department'][0];
echo "Looked for Shoe department and found department with name = " . $shoe->name . " and id " . $shoe->id . "\n";

unset($acme['department'][0]);

$it = $acme->createDataObject('department');
$it->name = 'IT';
$das -> applyChanges($dbh, $root);
echo "Deleted a department and added one\n";

/**************************************************************
* Retrieve the company and IT department, then delete the whole company
***************************************************************/
$dbh = new PDO("mysql:dbname=companydb;host=localhost",DATABASE_USER,DATABASE_PASSWORD);
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);

$root = $das->executeQuery($dbh,
'select c.id, c.name, d.id, d.name from company c, department d where d.co_id = c.id',
array('company.id','company.name','department.id','department.name'));

$acme = $root['company'][0];
$it = $acme['department'][0];
echo "Looked for IT department and found department with name = " . $it->name . " and id " . $it->id . "\n";

unset($root['company'][0]);

$das -> applyChanges($dbh, $root);
echo "Deleted Company and IT department\n";


?>
