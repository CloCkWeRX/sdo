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

include_once "../shop/Cart/cart.php";
include_once "../shop/customer.php";

include "SCA/SCA.php";
$order_id = 1170781589;

$warehouse = SCA::getService("../WarehouseService/WarehouseService.php");
$order = $warehouse->getOrder($order_id);

$warehouse->signalDispatched($order_id);

$event_log = SCA::getService('../EventLogService/EventLogService.php');
$event_log->logEvent($order, 'Order dispatched from warehouse: ' . $order->item[0]->warehouseId);
print_r($order);
?>