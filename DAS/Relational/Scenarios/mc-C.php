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
 * Scenario - Create multiple companies
 *
 * Create multiple company rows in the company table.
 *
 * create table company (
 *   id integer auto_increment,
 *   name char(20),
 *   employee_of_the_month integer,
 *   primary key(id) ); 
 */

$dbh = new PDO("mysql:dbname=COMPANYDB;host=localhost",DATABASE_USER,DATABASE_PASSWORD);
$pdo_stmt = $dbh->prepare('DELETE FROM COMPANY;');
$rows_affected = $pdo_stmt->execute();

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
 * CREATE THREE COMPANY OBJECTS UNDER THE SAME ROOT
 ***************************************************************/

$root = $das->createRootDataObject();
$acme = $root -> createDataObject('company');
$acme -> name = "Acme";

$megacorp = $root->createDataObject('company');
$megacorp->name = 'MegaCorp';

$ultracorp = $root->createDataObject('company');
$ultracorp->name = 'UltraCorp';

$dbh = new PDO("mysql:dbname=COMPANYDB;host=localhost",DATABASE_USER,DATABASE_PASSWORD);

try {
    $das -> applyChanges($dbh, $acme);
    echo "Companies Acme, MegaCorp and UltraCorp have been written to the database\n";

} catch (SDO_DAS_Relational_Exception $e) {
    echo "\nSDO_DAS_Relational_Exception raised when trying to apply changes.";
    echo "\nProbably something wrong with the data graph.";
    echo "\n".$e->getMessage();
    exit();
}


?>
