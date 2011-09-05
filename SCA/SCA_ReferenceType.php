<?php
/**
 * +-----------------------------------------------------------------------------+
 * | (c) Copyright IBM Corporation 2006, 2007.                                   |
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
 * @author   Graham Charters <gcc@php.net>
 * @author   Matthew Peters <mfp@php.net>
 * @author   Caroline Maynard <cem@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 *
 * $Id: SCA_ReferenceType.php 234864 2007-05-03 18:23:57Z mfp $
 */

/**
 * A container for and information specified in the doc comment
 * of a reference
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Graham Charters <gcc@php.net>
 * @author   Matthew Peters <mfp@php.net>
 * @author   Caroline Maynard <cem@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */
class SCA_ReferenceType
{
    protected $binding_type;
    protected $binding;
    protected $types;
    protected $class_name;
    protected $binding_config;

    /**
     * A container for and information specified in the doc comment
     * of a reference
     */
    public function __construct()
    {
        $this->binding = null;
        $this->types   = null;

        $this->binding_type = null;
    }

    /**
     * Set the current class name
     *
     * @param string $class_name Class to add
     *
     * @return null
     */
    public function addClassName($class_name)
    {
        $this->class_name = $class_name;
    }

    /**
     * Get the current class name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->class_name;
    }

    /**
     * Set the specific types.
     *
     * @param mixed $types Types to set
     *
     * @return null
     */
    public function addTypes($types)
    {
        $this->types = $types;
    }

    /**
     * Get types
     *
     * @return mixed
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Add a binding
     *
     * @param mixed $binding Binding to add
     *
     * @return null
     */
    public function addBinding($binding)
    {
        $this->binding = $binding;
    }

    /**
     * Get current binding
     *
     * @return mixed
     */
    public function getBinding()
    {
        return $this->binding;
    }

    /**
     * Set binding type
     *
     * @param mixed $binding_type Binding type
     *
     * @return null
     */
    public function setBindingType($binding_type)
    {
        $this->binding_type = $binding_type;
    }

    /**
     * Set any other binding specific configuration (e.g. @id 12 would
     * result in an entry in binding config equivalent to array('id' => 12).
     *
     * @param array $binding_config Configuration
     *
     * @return null
     */
    public function setBindingConfig($binding_config)
    {
        $this->binding_config = $binding_config;
    }

    /**
     * Get binding config
     *
     * @return array
     */
    public function getBindingConfig()
    {
        return $this->binding_config;
    }

    /**
     * Get the binding type
     *
     * @return mixes
     */
    public function getBindingType()
    {
        return $this->binding_type;
    }

    /**
     * Instantiate and populate an SDO_DAS_XML
     *
     * @return SDO_DAS_XML
     */
    public function getXmlDas()
    {
        $xsds   = $this->types;
        $xmldas = SDO_DAS_XML::create();

        foreach ($xsds as $index => $xsds) {
            list($namespace, $xsdfile) = $xsds;

            if (SCA_Helper::isARelativePath($xsdfile)) {
                $xsd = SCA_Helper::constructAbsolutePath(
                    $xsdfile,
                    $this->class_name
                );

                $xmldas->addTypes($xsd);
            }
        }

        return $xmldas;
    }
}
