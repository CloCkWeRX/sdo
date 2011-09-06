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
 * $Id: Proxy.php 238265 2007-06-22 14:32:40Z mfp $
 * 
 * PHP Version 5
 * 
 * @category SCA_SDO
 * @package  SCA_SDO
 * @author   Matthew Peters <mfp@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */

/**
 * Purpose:
 * To ensure that the target local SCA component is initialised.
 * To ensure pass by value semantics when methods on the component are called.
 * Also acts as a data factory for complex data types.
 *
 * Methods:
 *
 * __construct()
 * This method initialises the local SCA component and ensures that any @reference
 * annotation is processed.
 *
 * __call()
 * Copies the arguments to ensure that they are always passed by value even if passed
 * by reference by caller.
 * Calls the method on the component, passing copies of the arguments.
 *
 * createDataOject()
 * This method returns an SDO conforming to the data model specified in the
 * parameters.
 *
 * @category SCA_SDO
 * @package  SCA_SDO
 * @author   Matthew Peters <mfp@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */
class SCA_Bindings_Local_Proxy
{
    protected $instance_of_the_component            = null;
    protected $component_class_name                 = null;

    /**
     * Create the local proxy to the service given as an argument.
     *
     * @param string $target                       Target URI
     * @param string $base_path_for_relative_paths Path
     * @param mixed  $binding_config               Config
     */
    public function __construct($target, $base_path_for_relative_paths, $binding_config)
    {
        $absolute_path_to_component = SCA_Helper::constructAbsoluteTarget($target, $base_path_for_relative_paths);

        $this->component_class_name = SCA_Helper::guessClassName($absolute_path_to_component);

        if (!class_exists($this->component_class_name, false)) {
            include_once "$absolute_path_to_component";
        }

        $this->instance_of_the_component = SCA::createInstance($this->component_class_name);
        SCA::fillInReferences($this->instance_of_the_component);


    }

    /**
     * The infrastructure provides us with an object that
     * represents the whole doc comment that this proxy
     * is configured with. We don't use it here at the
     * moment.
     *
     * @param mixed $reference_type Reference Type
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
     * @param string $method_name Method
     * @param array  $arguments   Arguments
     *
     * @return mixed
     */
    public function __call($method_name, $arguments)
    {

        if ($this->instance_of_the_component === null) {
            $this->instance_of_the_component = SCA::createInstance($this->component_class_name);
            SCA::fillInReferences($this->instance_of_the_component);
        }

        if (SCA_Helper::checkMethods($method_name, $this->instance_of_the_component)) {
            $arguments_by_value_array = array();
            foreach ($arguments as $arg) {
                $arguments_by_value_array[] = is_object($arg)? clone $arg : $arg;
            }

            $return = call_user_func_array(
                array(&$this->instance_of_the_component, $method_name),
                $arguments_by_value_array
            );
        } else {
            $classname = get_class($this->instance_of_the_component);
            $msg       = "Method '{$method_name}' not found in class {$classname}";
            throw new SCA_RuntimeException($msg);
        }

        return $return;
    }

    /**
     * Create data pnkect
     *
     * @param string $namespace_uri Namespace URI
     * @param string $type_name     Type name
     *
     * @return mixed
     */
    public function createDataObject($namespace_uri, $type_name)
    {
        try {
            return SCA_Helper::createDataObject($namespace_uri, $type_name, $this->component_class_name);
        } catch (Exception $e) {
            throw new SCA_RuntimeException($e->getMessage());
        }
        // following return logically unecessary but keeps the ZS code
        // analyzer happy
        return null;
    }

}
