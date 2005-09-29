<?
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
$Id$
*/

// A quick bodge to handle how PDO constants are defined
// This file gets included if they are are old-style PDO_xxx
define('SDO_DAS_Relational_PDO_FETCH_ASSOC', PDO_FETCH_ASSOC);
define('SDO_DAS_Relational_PDO_FETCH_NUM', PDO_FETCH_NUM);
define('SDO_DAS_Relational_PDO_ATTR_CLIENT_VERSION', PDO_ATTR_CLIENT_VERSION);
?>
 