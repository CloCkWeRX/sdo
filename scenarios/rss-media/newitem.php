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
<title>My half-baked blog - new item</title>
</head>

<body>
<p>
<strong>My half-baked blog - add a new item</strong>
<br/>
<br/>

<form action="additem.php" enctype="multipart/form-data" method="post">
  Title:
  <br/>
  <input type="text" size="50" name="title"/>
  <br/>
  Description:
  <br/>
  <textarea rows="5" cols="50" name="description"></textarea>
  <br/>
  File to upload:
  <br/>
  <input type="hidden" name="MAX_FILE_SIZE" value="6000000" />
  <input type="file" size="50" name="media"\>
  <br/>
  <br/>
  <input value="Submit" type="submit"/>
</form>
</p>
</body>
</html>
