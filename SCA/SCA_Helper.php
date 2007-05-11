<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006, 2007.                                   |
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
$Id$
*/

require "SCA/SCA_Exceptions.php";

if ( ! class_exists('SCA_Helper', false) ) {
    class SCA_Helper {

        private static $tmpdir;

        public static function guessClassName($class_file)
        {
            // TODO - How do we handle the case where the classname
            // follows PEAR coding standards? For the time being
            // just keep extending the class name with parts of the 
            // directory path until we find a class that exists or we 
            // run out of directory path
            
            $class_name = basename($class_file, '.php');
            if (class_exists($class_name)){
                return $class_name;
            }
            
            // walk back up the dir path trying to find a class
            // that has already been loaded
            $pear_class_name = $class_name;
            $class_file = str_replace('\\', '/', $class_file);
            $dir_array = explode('/', dirname($class_file));
            foreach( array_reverse($dir_array) as $dir){                     
                $pear_class_name = $dir . '_' . $pear_class_name;                           
                if (class_exists($pear_class_name)){
                    return $pear_class_name;
                }
            }
            
            // Don't throw an error if nothing is found as other code
            // in SCA does file inclusion in an attempt to resolve classes
            return $class_name;
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
            SCA::$logger->log("Entering");
            SCA::$logger->log("Looking for file that contains class=$class_name");           
            foreach (get_included_files() as $file) {
                // try to find an include using the full class name
                if (strcasecmp($class_name,basename($file, ".php")) === 0) {
                    SCA::$logger->log("found a match with file $file");
                    return $file;
                }
                
                // try to find an include using PEAR coding standards. In this
                // case a class name A_Class_Name is considered to be found in the
                // file A/Class/Name.php   
                
                // TODO - I'm cheating here as I'm only checking the last part
                // of the PEAR class name with the basename of the file. I could
                // pick up the wrong filename if there is another file with the 
                // same name in a directory that doesn't match the PEAR classname
                // need to walk back through the file name checking that the 
                // directory path matched as well. This is just a test to 
                // see if I have the right idea about PEAR class names so leave
                // this until later. 
                $parts = explode('_', $class_name);
                $pear_class_name = array_pop($parts);
                if ($pear_class_name == basename($file, ".php")) {
                    SCA::$logger->log("found a match with file $file");                
                    return $file;
                }
            }

            throw new SCA_RuntimeException("Unable to determine which file contains class $class_name \n");
        }

        public static function checkSdoExtensionLoaded()
        {
            $extensionArray = get_loaded_extensions();

            if ( ! (in_array('sdo', $extensionArray)) ) {
                throw new SCA_RuntimeException("The SDO extension must be loaded");
            }

            $sdoversion = phpversion('sdo');

            if ( strcmp($sdoversion, '1.1.2') < 0 ) {
                throw new SCA_RuntimeException("The SDO extension must be 1.1.2 or later. You have " . $sdoversion);
            }
        }

        public static function checkSoapExtensionLoaded()
        {
            $extensionArray = get_loaded_extensions();

            if ( ! (in_array('soap', $extensionArray)) ) {
                throw new SCA_RuntimeException("The soap extension must be loaded");
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
            $xmldas     = self::getXmldas($class_name, $namespace_uri);
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
        public static function checkMethods($method, $class)
        {
            $return       = false ;
            $classMethods = get_class_methods($class);


            //SCA::$logger->log("methods found: ".print_r($classMethods,true)."class: ".get_class($class)."\n");


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

        /**
        * Find the system's temporary directory.
        * (The function formerly known as sys_get_temp_dir())
        *
        * @return string the path to the system temporary directory
        */
        public static function getTempDir()
        {
            if (empty(SCA_Helper::$tmpdir)) {
                $temp_file = tempnam(NULL, 'SCA');
                SCA_Helper::$tmpdir = dirname(realpath($temp_file));
                unlink($temp_file);
            }
            return SCA_Helper::$tmpdir;
        }

        public static function getXmldas($class_name, $namespace_uri)
        {
            SCA::$logger->log("Entering");
            SCA::$logger->log("class_name = $class_name, namespace_uri = $namespace_uri");
            $reader                     = new SCA_AnnotationReader($class_name);
            $namespace_and_xsd_pairs    = $reader->reflectXsdTypes(); // may be an empty array if none to be found
           
            if (count($namespace_and_xsd_pairs) == 0 ) {
                return SDO_DAS_XML::create();
            }
            
            foreach ($namespace_and_xsd_pairs as $index => $one_namespace_and_xsd_pair) {
                list($namespace, $xsdfile) = $one_namespace_and_xsd_pair;
                if (SCA_Helper::isARelativePath($xsdfile)) {
                    $xsd_list[] = SCA_Helper::constructAbsolutePath($xsdfile, $class_name);
                } else {
                    $xsd_list[] = $xsdfile;
                }
            }
            $xmldas = SDO_DAS_XML::create($xsd_list,$class_name);
            return $xmldas;
        }


        /**
         * Am repeating this code here (it's in the previous method also)
         * as we are seeing a number of different combinitations of
         * creating and XMLDAS based on the @type annotations in the
         * @Service comment or in the @Reference comment
         *
         * TODO - create a more complete model of the service in memory
         *        so that we can pick bits from it rather than having to
         *        rescan the class and regen it every time we need a bit
         */
        public static function getXmldasFormXsdArray($class_name, $xsds)
        {
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

        public static function getAllXsds($class_name)
        {
            $reader                     = new SCA_AnnotationReader($class_name);
            $namespace_and_xsd_pairs    = $reader->reflectXsdTypes();

            return $namespace_and_xsd_pairs;
        }

        /**
         * A problematic method for the time being as the
         * XML DAS doesn't provide an easy way of retrieving
         * a list of the types that it knows about. This is
         * slightly different from the one in SDO_TypeHandler in
         * that it finds top level types
         */
        const EOL = "\n" ;

        public static function getAllXmlDasTypes($xml_das)
        {
            $str   = $xml_das->__toString();
            $types = array();

            $line = strtok($str, self::EOL);

            while ($line !== false) {
                $trimmed_line = trim($line);
                $words        = explode(' ', $trimmed_line);

                if ( count($words) > 1 ) {
                    $namespace_and_type = $words[1];
                    $pos_last_colon     = strrpos($namespace_and_type, '#');
                    if ( $pos_last_colon !== false ) {
                        $namespace = substr($namespace_and_type, 0, $pos_last_colon);
                        $type      = substr($namespace_and_type, $pos_last_colon+1);

                        //don't include any SDO primitve types or the root type
                        if ( $namespace != "commonj.sdo" &&
                        $type      != "RootType"       ) {
                            $types[] = array($namespace, $type);
                        }
                    }
                }
                $line = strtok(self::EOL);
            }
            return $types;
        }

    }/* End SCA_Helper class                                                       */

}/* End instance check                                                             */

?>
