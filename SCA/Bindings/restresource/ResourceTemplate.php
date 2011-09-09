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
 * A template for a resource service. The service must implement
 * the five methods defined here. It's not actually imperative
 * that the service physically extends this interface just that
 * the methods are implemented.
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Matthew Peters <mfp@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */
interface SCA_Bindings_Restresource_ResourceTemplate
{

    /**
     * $resource can be an sdo or string
     * returns an sdo
     *
     * @param SDO|string $resource Resource
     *
     * @return SDO
     */
    public function create($resource);

    /**
     * $id is a string that identifies a resource
     * returns an sdo
     *
     * @param string $id ID
     *
     * @return SDO
     */
    public function retrieve($id);

    /**
     * $id is a string that identifies a resource, $resource
     * is the new version of the resource for this id
     * returns an sdo
     *
     * @param string $id       ID
     * @param SDO    $resource Resource
     *
     * @return SDO
     */
    public function update($id, $resource);

    /**
     * Deletes the resource for $id
     * returns void
     *
     * @param string $id ID
     *
     * @return null
     */
    public function delete($id);

    /**
     * Returns a collection of resource id's that this
     * returns void
     *
     * @return null
     */
    public function enumerate();
}