<?php
/*
+-----------------------------------------------------------------------------+
| Copyright IBM Corporation 2006.                                             |
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
|         Chris Miller.                                                       |
|                                                                             |
+-----------------------------------------------------------------------------+
$Id$
*/

require "SCA/SCA_RuntimeException.php";

if ( ! class_exists('SCA_Helper', false) ) {
    class SCA_Helper {

        public static function guessClassName($class_file)
        {
            $basename = basename($class_file, '.php');
            return $basename;
        }

        public static function isARelativePath($path)
        {
            /**
             * relative paths:
             *    ../anything
             *    ./anything
             *    ..\anything
             *    .\anything
             *    anything
             *
             * absolute paths:
             *    /anything
             *    \anything
             *    X:\anything
             */
            if (substr($path, 0, 1) == ".")     return true;
            if (strpos($path, ":") != 0)     return false;
            if (substr($path, 0, 1) == '/' ) return false;
            if (substr($path, 0, 1) == '\\' ) return false;
            return true;
        }

        public static function constructAbsolutePath($relative_path, $class_name)
        {
            
            $file_containing_class = SCA_Helper::getFileContainingClass($class_name);
             
            $dir           = dirname($file_containing_class);
            $absolute_path = realpath("$dir/$relative_path");            
            if ($absolute_path === null || strlen($absolute_path) == 0) {
                throw new SCA_RuntimeException("Only an invalid absolute path could be constructed from class $class_name and relative path $relative_path");
            }
            return $absolute_path;
        }

        public static function getFileContainingClass($class_name)
        {
            foreach (get_included_files() as $file) {
                
                if ($class_name == basename($file, ".php")) {
                    
                    return $file;
                }
                
            }
            throw new SCA_RuntimeException("Unable to determine which file contains class $class_name \n");
        }

        public static function checkExtensionsLoaded()
        {
            $extensionArray = get_loaded_extensions();

            if ( ! (in_array('sdo', $extensionArray)) ) {
                throw new SCA_RuntimeException("The SDO extension must be loaded");
            }

            if ( ! (in_array('soap', $extensionArray)) ) {
                throw new SCA_RuntimeException("The soap extension must be loaded");
            }

            $sdoversion = phpversion('sdo');

            if ( strcmp($sdoversion, '1.0.4') < 0 ) {
                throw new SCA_RuntimeException("The SDO extension must be 1.0.4 or later. You have " . $sdoversion);
            }

            if ( ! self::findExtensionMethod('setObject', 'soap') ) {
                throw new SCA_RuntimeException("This soap extension does not support the setObject() call");
            }
        }

        /**
        * Search for a matching method name in a php extension class
        *
        * @param string $methodName      name of the method to be found
        * @param string $extensionName   Name of the extension to be checked
        * @return boolean                true' if found
        */
        public static function findExtensionMethod( $methodName, $extensionName )
        {
            // TODO implement this method using the reflection API
            return true ;
        }/* End find extension method function                                     */

        public static function wsdlWasGeneratedForAnScaComponent($wsdl_file_name)
        {
            $wsdl = file_get_contents($wsdl_file_name);
            if (strpos($wsdl, "WSDL generated by SCA for PHP") > 0) {
                return true;
            } else {
                return false;
            }
        }

        public static function createDataObject( $namespace_uri, $type_name, $class_name)
        {
                $xmldas     = self::_getXmldas($class_name, $namespace_uri);
                $dataobject = $xmldas->createDataObject($namespace_uri, $type_name);
                return      $dataobject;
        }

        /**
        * Find out if the method to be called exists in the target class.
        *
        * @param string $method
        * @param string $class
        * @return boolean
        */
        public static function checkMethods( $method, $class )
        {
            $return       = false ;
            $classMethods = get_class_methods($class);

            foreach ( $classMethods as $method_name ) {
                if ( $method === $method_name ) {
                    $return = true ;
                    break ;
                }
            }

            return $return ;

        }/* End check methods */

        /**
         * The ReflectMethod class returns all of the methods in a class. To build a
         * WSDL only public methods may be used, so using the get_class_methods call
         * ( which only lists public methods ) the filter removes any protected, or
         * private functions.
         *
         * @param array $public_list 1dim array of public methods
         * @param unknown_type $allMethodsArray 2dim array of all methods
         * @return array     modified 2dim array of all methods
         */
        public static function filterMethods( $public_list        //PHP Class list
        , $allMethodsArray    //reflectionObject list
        )
        {
            $editedArray = array() ;
            $elements    = count($public_list);
            $j           = 0 ;

            /* Check every public method ....                                      */
            for ( $i = 0 ; $i < $elements ; $i++ ) {
                /*  ... has a reflection object ....                               */
                foreach ( $allMethodsArray as $allMethod ) {
                    $objArray = get_object_vars($allMethod);

                    /* ... and copy the object when it does                        */

                    if ( strcmp($objArray[ 'name' ], $public_list[ $i ]) === 0 ) {
                        $editedArray[ $j++ ] = $allMethod ;

                    }/* End it does                                                */

                }/* End each reflection object                                     */

            }/* end all public methods                                             */

            return $editedArray ;

        }/* End filter methods function                                            */

        private static function _getXmldas($class_name, $namespace_uri)
        {
            // TODO examine this code again
            // one might imagine that what is wanted is a map:
            // array(namespace => Set Of xsd)
            // but this is not what we have
            // Code Analyser correctly picks up a number
            // of strangenesses
            $xsds   = self::_getAllXsds($class_name);
            $xmldas = SDO_DAS_XML::create();
            foreach ($xsds as $index => $xsds) {
                list($namespace, $xsdfile) = $xsds;
                if (SCA_Helper::isARelativePath($xsdfile)) {
                    $xsd = SCA_Helper::constructAbsolutePath($xsdfile, $class_name);
                    $xmldas->addTypes($xsd);
                }
            }
            return $xmldas;
        }

        private static function _getAllXsds($class_name)
        {
            $instance = new $class_name;
            $reader   = new SCA_AnnotationReader($instance);
            $xsds     = $reader->reflectXsdTypes($instance);
            return    $xsds;
        }

    }/* End SCA_Helper class                                                       */

}/* End instance check                                                             */

?>