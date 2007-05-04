<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                         |
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
*/

include "SCA/Bindings/restrpc/Wrapper.php";
include "SCA/Bindings/restrpc/Server.php";

class SCA_Bindings_restrpc_ServiceRequestHandler
{
    public function handle($calling_component_filename, $service_description)
    {
        SCA::$logger->log('Entering');

        $class_name = SCA_Helper::guessClassName($calling_component_filename);

        $service_wrapper = new SCA_Bindings_restrpc_Wrapper($class_name);

        $server = new SCA_Bindings_restrpc_Server($service_wrapper);

        $server->handle();
    }
}

?>