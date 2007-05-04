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

class SCA_Bindings_restrpc_RequestTester
{
    public function isServiceDescriptionRequest($calling_component_filename)
    {
        return false;
    }

    public function isServiceRequest($calling_component_filename)
    {
        // check if the request is a GET or a POST and that
        // there is something (the actoin name) in the path info
        if (isset($_SERVER['HTTP_HOST'])) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST'
                || $_SERVER['REQUEST_METHOD'] == 'GET') {
                $p1 = realpath($calling_component_filename);
                $p2 = realpath($_SERVER['SCRIPT_FILENAME']);
                if ($p1 == $p2 && 
                    isset($_SERVER['PATH_INFO'])){
                    return true;
                }
            }
        }

        return false;
    }

}

?>