<?php
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                  |
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
| Author: Graham Charters                                              |
+----------------------------------------------------------------------+
$Id$
*/

function retrieve_contact($shortname) {

	require_once '../contacts/contacts_schema.inc.php';
	require_once '../contacts/db_config.inc.php';
	require_once 'SDO/DAS/Relational.php';

	$dbh = new PDO(PDO_DSN, DATABASE_USER, DATABASE_PASSWORD);
	$das = new SDO_DAS_Relational ($table_schema, 'contact', array($address_reference));

	$pdo_stmt = $dbh->prepare('select c.shortname, c.fullname, a.id, a.addressline1, a.addressline2, ' .
	' a.city, a.state, a.zip, a.telephone from contact c, address a ' .
	'where a.contact_id = c.shortname and c.shortname=?');

	return $das->executePreparedQuery($dbh, $pdo_stmt, array($shortname));

}

function update_contact($root) {

	require_once './contacts_schema.inc.php';
	require_once './db_config.inc.php';
	require_once 'SDO/DAS/Relational.php';

	$dbh = new PDO(PDO_DSN, DATABASE_USER, DATABASE_PASSWORD);
	$das = new SDO_DAS_Relational ($table_schema, 'contact', array($address_reference));

	$das->applyChanges($dbh, $root);

}
?>