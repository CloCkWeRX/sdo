<html>
<!--
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
-->

<title>Demo of SDO APIs</title>
<body>

<?php

/**
 * Load the contact from either database or XML
 */
$contact = null;
switch ($_GET['type']) {
	/**
 	* Retrieve a contact from the database
 	*/
	case 'database':
		echo '<b>Retrieving from database</b><br/><br/>';
		require_once '../contacts/contacts_data.inc.php';
		$root = retrieve_contact('shifty');
		$contact = $root->contact[0];
		break;

	/**
 	* Load the contact from XML
 	*/
	case 'XML':
		echo '<b>Retrieving from XML</b><br/><br/>';
		$xmldas = SDO_DAS_XML::create('contacts.xsd');
		$doc = $xmldas->loadFile('contacts.xml');
		$contact = $doc->getRootDataObject();
		break;
	default:
		echo '<b>Invalid data source type (use database or XML)</b><br/><br/>';
		return false;
		break;

}


/**
 * Create a new address child data object
 * Note: data objects are factories for their children
 */
$address = $contact->createDataObject('address');

echo '<b>Set and Get output</b><br/>';

/**
 * Object syntax for setting and getting
 */
$address->addressline1 = '2 Work Road';
$address->addressline2 = 'My Business';
echo $address->addressline1 . '<br/>';
echo $address->addressline2 . '<br/>';

/**
 * Array syntax for setting and getting
 */
$address['city'] = 'Big City';
$address['state'] = 'Small State';
echo $address['city'] . '<br/>';
echo $address['state'] . '<br/>';

/**
 * The address is already part of the contact
 */
$contact->address[1]->zip = "CA 23423";
echo $contact->address[1]->zip . '<br/>';

/**
 * Iteration over properties
 */
echo '<br/><b>Iteration output</b><br/>';
foreach ($address as $name => $value) {
	echo "$name : $value <br/>";
}

/**
 * Unsetting properties
 */
unset($address->addressline2);
unset($contact->address[1]->city);
echo '<br/><b>Iteration output</b><br/>';
foreach ($address as $name => $value) {
	echo "$name : $value <br/>";
}

/**
 * Print or var_dump of a data object
 */
echo '<br/><b>Print/var_dump output</b><br/>';
print "$address <br/>";

/**
 * Access via XPath - point out the restrictions
 */
echo '<br/><b>The address via XPath</b><br/>';
print $contact["address[state='Small State']"] . '<br/>';


?>
</body>
</html>
