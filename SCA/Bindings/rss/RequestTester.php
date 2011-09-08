<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2007.                                         |
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
|         Megan Beynon,                                                       |
|         Caroline Maynard                                                    |
+-----------------------------------------------------------------------------+
$Id: RequestTester.php 238265 2007-06-22 14:32:40Z mfp $
*/

class SCA_Bindings_rss_RequestTester
{

    /**
     * isServiceDescriptionRequest ?
     *
     * @param string $calling_component_filename Filename
     *
     * @return bool
     */
    public function isServiceDescriptionRequest($calling_component_filename)
    {
        // RSS doesn't have service descriptions
        return false;
    }

    /**
     * Is service request?
     *
     * @param string $calling_component_filename Filename
     *
     * @return bool
     */
    public function isServiceRequest($calling_component_filename)
    {
        // RSS uses GET
        if (isset($_SERVER['HTTP_HOST']) &&
          ($_SERVER['REQUEST_METHOD'] == 'GET')) {
            return true;
        }
        return false;
    }

}

?>