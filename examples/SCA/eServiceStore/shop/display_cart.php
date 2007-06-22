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

include_once "table.php";


function display_cart($cart) {
    
  $cell_colour = "#DDFFFF";

  table_start();
  table_row_start();
  table_cell('<b>Product</b>', $cell_colour);
  table_cell('<b>Quantity</b>', $cell_colour);
  table_cell('<b>Price</b>', $cell_colour);
  table_row_end();

  $total = 0;
  foreach ($cart->item as $value) {
    table_row_start();
    table_cell($value->description);
    table_cell($value->quantity);
    table_cell($value->price * $value->quantity);
    $total += $value->price * $value->quantity;
    table_row_end();
  }
  table_row_start();
  table_cell('');
  table_cell('<b> Total </b>', $cell_colour);
  table_cell("<b>$total<b>", $cell_colour);
  table_row_end();
  table_end();

}

?>