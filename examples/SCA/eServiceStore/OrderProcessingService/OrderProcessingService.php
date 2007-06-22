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
 * @binding.ws
 * @types urn::cartNS     ../Schema/Cart.xsd
 * @types urn::customerNS ../Schema/Customer.xsd
 * @types urn::orderNS    ../Schema/Order.xsd
 */

class OrderProcessingService {

    /**
     * @reference
     * @binding.php ../WarehouseService/WarehouseService.php
     */
    public $warehouse;

    /**
     * @reference
     * @binding.php ../EventLogService/EventLogService.php
     */
    public $event_log;

    /**
     * @reference
     * @binding.php ../PaymentService/PaymentService.php
     */
    public $payment;

		/**
     * Place a new order.
     *
     * @param CartType $cart urn::cartNS The ticker symbol.
     * @param CustomerType $customer urn::customerNS The customer data.
     * @return integer The order id.
     */
    public function placeNewOrder($cart,$customer) 
    {

        $order           = SCA::createDataObject('urn::orderNS','OrderType');
        $order->orderId  = time();
        $order->status   = 'NONE';
        foreach ($cart->item as $item_in_cart) {
            // TODO is a all-in-one copy from one NS to another in the wishlist?
            $item_in_order              = $order->createDataObject('item');
            $item_in_order->itemId      = $item_in_cart->itemId;
            $item_in_order->description = $item_in_cart->description;
            $item_in_order->price       = $item_in_cart->price;
            $item_in_order->quantity    = $item_in_cart->quantity;
            $item_in_order->warehouseId = $item_in_cart->warehouseId;
        }
        $order->customer = $customer;

        $payment = $order->customer->payment;
        unset($order->customer->payment); // don't send the payment details to the warehouse
        $payment->paymentId = $order->orderId;

        // Send the order to the warehouse for processing
        $this->warehouse->fulfillOrder($order);

        // Log an event to say it's been sent to the warehouse
        $order->status='RECEIVED';
        $this->event_log->logEvent($order, "Order awaiting dispatch from warehouse.");

        // Send a payment request to the payment provider
        $this->payment->directPayment($payment);

        // Log an event to say the payment has been requested
        $order->status='INVOICED';
        $this->event_log->logEvent($order, "Payment taken.");

        return $order->orderId;

    }

		/**
     * Get an order.
     *
     * @param integer $order_id The order id.
     * @return OrderType urn::orderNS An order.
     */
    public function getOrder($order_id) {

        // Get an order
        return $this->warehouse->getOrder($order_id);

    }


}

?>
