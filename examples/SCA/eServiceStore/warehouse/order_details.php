<html>
<title>
Order Number <?= $_GET['orderId']; ?>
</title>
<body>

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

include_once "../shop/display_cart.php";
include_once "../shop/customer.php";

include "SCA/SCA.php";

$warehouse = SCA::getService("../WarehouseService/WarehouseService.php");
$order_id  = $_GET['orderId'];
$order     = $warehouse->getOrder($order_id);

if (isset($_POST['DISPATCHED'])) {
    $warehouse->signalDispatched($order_id);
    $event_log     = SCA::getService('../EventLogService/EventLogService.php');
    $order->status = 'DISPATCHED';
    $event_log->logEvent($order, 'Order dispatched from warehouse: ' . $order->item[0]->warehouseId);
}

echo "<b>Order Number: " . $order_id . "</b><br/><br/>";

// TODO we are using display_cart to display and order. Not right
display_cart($order);

echo "<br/><b>Customer Details: </b><br/><br/>";

display_customer($order->customer);

echo "<br/><b>Status (" . $order->status. "): </b>";

if ($order->status != 'DISPATCHED') {
    echo '<form method=POST action="order_details.php?orderId=' . $order_id . '">';
    echo '<input type=submit name="DISPATCHED" value="Dispatched"/>';
    echo '</form>';
}

?>

</br><a href="./warehouse.php">Home</a>
</body>
</html>