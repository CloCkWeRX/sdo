<html>
<title>Christmas Shopping</title>
<body>

<h1 ><img src="santa.jpg"/>Christmas Shopping<img src="santa2.jpg"/></h1>

Welcome to our Christmas shopping site. Feel free to browse or buy.<br/><br/>

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
include_once "Catalog/catalog.php";
include_once "table.php";

$catalog = get_catalog();
display_catalog($catalog); 
?>

<p>
<a href="view_cart.php?view">View Cart</a>

<form method=POST action="order_status.php">
<b>View order status:</b><br/>
Order ID:
<input type=text name="orderid"/>
<input type=submit name="status" value="Get Status"/>
</form> 

</body>
</html>