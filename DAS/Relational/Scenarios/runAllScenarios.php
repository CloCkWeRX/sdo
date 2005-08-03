<?php
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

/**
 * run all the scenarios
 */ 
require_once 'company_metadata.inc.php';

/* one-table, one-company scenarios */
require_once '1c-CRUD.php';
require_once '1c-C.php';
require_once '1c-R.php';
require_once '1c-RA.php';
require '1c-R.php';
require '1c-RD.php';
require_once '1c-R.php';
require '1c-C.php';
require '1c-DCsamePK.php'; // delete and create another with same PK
require '1c-C.php';
require '1c-RUnull.php'; // change name but change it back so new == old => no update
require '1c-C.php';
require '1c-RUunsetPrimitive.php'; // unset of a primitive actually does nothing but at least check it breaks nothing

/* one-table, multi-company scenarios */
require_once 'mc-C.php';
require_once 'mc-R.php';
require_once 'mc-RU.php';
require 'mc-R.php';
require 'mc-RD.php';
require 'mc-R.php';

/* two-table, one-company scenarios */
require_once '1cd-C.php';
require_once '1cd-RA.php';
require_once '1cd-CRUD.php';

/* three table scenarios */
require_once '1cde-C.php';
require_once '1cde-R.php';
require_once '1cde-CRUD.php';
//require_once '1cde-CRUDdetach.php';

/* one-table, one-employee scenario */
/* alters the metadata so put it last */
require_once '1e-CRUD.php';


?>