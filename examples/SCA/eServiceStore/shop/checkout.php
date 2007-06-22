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
include_once "customer.php";

session_start();
?>
<html>
<head>
<title>Cart</title>
</head>
<body>
<p>
<?php
if (isset($_SESSION['cart'])) { // pick up the old cart if there is one, else get a new one
    $cart = $_SESSION['cart'];

    echo "<b>Your cart:</b><br/><br/>";
    
    display_cart($cart);

    display_customer_form();


} else {
    echo "Cart not found </br>";
}

?>

<p>
<a href="welcome.php">Home</a>

</body>
</html>
