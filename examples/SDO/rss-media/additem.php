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
| Author: Matthew Peters, Graham Charters                              |
+----------------------------------------------------------------------+
$Id$
-->
<head>
<title>Add an item</title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<p><strong>Added the following item</strong><br />
<?php

/**
 * Process the file upload
 */
$was_uploaded = false;
include 'handle_upload.inc.php';
$was_uploaded = handle_upload($_FILES['media']['tmp_name']);

if ($_POST['title'] === null) {
	echo "The title field was empty - some kind of upload failure - resubmit the form";
} else {
	$to = './media/' . $_FILES['media']['name'];
	
	/**
	 * Load the existing blog entries
	 */
	$xmldas = SDO_DAS_XML::create('./blog.xsd');
	$xmldoc = $xmldas->loadFile('./blog.xml');
	$blog = $xmldoc->getRootDataObject();
	
	/**
	 * Add a new blog entry
	 */
	$new_item = $blog->createDataObject('item');
	$new_item->title = $_POST['title'];
	$new_item->description = $_POST['description'];
	$new_item->date = date("D\, j M Y G:i:s T");
	$new_item->guid = md5($new_item->date);
	$new_item->from_ip = $_SERVER['REMOTE_ADDR'];

	/**
	 * Add the enclosure tag if a file was uploaded.
	 */
	if ($was_uploaded) {
		$new_item->enclosure = $_FILES['media']['name'];
	}

	/**
	 * Save the updated blog file
	 */
	$xmldas->saveFile($xmldoc,'./blog.xml',2);

	/**
	 * Inform the blogger of the results
	 */
	echo "Title: " . $new_item->title;
	echo "<br/>";
	echo "Description: " . $new_item->description;
	if ($was_uploaded) {
		echo "<br/>";
		echo "Uploaded: " . basename($to);
	}
	echo "<br/>";
	echo "Date: " . $new_item->date;

}

?>
</p>
</body>
</html>
