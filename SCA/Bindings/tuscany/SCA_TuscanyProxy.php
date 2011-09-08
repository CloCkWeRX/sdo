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
 * $Id: SCA_TuscanyProxy.php 234945 2007-05-04 15:05:53Z mfp $
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
 * Tuscany proxy
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Matthew Peters <mfp@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */
class SCA_TuscanyProxy
{

    protected $containing_class_name          = null;
    protected $reference_name                 = null;

    /**
     * Create the local proxy to the service given as an argument.
     *
     * @param string $reference_name Reference name
     */
    public function __construct($reference_name)
    {
        $this->reference_name = $reference_name;
    }

    /**
     * The infrastructure provides us with an object that
     * represents the whole doc comment that this proxy
     * is configured with. We don't use it here at the
     * moment.
     *
     * @param string $reference_type Reference type
     *
     * @return null
     */
    public function addReferenceType($reference_type)
    {
    }

    /**
     * Add containing class name
     *
     * @param string $class_name Class name
     *
     * @return null
     */
    public function addContainingClassName($class_name)
    {
        $this->containing_class_name = $class_name;
    }

    /**
     * Invoke the method name in the target service.
     *
     * @param string $method_name Method name
     * @param array  $arguments   Arguments
     *
     * @return mixed
     */
    public function __call($method_name, $arguments)
    {
        // Invoke the Tuscany service

        $return = SCA_Tuscany::invoke(
            $this->containing_class_name,
            $this->reference_name,
            $method_name,
            $arguments
        );

        return $return;
    }

    /**
     * Create a data object
     *
     * @param string $namespace_uri Namespace URI
     * @param string $type_name     Type name
     *
     * @return mixed
     */
    public function createDataObject($namespace_uri, $type_name)
    {
        try {
            return SCA_Helper::createDataObject(
                $namespace_uri, $type_name, $this->component_class_name
            );
        } catch (Exception $e) {
            throw new SCA_RuntimeException($e->getMessage());
        }
        // following return logically unecessary but keeps the ZS code
        // analyzer happy
        return null;
    }

}

