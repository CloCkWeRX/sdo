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
* The company table is defined like this to MySQL:
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
$count = $dbh->exec('DELETE FROM company');
$count = $dbh->exec('DELETE FROM department');
$count = $dbh->exec('DELETE FROM employee');


/*************************************************************************************
* Create the root data object then a tiny but complete company.
* The company name is Acme.
* There are two departments, Shoe and IT.
* There are two employees, Sue and Billy.
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
$it 			= $acme -> createDataObject('department');
$it -> name 	= 'IT';
$it -> location = 'G-block';
$sue 			= $shoe -> createDataObject('employee');
$sue -> name 	= 'Sue';
$billy 			= $it   -> createDataObject('employee');
$billy -> name 	= 'Billy';
$acme -> employee_of_the_month = $sue;
$das -> applyChanges($dbh, $root);

echo "Wrote back company with name Acme, two departments, and two employees\n";

/*************************************************************************************
* Find the company again and change various aspects.
* Swap sue and billy by detaching from the graph and reinserting
* Then write it back again.
*************************************************************************************/
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);
$dbh = new PDO("mysql:dbname=companydb;host=localhost",DATABASE_USER,DATABASE_PASSWORD);
$name = 'Acme';
$root = $das->executeQuery($dbh,
'select c.id, c.name, d.id, d.name, e.id, e.name from company c, department d, employee e where e.dept_id = d.id and d.co_id = c.id and c.name="' . $name . '";' ,
array('company.id','company.name','department.id','department.name','employee.id','employee.name'));
$acme2 = $root['company'][0];
$shoe = $acme2['department'][0];
$it = $acme2['department'][1];
$sue = $shoe['employee'][0];
$billy = $it['employee'][0];
echo "Looked for Acme and found company with name = $acme2->name and id $acme2->id\n";
echo "First department had name = " . $acme2->department[0]->name . "\n";
echo "First employee had name = " . $acme2->department[0]->employee[0]->name . "\n";
echo "Second department had name = " . $acme2->department[1]->name . "\n";
echo "Second employee had name = " . $acme2->department[1]->employee[0]->name . "\n";

// Swap Sue and Billy
// Since we have sue and billy safely in variables, remove them from their departments
unset($shoe['employee'][0]);
unset($it['employee'][0]);

// reinsert: two ways to do it
$shoe['employee']->insert($billy);
$it['employee'][] = $sue;

$das -> applyChanges($dbh, $root);

/*************************************************************************************
* Find the company again and check that they are swapped
*************************************************************************************/
$das = new SDO_DAS_Relational ($database_metadata,'company',$SDO_reference_metadata);
$dbh = new PDO("mysql:dbname=companydb;host=localhost",DATABASE_USER,DATABASE_PASSWORD);
$name = 'Acme';
$root = $das->executeQuery($dbh,
'select c.id, c.name, d.id, d.name, e.id, e.name from company c, department d, employee e where e.dept_id = d.id and d.co_id = c.id and c.name="' . $name . '";' ,
array('company.id','company.name','department.id','department.name','employee.id','employee.name'));
var_dump($root);
$acme3 = $root['company'][0];
//$shoe = $acme3['department'][0];
//$it = $acme3['department'][1];
//$sue = $shoe['employee'][0];
//$billy = $it['employee'][0];
echo "Looked for Acme and found company with name = $acme3->name and id $acme3->id\n";
echo "First department had name = " . $acme3->department[0]->name . "\n";
echo "First employee had name = " . $acme3->department[0]->employee[0]->name . "\n";
echo "Second department had name = " . $acme3->department[1]->name . "\n";
echo "Second employee had name = " . $acme3->department[1]->employee[0]->name . "\n";




?>
