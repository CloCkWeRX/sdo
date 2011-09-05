<?php
/**
 * +----------------------------------------------------------------------+
 * | (c) Copyright IBM Corporation 2007.                                  |
 * | All Rights Reserved.                                                 |
 * +----------------------------------------------------------------------+
 * |                                                                      |
 * | Licensed under the Apache License, Version 2.0 (the "License"); you  |
 * | may not use this file except in compliance with the License. You may |
 * | obtain a copy of the License at                                      |
 * | http://www.apache.org/licenses/LICENSE-2.0                           |
 * |                                                                      |
 * | Unless required by applicable law or agreed to in writing, software  |
 * | distributed under the License is distributed on an "AS IS" BASIS,    |
 * | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
 * | implied. See the License for the specific language governing         |
 * | permissions and limitations under the License.                       |
 * +----------------------------------------------------------------------+
 * | Author: Matthew Peters                                               |
 * +----------------------------------------------------------------------+
 * $Id: SCA_BindingFactory.php 254122 2008-03-03 17:56:38Z mfp $
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
 * Binding factory
 *
 * @category SCA_SDO
 * @package  SCA_SDO
 * @author   Matthew Peters <mfp@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */
class SCA_Binding_Factory
{
    /**
     * Load a request tester
     *
     * @param string $binding_string Binding string
     *
     * @return object
     */
    public static function createRequestTester($binding_string)
    {
        $tester_class_name = self::_generateClassNameAndLoadClass(
            $binding_string,
            'RequestTester'
        );

        return new $tester_class_name();
    }

    /**
     * Load a request tester
     *
     * @param string $binding_string Binding string
     *
     * @return object
     */
    public static function createServiceDescriptionGenerator($binding_string)
    {
        $tester_class_name = self::_generateClassNameAndLoadClass(
            $binding_string,
            'ServiceDescriptionGenerator'
        );

        return new $tester_class_name();
    }

    /**
     * Load a request tester
     *
     * @param string $binding_string Binding string
     *
     * @return object
     */
    public static function createServiceRequestHandler($binding_string)
    {
        $tester_class_name = self::_generateClassNameAndLoadClass(
            $binding_string,
            'ServiceRequestHandler'
        );

        return new $tester_class_name();
    }

    /**
     * Load a proxy
     *
     * @param string $binding_string               Binding string
     * @param string $target                       Target
     * @param string $base_path_for_relative_paths Base Path
     * @param string $binding_config               Binding config
     *
     * @return object
     */
    public static function createProxy(
        $binding_string,
        $target,
        $base_path_for_relative_paths,
        $binding_config
    ) {
        SCA::$logger->log('Entering');
        SCA::$logger->log("binding_string = $binding_string, target = $target");

        $proxy_class_name = self::_generateClassNameAndLoadClass(
            $binding_string,
            'Proxy'
        );

        return new $proxy_class_name($target, $base_path_for_relative_paths, $binding_config);
    }

    /**
     * Load class
     *
     * @param string $binding_string Binding string
     * @param string $class          Class
     *
     * @return object
     */
    private static function _generateClassNameAndLoadClass($binding_string, $class)
    {
        $full_class_name = "SCA_Bindings_{$binding_string}_{$class}";
        if (!class_exists($full_class_name, false)) {
            $class_filename = "SCA/Bindings/{$binding_string}/$class.php";
            include_once $class_filename;
        }
        return $full_class_name;
    }
}
