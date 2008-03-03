<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006, 2007.                                   |
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
| Author: Graham Charters,                                                    |
|         Matthew Peters,                                                     |
|         Megan Beynon,                                                       |
|         Chris Miller,                                                       |
|         Caroline Maynard,                                                   |
|         Simon Laws                                                          |
+-----------------------------------------------------------------------------+
$Id$
*/

if ( ! class_exists('SCA_Bindings_Atom_RequestTester', false) ) {
    class SCA_Bindings_Atom_RequestTester
    {
        public function isServiceDescriptionRequest($calling_component_filename)
        {
            // there is no equivalent to WSDL, SMD, system.describe for atom
            return false;
        }

        //example requests for Atom:
        //GET http://localhost:1112/examples/SCA/Atom/ContactFeed.php
        //GET http://localhost:1112/examples/SCA/Atom/ContactFeed.php/7
        //POST http://localhost:1112/examples/SCA/Atom/ContactFeed.php
        //PUT http://localhost:1112/examples/SCA/Atom/ContactFeed.php/7
        //DELETE http://localhost:1112/examples/SCA/Atom/ContactFeed.php/7
        //Only Atom supports GET requests that go directly to SCA components, so if a GET request is destined directly for an SCA component, it must be an Atom request.

        public function isServiceRequest($calling_component_filename)
        {
            //Should be the case for POST and PUT that the content type will be set.
            //Delete does not require it though, and although the atompub spec requires GET requests to specify the Accept header, this is not always observed.
            if (isset($_SERVER['HTTP_HOST'])) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST'
                || $_SERVER['REQUEST_METHOD'] == 'GET'
                || $_SERVER['REQUEST_METHOD'] == 'PUT'
                || $_SERVER['REQUEST_METHOD'] == 'DELETE') {
                    $p1 = realpath($calling_component_filename);
                    $p2 = realpath($_SERVER['SCRIPT_FILENAME']);
                    if ($p1 == $p2) {
                        /*&&
                        (isset($_SERVER['CONTENT_TYPE']) &&
                        strpos($_SERVER['CONTENT_TYPE'], 'application/atom+xml') !== FALSE) ||
                        (isset($_SERVER['HTTP_ACCEPT']) &&
                        strpos($_SERVER['HTTP_ACCEPT'], 'application/atom+xml') !== FALSE)*/
                        return true;
                    }
                }

            }
            return false;
        }

    }
}
?>