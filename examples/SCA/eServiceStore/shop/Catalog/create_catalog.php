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

/*
 * Created on Nov 8, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 function add_item($catalog, $itemId, $desc, $price, $quantity, $warehouseId)
 {
    $item = $catalog->createDataObject('item');
    $item->itemId = $itemId;
    $item->description = $desc;
    $item->price = $price;
    $item->quantity = $quantity;
    $item->warehouseId = $warehouseId;
 }
 
 
 $xmldas = SDO_DAS_XML::create('Catalog.xsd');
 
 $catalog = $xmldas->createDataObject('catalogNS', 'CatalogType');
 
 add_item($catalog, 1,  'A Partridge in a Pear Tree', 1.99, 0, 1);
 add_item($catalog, 2,  'Turtle Doves', 2.99, 0, 1);
 add_item($catalog, 3,  'French Hens', 3.99, 0, 1);
 add_item($catalog, 4,  'Calling Birds', 4.99, 0, 1);
 add_item($catalog, 5,  'Golden Rings', 5.99, 0, 1);
 add_item($catalog, 6,  'Geese a-laying', 6.99, 0, 1);
 add_item($catalog, 7,  'Swans a-swimming', 7.99, 0, 1);
 add_item($catalog, 8,  'Maids a-milking', 8.99, 0, 1);
 add_item($catalog, 9,  'Ladies dancing', 9.99, 0, 1);
 add_item($catalog, 10, 'Lords a-leaping', 10.99, 0, 1);
 add_item($catalog, 11, 'Pipers piping', 11.99, 0, 1);
 add_item($catalog, 12, 'Drummers drumming', 12.99, 0, 1);
 
 $doc = $xmldas->createDocument('catalogNS', 'catalog',$catalog);
// echo htmlspecialchars($xmldas->saveString($doc));
 
 $xmldas->saveFile($doc, 'Catalog.xml',4);
 
?>
