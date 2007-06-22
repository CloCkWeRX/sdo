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

include_once "display_cart.php";
include "SCA/SCA.php";

session_start();
?>
<html>
<head>
<title>Order Submission</title>
</head>
<body>
<?php

$order_process = SCA::getService('../OrderProcessingService/OrderProcessingService.php');

/**
 * pick up the cart from the session
 */
//if (isset($_SESSION['cart'])) { 
    $cart = $_SESSION['cart'];
//} else {
//    echo 'you have jumped into the middle of a dialog here. Go back to <p><a href="welcome.php">Home</a></p>';
//    exit;
//}

$total = 0;
foreach ($cart->item as $item) {
    $total += $item->quantity * $item->price;
}

// We should 'clean' the posted data at this point

$customer              = $order_process->createDataObject('urn::customerNS','CustomerType');
$customer->customerId  = 1;
$customer->name        = $_POST['Name'];
$shipping              = $customer->createDataObject('shipping');
$shipping->street      = $_POST['Street'];
$shipping->city        = $_POST['City'];
$shipping->state       = $_POST['State'];
$shipping->zip         = $_POST['Zip'];
$payment               = $customer->createDataObject('payment');
$payment->accountNo    = $_POST['Account'];
$payment->bank         = $_POST['Bank'];
$payment->securityCode = $_POST['SecurityCode'];
$payment->amount       = $total;

$order_id = $order_process->placeNewOrder($cart,$customer);

echo '<p><b>Thank you for your order:</b></p>';
display_cart($cart);
echo "<br/><b>Your order number is $order_id</b><br/><br/>";
echo "<br/><b>You may use this order number on our home page to enquire on the status of your order.</b><br/><br/>";
unset($_SESSION['cart']);

?>

<p><a href="welcome.php">Home</a></p>

</body>
</html>
