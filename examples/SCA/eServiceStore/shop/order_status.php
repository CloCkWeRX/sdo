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


include_once "../Includes/WarehouseOrderNotFoundException.php";

include_once "display_cart.php";
include_once "display_events.php";

include "SCA/SCA.php";
if (!isset($_POST['orderid']) || ($_POST['orderid'] == "")) {
    echo '<b>No order number provided.  Please try again.</b>';
    echo '<p><a href="welcome.php">Home</a></p>';
    return;
}
$order_id = $_POST['orderid'];

?>
<html>
<title>
Status for order: <?php $order_id; ?>
</title>
<body>
<?php

$order_processing = SCA::getService("../OrderProcessingService/OrderProcessingService.php");
try {
$order = $order_processing->getOrder($order_id);
} catch (WarehouseOrderNotFoundException $e) {
    echo "<b>No order with order id $order_id was found</b><br/><br/>";
    echo '<p><a href="welcome.php">Home</a></p>';
    exit;
}

echo "<b>Order details: $order_id</b><br/><br/>";

display_cart($order);

echo "<b>Status:</b><br/><br/>";

$event_log = SCA::getService('../EventLogService/EventLogService.php');
$events = $event_log->getEvents($order_id);

display_events($events);

?>

<p><a href="welcome.php">Home</a></p>

</body>
</html>
