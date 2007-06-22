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

include "SCA/SCA.php";

function display_orders($status)
{
    $warehouse = SCA::getService('../WarehouseService/WarehouseService.php');
    $orders = $warehouse->getOrdersByStatus($status);

    if (count($orders->order) == 0) {
        echo "None\n";
        return;
    }

    include_once "./table.php";

    table_start();

    table_row_start();
    table_cell('<b>Order ID</b>', '#DDDDFF');
    table_cell('<b>Name</b>', '#DDDDFF');
    table_cell('<b>Status</b>', '#DDDDFF');
    table_row_end();

    $odd = false;

    foreach ($orders->order as $order) {

        table_row_start();

        if (!$odd) {
            table_cell("<a href=\"./order_details.php?orderId=" . $order->orderId . "\">" . $order->orderId . "</a>");
            table_cell(isset($order->customer->name) ? $order->customer->name : 'no name supplied');
            table_cell($order->status);
        }
        else {
            table_cell("<a href=\"./order_details.php?orderId=" . $order->orderId . "\">" . $order->orderId . "</a>", '#DDFFFF');
            table_cell(isset($order->customer->name) ? $order->customer->name : 'no name supplied', '#DDFFFF');
            table_cell($order->status, '#DDFFFF');
        }

        $odd = !$odd;

        table_row_end();
    }
    table_end();
}


?>
