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


function get_catalog() {
       
    $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/Catalog.xsd');
    $xdoc = $xmldas->loadFile(dirname(__FILE__) . '/Catalog.xml');
    return $xdoc->getRootDataObject();
}

function display_catalog($catalog) {

	table_start();
	table_row_start();
	table_cell('Product');
	table_cell('Price');
	table_row_end();
    
  foreach ($catalog->item as $value) {
        table_row_start();
        table_cell('<a href="view_product.php?product_code='.
            $value->itemId .
            '">'.
            $value->description .
            '</a>');
        table_cell($value->price);
        table_row_end();
  }  
  table_end();    

}
?>
