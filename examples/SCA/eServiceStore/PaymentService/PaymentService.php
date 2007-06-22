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
 *
 */

// TODO split into two like WarehouseService
// TODO provide a front end app to administer payments

class PaymentService {

    function directPayment($payment) {

        // Write the payment details out to file
        $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/../Schema/Customer.xsd');
        $filename = dirname(__FILE__) . "/Payments/Payment_" . $payment->paymentId . ".xml";
        $doc = $xmldas->createDocument('customerNS', 'payment', $payment);
        $xmldas->saveFile($doc, $filename,2);
    }
}

?>
