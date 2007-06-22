<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006, 2007                                    |
| All Rights Reserved.                                                        |
+-----------------------------------------------------------------------------+
| Licensed under the Apache License, Version 2.0 (the "License"); you may not |
| use this file except in compliance with the License. You may obtain a copy  |
| of the License at -                                                         |
|                                                                             |
|                   http://www.apache.org/licenses/LICENSE-2.0                |
|                                                                             |
| Unless required by applicable law or agreed to in writing, software         |
| distributed under the License is distributed on an "AS IS" BASIS, WITHOUT   |
| WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.            |
| See the License for the specific language governing  permissions and        |
| limitations under the License.                                              |
+-----------------------------------------------------------------------------+
| Authors: Graham Charters, Matthew Peters                                    |
|                                                                             |
+-----------------------------------------------------------------------------+
$Id$
*/

function display_events($events) {
    include_once "table.php";
    table_start();
    table_row_start();
    table_cell('<b>Date</b>', '#DDDDFF');
    table_cell('<b>Status</b>', '#DDDDFF');
    table_cell('<b>Description</b>', '#DDDDFF');
    table_row_end();
    foreach ($events->event as $event) {
        table_row_start();
        table_cell($event->timeStamp);
        table_cell($event->status);
        table_cell($event->description);
        table_row_end();
    }
    table_end();

}

?>