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
| Author: Wangkai Zai                                                         |
|                                                                             |
+-----------------------------------------------------------------------------+
*/
if ( ! class_exists('SCA_Bindings_message_RequestTester', false) ) {

    class SCA_Bindings_message_RequestTester
    {
        public function isServiceDescriptionRequest($calling_component_filename)
        {
            if ( isset($_SERVER['REQUEST_METHOD']) ) {
                if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
                    $p1 = realpath($calling_component_filename);
                    $p2 = realpath($_SERVER['SCRIPT_FILENAME']);
                    if (($p1 == $p2) && ( isset($_GET['msd']) || 
                                          isset($_GET['wsdl']) ) ) {
                        return true;
                    }
                }
            }
            return false;
        }

        public function isServiceRequest($calling_component_filename)
        {
            //check if SAM extension is loaded
            if ( ! (in_array('sam', get_loaded_extensions())) ) {
                throw new SCA_RuntimeException("The SAM extension must be loaded");
            }
            return true;
        }
    }
}
?>
