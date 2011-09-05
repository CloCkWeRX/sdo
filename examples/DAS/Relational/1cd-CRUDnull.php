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
require_once 'SDO/DAS/Relational.php';
require_once 'company_metadata.inc.php';

/**
 * Scenario - Use one company and department to test the handling of null
 * Set the location of the shoe department to a string and write back,
 * then to a null, write back, then back to a string, then to a null
 * Test each time.
 *
 */

/*************************************************************************************
* Empty out the two tables
*************************************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);
$count = $dbh->exec('DELETE FROM company');
$count = $dbh->exec('DELETE FROM department');


/**************************************************************
* Create company with name Acme and one department, Shoe
***************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);

$root = $das -> createRootDataObject();

$acme = $root -> createDataObject('company');
$acme -> name = "Acme";

$shoe = $acme->createDataObject('department');
$shoe->name = 'Shoe';
$shoe->location = null;

$das -> applyChanges($dbh, $root);
echo "\nCompany Acme has been written to the database\n";

/**************************************************************
* Retrieve the company and Shoe department, specifically the location column
* We are not injecting anything into the SQL statement so safe to use the simple executeQuery
***************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);

$root = $das->executeQuery($dbh,
'select c.id, c.name, d.id, d.name, d.location from company c, department d where d.co_id = c.id',
array('company.id','company.name','department.id','department.name','department.location'));

$acme = $root['company'][0];
echo "Looked for Acme and found company with name = " . $acme->name . " and id " . $acme->id . "\n";
$shoe = $acme['department'][0];
echo "Looked for Shoe department and found department with name = " . $shoe->name . " and id " . $shoe->id . "\n";
echo "location is ";
var_dump($shoe->location);
assert($shoe->location === null);

$shoe->location = 'Top floor';

$das -> applyChanges($dbh, $root);
echo "Wrote back company with changed location for shoe department\n";

/**************************************************************
* Retrieve the company and check the location of the shoe department; further set it back to null again
* We are not injecting anything into the SQL statement so safe to use the simple executeQuery
***************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);

$root = $das->executeQuery($dbh,
'select c.id, c.name, d.id, d.name, d.location from company c, department d where d.co_id = c.id',
array('company.id','company.name','department.id','department.name','department.location'));

$acme = $root['company'][0];
$shoe = $acme['department'][0];
assert($shoe->location == 'Top floor');
$shoe->location = null;

$das -> applyChanges($dbh, $root);

/**************************************************************
* Retrieve the company and check the location of the shoe department is back to null
* We are not injecting anything into the SQL statement so safe to use the simple executeQuery
***************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);

$root = $das->executeQuery($dbh,
'select c.id, c.name, d.id, d.name, d.location from company c, department d where d.co_id = c.id',
array('company.id','company.name','department.id','department.name','department.location'));

$acme = $root['company'][0];
$shoe = $acme['department'][0];
assert($shoe->location === null);


?>
