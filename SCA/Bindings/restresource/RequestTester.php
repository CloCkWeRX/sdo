<?php
/**
 * +-----------------------------------------------------------------------------+
 * | (c) Copyright IBM Corporation 2006.                                         |
 * | All Rights Reserved.                                                        |
 * +-----------------------------------------------------------------------------+
 * | Licensed under the Apache License, Version 2.0 (the "License"); you may not |
 * | use this file except in compliance with the License. You may obtain a copy  |
 * | of the License at -                                                         |
 * |                                                                             |
 * |                   http://www.apache.org/licenses/LICENSE-2.0                |
 * |                                                                             |
 * | Unless required by applicable law or agreed to in writing, software         |
 * | distributed under the License is distributed on an "AS IS" BASIS, WITHOUT   |
 * | WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.            |
 * | See the License for the specific language governing  permissions and        |
 * | limitations under the License.                                              |
 * +-----------------------------------------------------------------------------+
 * | Author: Graham Charters,                                                    |
 * |         Matthew Peters,                                                     |
 * |         Megan Beynon,                                                       |
 * |         Chris Miller,                                                       |
 * |         Caroline Maynard,                                                   |
 * |         Simon Laws                                                          |
 * +-----------------------------------------------------------------------------+
 *
 * PHP Version 5
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Matthew Peters <mfp@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */

/**
 * SCA_Bindings_restresource_RequestTester
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Matthew Peters <mfp@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */
class SCA_Bindings_Restresource_RequestTester
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
        if (isset($_SERVER['HTTP_HOST'])) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST'
                || $_SERVER['REQUEST_METHOD'] == 'GET'
                || $_SERVER['REQUEST_METHOD'] == 'PUT'
                || $_SERVER['REQUEST_METHOD'] == 'DELETE'
            ) {
                $p1 = realpath($calling_component_filename);
                $p2 = realpath($_SERVER['SCRIPT_FILENAME']);
                if ($p1 == $p2) {
                    return true;
                }
            }
        }

        return false;
    }

}