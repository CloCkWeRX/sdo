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
 * Scenario - Use one company, department, employee to test the handling of null
 * for non-containment reference employee_of_the_month.
 * Set it to a value and write back,
 * then to a null, write back, then back to a value, then to a null
 * Test each time. 
 *
 */

/*************************************************************************************
* Empty out the three tables
*************************************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);
$count = $dbh->exec('DELETE FROM company');
$count = $dbh->exec('DELETE FROM department');
$count = $dbh->exec('DELETE FROM employee');


/**************************************************************
* Create company with name Acme, one department, Shoe, one employee
***************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);

$root = $das -> createRootDataObject();

$acme = $root -> createDataObject('company');
$acme -> name = "Acme";

$shoe = $acme->createDataObject('department');
$shoe->name = 'Shoe';

$sue = $shoe->createDataObject('employee');
$sue->name = "Sue";

$acme->employee_of_the_month = $sue;

$das -> applyChanges($dbh, $root);
echo "\nCompany Acme has been written to the database\n";

/**************************************************************
* Retrieve the company and check sue is e.o.t.m, then null it
* We are not injecting anything into the SQL statement so safe to use the simple executeQuery
***************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);

$root = $das->executeQuery($dbh,
	'select c.id, c.name, c.employee_of_the_month, d.id, d.name, e.id, e.name'
	. ' from company c, department d, employee e '
	. ' where e.dept_id = d.id and d.co_id = c.id',
	array('company.id','company.name','company.employee_of_the_month',
	'department.id','department.name','employee.id','employee.name'));

$acme = $root['company'][0];
echo "Looked for Acme and found company with name = " . $acme->name . " and id " . $acme->id . "\n";
$shoe = $acme['department'][0];
echo "Looked for Shoe department and found department with name = " . $shoe->name . " and id " . $shoe->id . "\n";
$sue = $shoe['employee'][0];
echo "Looked for Employee Sue and found employee with name = " . $sue->name . " and id " . $sue->id . "\n";

// keep sue but we no longer have an employee of the month
$acme->employee_of_the_month = null;

$das -> applyChanges($dbh, $root);
echo "Wrote back company with null for employee of the month\n";

/**************************************************************
* Retrieve the company and check the employee of the month is null; then set it back to Sue again
* We are not injecting anything into the SQL statement so safe to use the simple executeQuery
***************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);

$root = $das->executeQuery($dbh,
	'select c.id, c.name, c.employee_of_the_month, d.id, d.name, e.id, e.name'
	. ' from company c, department d, employee e '
	. ' where e.dept_id = d.id and d.co_id = c.id',
	array('company.id','company.name','company.employee_of_the_month',
	'department.id','department.name','employee.id','employee.name'));

$acme = $root['company'][0];
echo "Looked for Acme and found company with name = " . $acme->name . " and id " . $acme->id . "\n";
$shoe = $acme['department'][0];
echo "Looked for Shoe department and found department with name = " . $shoe->name . " and id " . $shoe->id . "\n";
$sue = $shoe['employee'][0];
echo "Looked for Employee Sue and found employee with name = " . $sue->name . " and id " . $sue->id . "\n";

assert($acme->employee_of_the_month === null);
$acme->employee_of_the_month = $sue;

$das -> applyChanges($dbh, $root);

/**************************************************************
* Retrieve the company and check the eotm is sue
* We are not injecting anything into the SQL statement so safe to use the simple executeQuery
***************************************************************/
$dbh = new PDO(PDO_DSN,DATABASE_USER,DATABASE_PASSWORD);
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);

$root = $das->executeQuery($dbh,
	'select c.id, c.name, c.employee_of_the_month, d.id, d.name, e.id, e.name'
	. ' from company c, department d, employee e '
	. ' where e.dept_id = d.id and d.co_id = c.id',
	array('company.id','company.name','company.employee_of_the_month',
	'department.id','department.name','employee.id','employee.name'));

$acme = $root['company'][0];
echo "Looked for Acme and found company with name = " . $acme->name . " and id " . $acme->id . "\n";
$sue = $acme->employee_of_the_month;
assert($sue->name == 'Sue');

?>
