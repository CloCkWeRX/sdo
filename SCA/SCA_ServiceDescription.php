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
| Author: Matthew Peters                                                      |
+-----------------------------------------------------------------------------+
$Id: SCA_ServiceDescription.php 254122 2008-03-03 17:56:38Z mfp $
*/

if ( ! class_exists('SCA_ServiceDescription', false) ) {
    class SCA_ServiceDescription
    {

        public $class_name;
        
        /**
         * Used to restrict the operations on a service interface
         * (@service <interface_name>)
         *
         * @var string
         */
        public $interface_name;
        
        public $realpath;
        public $targetnamespace;
        
        /**
         * List of bindings
         *
         * @var array
         */
        public $binding;
        public $xsd_types;
        public $operations;
        public $script_name;
        public $http_host;
    }
}

?>