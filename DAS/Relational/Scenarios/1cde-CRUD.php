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

/*************************************************************************************
* Use SDO to perform create, retrieve and update operations on an entire company.
* The SDO will contain company, department, and employee objects in one graph.
*
* The three tables are defined like this to MySQL:
* create table company (
*   id integer auto_increment,
*   name char(20),
*   employee_of_the_month integer,
*   primary key(id)
* );
* create table department (
*   id integer auto_increment,
*   name char(20),
*   location char(10),
*   number integer(3),
*   co_id integer,
*   primary key(id)
* );
* create table employee (
*   id integer auto_increment,
*   name char(20),
*   SN char(4),
*   manager tinyint(1),
*   dept_id integer,
*   primary key(id)
* );
*************************************************************************************/

/*************************************************************************************
* Empty out the three tables
*************************************************************************************/
$dbh = new PDO("mysql:dbname=companydb;host=localhost",DATABASE_USER,DATABASE_PASSWORD);
$count = $dbh->exec("DELETE FROM company");
$count = $dbh->exec("DELETE FROM department");
$count = $dbh->exec("DELETE FROM employee");

/*************************************************************************************
* Create a tiny but complete company.
* The company name is Acme.
* There is one department, Shoe.
* There is one employee, Sue.
* The employee of the month is Sue.
*************************************************************************************/
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);
$dbh = new PDO("mysql:dbname=companydb;host=localhost",DATABASE_USER,DATABASE_PASSWORD);

$root 			= $das  -> createRootDataObject();
$acme 			= $root -> createDataObject('company');
$acme -> name 	= "Acme";
$shoe 			= $acme -> createDataObject('department');
$shoe -> name 	= 'Shoe';
$shoe -> location = 'A-block';
$sue 			= $shoe -> createDataObject('employee');
$sue -> name 	= 'Sue';
$acme -> employee_of_the_month = $sue;

$das -> applyChanges($dbh, $root);

echo "Wrote back Acme with one department and one employee\n";

/*************************************************************************************
* Find the company again and change various aspects.
* Change the name of the company, department and employee.
* Add a second department and a new employee.
* Change the employee of the month.
*************************************************************************************/
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);
$dbh = new PDO("mysql:dbname=companydb;host=localhost",DATABASE_USER,DATABASE_PASSWORD);

$name = 'Acme';
$root = $das->executeQuery($dbh,
'select c.id, c.name, c.employee_of_the_month, d.id, d.name, e.id, e.name from company c, department d, employee e where e.dept_id = d.id and d.co_id = c.id and c.name="' . $name . '";' ,
array('company.id','company.name','company.employee_of_the_month','department.id','department.name','employee.id','employee.name'));
$acme 	= $root['company'][0];

$shoe	= $acme->department[0];
$sue	= $shoe -> employee[0];

$it 	= $acme->createDataObject('department');
$it->name = 'IT';
$it->location = 'G-block';
$billy 	= $it->createDataObject('employee');
$billy->name = 'Billy';

$acme->name = 'MegaCorp';
$shoe->name = 'Footwear';
$sue->name = 'Susan';

$acme->employee_of_the_month = $billy;
$das -> applyChanges($dbh, $root);
echo "Wrote back company with extra department and employee and all the names changed (Megacorp/Footwear/Susan)\n";

/*************************************************************************************
* Find it again under its new name and check names and e.o.t.m are right
*************************************************************************************/
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);
$dbh = new PDO("mysql:dbname=companydb;host=localhost",DATABASE_USER,DATABASE_PASSWORD);

$name = 'MegaCorp';
$root = $das->executeQuery($dbh,
'select c.id, c.name, c.employee_of_the_month, d.id, d.name, e.id, e.name from company c, department d, employee e where e.dept_id = d.id and d.co_id = c.id and c.name="' . $name . '";' ,
array('company.id','company.name','company.employee_of_the_month','department.id','department.name','employee.id','employee.name'));
$megacorp 	= $root['company'][0];
$footwear 	= $megacorp->department[0];
$it 		= $megacorp->department[1];
$susan 		= $footwear->employee[0];
$billy 		= $it->employee[0];
assert	($megacorp->name == 'MegaCorp');
assert	($footwear->name == 'Footwear');
assert	($it->name == 'IT');
assert	($susan->name == 'Susan');
assert	($billy->name == 'Billy');
assert	($megacorp->employee_of_the_month['name'] == 'Billy');

/*************************************************************************************
* Now read it one more time and delete it.
* You can delete part, apply the changes, then carry on working with the same graph but
* care is needed to keep closure - you cannot delete the employee who is eotm without
* reassigning. For safety here we delete the company all in one go. 
*************************************************************************************/
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);
$dbh = new PDO("mysql:dbname=companydb;host=localhost",DATABASE_USER,DATABASE_PASSWORD);

$name='MegaCorp';
$root = $das->executeQuery($dbh,
	'select c.id, c.name, c.employee_of_the_month, d.id, d.name, e.id, e.name from company c, department d, employee e where e.dept_id = d.id and d.co_id = c.id and c.name="' . $name . '";' ,
	array('company.id','company.name','company.employee_of_the_month','department.id','department.name','employee.id','employee.name'));
$megacorp = $root['company'][0];

unset($root['company']);
$das -> applyChanges($dbh, $root);

echo "Deleted the company, departments and employees all in one go.\n";

?>
