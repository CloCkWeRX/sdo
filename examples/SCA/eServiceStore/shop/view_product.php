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
 

include_once "Catalog/catalog.php"; ?>

<html>
<head>
<title>Product Page</title>
</head>
<body>
<?php
$product_code = $_GET['product_code'];  // obtained from the URL
$catalog = get_catalog();
$product = $catalog["item[itemId=$product_code]"];
echo "<b>".$product->description."</b><br/>";
echo "(Imagine a compelling picture here)<br/>";
print "</b><br/>";
print "Click on Add to Cart to buy.";
?>


<p>
<a href="welcome.php">Home</a>
<p>
<?php
echo '<a href="view_cart.php?add='.
		$product_code.
		'">Add to Cart</a>';
?>
  

</body>
</html>
  