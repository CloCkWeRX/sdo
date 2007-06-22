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

include "SCA/SCA.php";

/**
 * @service
 * @binding.ws
 * @types urn::orderNS ../Schema/Order.xsd
 */

class WarehouseService {
    
    /**
     * @reference
     * @binding.php ./WarehouseDatabase.php
     */
    public $database;

    function fulfillOrder($order) 
    {
        $this->database->write($order);
    }

		/**
     * Get Order
     *
     * @param integer $order_id The order number.
     * @return OrderType urn::orderNS The order.
     */
    function getOrder($order_id) 
    {    
        $order = $this->database->retrieveOrderById($order_id);
        if ($order == null) {
            throw new WarehouseOrderNotFoundException();
        }
        return $order;
    }

		/**
     * Get Orders by status
     *
     * @param OrderStatus $status urn::orderNS The order status.
     * @return OrdersType urn::orderNS The order.
     */
    function getOrdersByStatus($status) 
    {    
        $orders = $this->database->retrieveOrdersByStatus($status);
        return $orders;
    }



    public function signalDispatched($order_id)
    {
        $this->database->updateOrderStatus($order_id,'DISPATCHED');
    }

}
?>
