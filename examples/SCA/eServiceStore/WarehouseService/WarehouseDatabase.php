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

/**
 * @service
 * @types urn::orderNS ../Schema/Order.xsd
 */


class WarehouseDatabase
{

    public function write($order)
    {
        $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/../Schema/Order.xsd');
        $filename = dirname(__FILE__) . "/Orders/Order_" . $order->orderId . ".xml";
        $doc = $xmldas->createDocument('urn::orderNS', 'order',$order);
        $xmldas->saveFile($doc, $filename,2);
    }

    public function retrieveOrderById($order_id)
    {
        $files = $this->_order_files();
        $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/../Schema/Order.xsd');
        foreach ($files as $file) {
            $xdoc = $xmldas->loadFile(dirname(__FILE__) . '/Orders/' . $file);
            $order = $xdoc->getRootDataObject();
            if ($order->orderId == $order_id) {
                return $order;
            }
        }
        return null;
    }

    public function retrieveOrdersByStatus($status)
    {

        $files = $this->_order_files();

        $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/../Schema/Order.xsd');
        $orders = SCA::createDataObject('urn::orderNS', 'OrdersType');
        foreach ($files as $file) {
            $xdoc = $xmldas->loadFile(dirname(__FILE__) . '/Orders/' . $file);
            $order = $xdoc->getRootDataObject();
            if ($order->status == $status) {
                $orders->order[] = $order;
            }
        }
        return $orders;
    }

    public function updateOrderStatus($order_id, $status)
    {
        $order = $this->retrieveOrderById($order_id);
        $order->status = $status;

        $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/../Schema/Order.xsd');
        $doc = $xmldas->createDocument('urn::orderNS', 'order', $order);
        $xmldas->saveFile($doc, dirname(__FILE__) . '/Orders/Order_' . $order->orderId . '.xml',2);

    }

    private function _order_files() {
        $files = scandir(dirname(__FILE__) . '/Orders');
        $good_files = array();
        // get rid of '.' and '..' and '.cvsignore' and 'CVS'
        foreach ($files as $file) {
            $path_parts = pathinfo($file);
            if (isset($path_parts['extension']) && $path_parts['extension'] == 'xml') {
                $good_files[] = $file;
            }
        }
        return $good_files;
    }

}

?>