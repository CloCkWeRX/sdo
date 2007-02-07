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
?>

<html>

<head>
    <title>MyContacts Edit</title>
</head>
<body BGCOLOR="#EFEFEF">

<?php
require('./contacts_config.inc.php');

require('./contacts_data.inc.php');

if (!ctype_alnum($_POST['shortname'])) {
	echo '<p><b>Invalid shortname provided.</b></p>';
	echo '<form action="http://' . APP_ROOT . '/welcome.php" method="POST">';
	echo '<input type="submit" name="Return" value="Return"/>';
	echo '</form>';
	return;
}

$root = retrieve_contact($_POST['shortname']);
$contact = $root->contact[0];
if (! $contact) {
	echo '<p><b>Unknown shortname provided.</b></p>';
	echo '<form action="http://' . APP_ROOT . '/welcome.php" method="POST">';
	echo '<input type="submit" name="Return" value="Return"/>';
	echo '</form>';
	return;
}
$address = $contact->address[0];

echo '<form action="http://' . APP_ROOT . '/confirm.php" method="POST">';

/*
* ensure each browser tab has its own sdo serialized in the session data
* The time is sufficiently granular to use for this example, but not for 
* real code!
*/
$handle = 'sdo-contact-'.$_SERVER['REQUEST_TIME'];
$_SESSION[$handle] = $root;

echo <<<DOC
 <input type="hidden" name="sdo-root" value="$handle"/>
 <p><b>Contact Edit</b></p>
 <table cellspacing=3 cellpadding=3 >
  <tr>
  	<td><b>Contact details</b><td/><td/>
  <tr>
    <td>Shortname:</td>
    <td>$contact->shortname</td>
  </tr>
  <tr>
    <td>Fullname:</td>
    <td><input type="text" name="fullname" value="$contact->fullname"/></td>
  </tr>
  <tr>
  	<td><b>Address details</b><td/><td/>
  <tr>
  <tr>
    <td>Address Line1:</td>
    <td><input type="text" name="addressline1" value="$address->addressline1"/></td>
  </tr>
  <tr>
    <td>Address Line2:</td>
    <td><input type="text" name="addressline2" value="$address->addressline2"/></td>
  </tr>
  <tr>
    <td>City:</td>
    <td><input type="text" name="city" value="$address->city"/></td>
  </tr>
  <tr>
    <td>State:</td>
    <td><input type="text" name="state" value="$address->state"/></td>
  </tr>
  <tr>
    <td>Zip:</td>
    <td><input type="text" name="zip" value="$address->zip"/></td>
  </tr>
  <tr>
    <td>Telephone:</td>
    <td><input type="text" name="telephone" value="$address->telephone"/></td>
  </tr>
</table>    

DOC;
?>

    <input type="submit" name="Update" value="Update"/>
 </form>

<form action="http://<?php  echo APP_ROOT . '/welcome.php' ?>" method="POST">
    <input type="submit" name="Cancel" value="Cancel"/>
</form>
 
</body>
</html>