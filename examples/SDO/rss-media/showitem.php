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
<title>Show Item</title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<p><strong>My half-baked feed</strong><br />
<?php
	$xmldas = SDO_DAS_XML::create('./blog.xsd');
	$xmldoc = $xmldas->loadFile('./blog.xml');
	$blog = $xmldoc->getRootDataObject();
	$id =  $_GET['id'];
	$item = $blog["item[guid=$id]"];

   echo "<br/>";
   echo "The following item was added on " . $item->date;
   echo " from ip address: " . $item->from_ip;

   echo "<br/>";
   echo "<br/>";
   echo "Title: " . $item->title;
   echo "<br/>";
   echo "Description: " . $item->description;
   echo "<br/>";
   if (isset($item->enclosure)) {
   	$filename_as_url = str_replace(array(' '), array('%20'),$item->enclosure);
   	$link =  "./media/" . $filename_as_url;
   	echo "Related link: " . "<a href=" . $link . ">mov</a>" 		;
   }
   
?>
</p>
</body>
</html>
