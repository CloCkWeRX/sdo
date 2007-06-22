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

include_once "display_cart.php";
include_once "display_events.php";

$order_process = SCA::getService('../OrderProcessingService/OrderProcessingService.php');

/**
 * create and fill in a cart
 */
$cart              = $order_process->createDataObject('cartNS','CartType');
$item              = $cart->createDataObject('item');
$item->itemId      = 1;
$item->description = "Bath Salts";
$item->price       = "0.99";
$item->quantity    = 6;

/**
 * create and fill in the customer information
 */
$customer              = $order_process->createDataObject('customerNS','CustomerType');
$customer->customerId  = 1;
$customer->name        = 'Matthew';
$shipping              = $customer->createDataObject('shipping');
$shipping->street      = 'Desk CS1J9';
$shipping->city        = 'Hursley';
$shipping->state       = 'Winchester';
$shipping->zip         = 'SO21 2JN';
$payment               = $customer->createDataObject('payment');
$payment->accountNo    = '2131231233131';
$payment->bank         = 'Big Bank of Nowhere';
$payment->securityCode = 'you must be joking';
$payment->amount       = 999;

/**
 * submit the order
 */
$order_number = $order_process->placeNewOrder($cart,$customer);

echo "<br/><b>Order Confirmation No: " . $order_number . "</b><br/><br/>";

$order = $order_process->getOrder($order_number);

echo "Order details: $order_number\n";

display_cart($order);

echo "\n\n\nStatus:\n";

$event_log = SCA::getService('../EventLogService/EventLogService.php');
$events = $event_log->getEvents($order_number);

display_events($events);


?>
