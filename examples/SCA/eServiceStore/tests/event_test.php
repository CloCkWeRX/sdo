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

$xmldas = SDO_DAS_XML::create('Event.xsd');

$doc = $xmldas->createDocument('urn::eventNS', 'events');
$events = $doc->getRootDataObject();

date_default_timezone_set('UTC');

$event = $events->createDataObject('event');
$event->eventId = 1;
$event->orderId = 2;
$event->customerId = 3;
$event->timeStamp = date(DATE_W3C);
$event->description = "This is an event for a newly opened order.";
$event->status = "RECEIVED";

$event = $events->createDataObject('event');
$event->eventId = 2;
$event->orderId = 2;
$event->customerId = 3;
$event->timeStamp = date(DATE_W3C);
$event->description = "This is an event for completing an order.";
$event->status = "COMPLETED";

echo $xmldas->saveString($doc,2);

echo "\n Now test the single event \n";

$doc   = $xmldas->createDocument('urn::eventNS', 'event');
$event = $doc->getRootDataObject();

$event->eventId = 3;
$event->orderId = 2;
$event->customerId = 3;
$event->timeStamp = date(DATE_W3C);
$event->description = "This is an event for a cancelled order.";
$event->status = "CANCELLED";

echo $xmldas->saveString($doc,2);

?>