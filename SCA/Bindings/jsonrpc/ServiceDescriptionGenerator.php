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
$Id: ServiceDescriptionGenerator.php 234945 2007-05-04 15:05:53Z mfp $
*/

require 'SCA/Bindings/jsonrpc/SCA_GenerateSmd.php';

if ( ! class_exists('SCA_Bindings_Jsonrpc_ServiceDescriptionGenerator', false) ) {

    class SCA_Bindings_Jsonrpc_ServiceDescriptionGenerator
    {
        public function generate($service_description)
        {
            SCA::$logger->log( "Entering");
            try
            {
                $smd_str = SCA_GenerateSmd::generateSmd($service_description);
                SCA::sendHttpHeader('Content-type: text/plain');
                echo $smd_str;
            } catch (SCA_RuntimeException $se ) {
                echo $se->exceptionString() . "\n" ;
            } catch( SDO_DAS_XML_FileException $e) {
                throw new SCA_RuntimeException("{$e->getMessage()} in {$e->getFile()}");
            }
            return;
        }

    }
}

?>