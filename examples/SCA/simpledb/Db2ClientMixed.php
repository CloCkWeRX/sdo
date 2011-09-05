<?php
/*
+----------------------------------------------------------------------+
| Copyright IBM Corporation 2007.                                      |
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

require_once 'SCA/SCA.php';

$dbservice = SCA::getService('CHARTERS.CONTACT', 'simpledb',
                 array('config' => 'config/db2_config.ini',
                       'case' => 'mixed'));

$contact = $dbservice->createDataObject('http://example.org', 'Contact');
$contact->Shortname = 'bertie';
$contact->Fullname = 'Bertie Bassett';
$contact->Email = 'bertie@bassets.com';
$id = $dbservice->create($contact);
echo "Created: $id";

$contact = $dbservice->retrieve('bertie');
echo "Retrieved: " . $contact->Fullname;

$contact->Fullname = 'Bertie B Bassett';
$dbservice->update($contact->Shortname, $contact);

$dbservice->delete($contact->Shortname);

?>