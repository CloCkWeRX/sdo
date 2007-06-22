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
 */

// TODO split into two like WarehouseService

function event_files() {

    $files = scandir(dirname(__FILE__) . '/Events');

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

class EventLogService {

    function logEvent($order, $description='') {

        $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/../Schema/Event.xsd');
        $event = $xmldas->createDataObject('urn::eventNS', 'EventType');

        $event->eventId = microtime(true);
        $event->orderId = $order->orderId;
        $event->customerId = $order->customer->customerId;

        // PHP5.1 rc7 lost the consts :-(
        date_default_timezone_set('UTC');
        $event->timeStamp = date('jS F, Y G:i:s');

        $event->description = $description;
        $event->status = $order->status;

        // Write the event out to file
        $filename = dirname(__FILE__) . '/Events/Event_' . $event->eventId . '.xml';
        $doc = $xmldas->createDocument('urn::eventNS', 'event', $event);
        $xmldas->saveFile($doc, $filename,2);
    }

    function getEvents($order_id) {

        $files = event_files();

        $xmldas = SDO_DAS_XML::create(dirname(__FILE__) . '/../Schema/Event.xsd');
        $doc = $xmldas->createDocument('urn::eventNS', 'events');
        $events = $doc->getRootDataObject();
        foreach ($files as $file) {
            $xdoc = $xmldas->loadFile(dirname(__FILE__) . '/Events/' . $file);
            $event = $xdoc->getRootDataObject();
            if ($event->orderId == $order_id) {
                $events->event[] = $event;
            }
        }
        return $events;
    }
}
?>
