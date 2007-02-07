<?php
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                  |
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+
|                                                                      |
| Licensed under the Apache License, Version 2.0 (the "License"); you  |
| may not use this file except in compliance with the License. You may |
| obtain a copy of the License at                                      |
| http://www.apache.org/licenses/LICENSE-2.0                           |
|                                                                      |
| Unless required by applicable law or agreed to in writing, software  |
| distributed under the License is distributed on an "AS IS" BASIS,    |
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
| implied. See the License for the specific language governing         |
| permissions and limitations under the License.                       |
+----------------------------------------------------------------------+
| Author: Matthew Peters                                               |
+----------------------------------------------------------------------+
$Id$
*/

/**
 * Load the jungle and animal schema
 */
$xmldas = SDO_DAS_XML::create('jungle.xsd');
$xmldas->addTypes('animalTypes.xsd');

/**
 * Create three animals
 */
$baloo          = $xmldas->createDataObject('','bearType');
$baloo->name    = "Baloo";
$baloo->weight  = 700;

$bagheera         = $xmldas->createDataObject('','pantherType');
$bagheera->name   = "Bagheera";
$bagheera->colour = 'inky black';

$kaa            = $xmldas->createDataObject('','snakeType');
$kaa->name      = "Kaa";
$kaa->length    = 25;

/**
 * Create a jungle (picks the root element <jungle/>)
 * Jungle is an open type (i.e. specifies it can contain "any" type)
 */
$document          = $xmldas->createDocument();
$jungle            = $document->getRootDataObject();
$jungle->bear      = $baloo;
$jungle->panther   = $bagheera;
$jungle->snake     = $kaa;

/**
 * Write out the resulting XML
 */
header('Content-type: application/xml');
print($xmldas->saveString($document, 2));

?> 