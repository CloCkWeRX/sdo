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
<head>
    <title>MyContacts Welcome</title>
</head>
<body BGCOLOR="#EFEFEF">

<?php
require('./contacts_config.inc.php');
?>

 <form action="http://<?php echo APP_ROOT . '/edit.php' ?>" method="POST">
 <b>Contact Main Page</b><br/>
    Shortname:
    <input type="text" name="shortname" value=""/>
    <input type="submit" name="Edit" value="Edit"/>
 </form>

</body>
</html>