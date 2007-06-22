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
include_once "table.php";
include_once "Catalog/catalog.php";

session_start();
if (isset($_SESSION['cart'])) { // pick up the old cart if there is one, else get a new one
    $cart = $_SESSION['cart'];
} else {

    $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/../Schema/Order.xsd');
    $cart = $xmldas->createDataObject('urn::orderNS', 'OrderType');
    $cart->orderId = time();
    $cart->status = 'NONE';
    $_SESSION['cart'] = $cart;

}

?>

<html>
<head>
<title>Cart</title>
</head>
<body>
<p>

<?php
if (isset($_GET['clear'])) {
    unset($cart->item);  // clear out the cart
}
if (isset($_GET['add'])) {
    $catalog = get_catalog();
    $add = $_GET['add'];
    $item_from_catalog = $catalog["item[itemId=$add]"];
    try {
        $item_in_cart = $cart["item[itemId=$add]"];
        $item_in_cart->quantity++;
    } catch (SDO_IndexOutOfBoundsException $e) {
        $item_in_cart = $cart->createDataObject('item');
        $item_in_cart->quantity = 1;
    }
    $item_in_cart->itemId      = $item_from_catalog->itemId;
    $item_in_cart->description = $item_from_catalog->description;
    $item_in_cart->price       = $item_from_catalog->price;
    $item_in_cart->warehouseId = $item_from_catalog->warehouseId;
}

if (count($cart->item) == 0) {
    echo "<b>Your cart is empty.</b>";
} else {
    echo "<b>Your cart:</b><br/><br/>";
    display_cart($cart);

    echo '<form method=GET action="view_cart.php">';
    echo '<input type=submit value="Clear Cart" name="clear"/>';
    echo '</form>';
    echo '<form method=POST action="checkout.php">';
    echo '<input type=submit value="Checkout" name="clear"/>';
    echo '</form>';
}

?>

<p><a href="welcome.php">Home</a></p>

</body>
</html>
  