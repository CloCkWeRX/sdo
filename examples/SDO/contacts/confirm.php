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
| Author: Graham Charters, Caroline Maynard                            |
+----------------------------------------------------------------------+
$Id$
*/

session_start();

require('./contacts_config.inc.php');

$handle = $_POST['sdo-root'];
$root = $_SESSION[$handle];
unset($_SESSION[$handle]);
$contact = $root->contact[0];

if (! $contact) {
	header('Location: http://' . APP_ROOT . '/welcome.php');
	return;
}

if (isset($_POST['fullname']) && ($contact['fullname'] != $_POST['fullname']))
$contact->fullname = $_POST['fullname'];

$address = $contact->address[0];

$reflection = new SDO_Model_ReflectionDataObject($address);
$properties = $reflection->getType()->getProperties();

foreach ($properties as $property) {
	$name = $property->getName();
	if (isset($_POST[$name]) && ($address[$name] != $_POST[$name])) {
		$address[$name] = $_POST[$name];
	}
}

require('./contacts_data.inc.php');
?>
<html>
 
<head>
    <title>MyContacts</title>
</head>
<body BGCOLOR="#EFEFEF">
 
<?php
try {
	update_contact($root);
	echo '<b>Contact Update Confirmation</b><br/>';
	echo "Successfully updated: $contact->shortname";
}
catch (SDO_DAS_Relational_Exception $e) {
	echo "<b>Contact Update Failed: </b>" . $e->getMessage() . "<br/>";
}

?>
 
<form action="http://<?php  echo APP_ROOT . '/welcome.php' ?>" method="POST">
    <input type="submit" name="Home" value="Home"/>
</form>

 
</body>
</html>