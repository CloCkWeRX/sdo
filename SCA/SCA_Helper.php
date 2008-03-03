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
            if (class_exists($class_name, false)){
                return $class_name;
            }

            // walk back up the dir path trying to find a class
            // that has already been loaded
            $pear_class_name = $class_name;
            $class_file = str_replace('\\', '/', $class_file);
            $dir_array = explode('/', dirname($class_file));
            foreach( array_reverse($dir_array) as $dir){
                $pear_class_name = $dir . '_' . $pear_class_name;
                if (class_exists($pear_class_name, false)){
                    return $pear_class_name;
                }
            }

            // Don't throw an error if nothing is found as other code
            // in SCA does file inclusion in an attempt to resolve classes
            return $class_name;
        }

        /**
         * Takes the configuration passed into a binding and looks for a 'config'
         * entry which should point to an ini file with additiional configuration.
         * Loads the ini file configuration and then 'overlays' the other
         * configuration values
         *
         * @param array $binding_config The unmerged binding configuration
         * @param string $base_path_for_relative_paths Used to locate the ini file.
         * @return string The merged config
         */
        public static function mergeBindingIniAndConfig($binding_config, $base_path_for_relative_paths) {
            // Merge values from a config file and annotations
            if (key_exists('config', $binding_config)) {

                if (SCA_Helper::isARelativePath($binding_config['config'])) {
                    $msg = $binding_config['config'];
                    if (!empty($base_path_for_relative_paths)) {
                        $msg = $base_path_for_relative_paths . '/' . $msg;
                    }
                    $absolute_path = realpath($msg);
                }
                else {
                    $absolute_path = $binding_config['config'];
                }
                if ($absolute_path === false) {
                    throw new SCA_RuntimeException("File '$msg' could not be found");
                }
                SCA::$logger->log('Loading external configuration from: ' . $absolute_path);
                $config = @parse_ini_file($absolute_path, true);
                $binding_config = array_merge($config, $binding_config);
            }
            return $binding_config;           
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

        public static function constructAbsoluteTarget($target, $base_path_for_relative_paths) {
            $absolute_target = $target;
            if (SCA_Helper::isARelativePath($target)) {
                $absolute_path = realpath("$base_path_for_relative_paths/$target");
                if ($absolute_path === false) {
                    $msg = "file '$base_path_for_relative_paths/$target' could not be found";
                    throw new SCA_RuntimeException($msg);
                }
                $absolute_target = $absolute_path;
            }

            /* If the target is a local file, check it exists      */
            if (!strstr($absolute_target, 'http:')
                 && !strstr($absolute_target,'https:')) {
                if (!file_exists($absolute_target)) {
                    $msg = "file '$absolute_target' could not be found";
                    throw new SCA_RuntimeException($msg);
                }
            }
            
            return $absolute_target;
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
         * WSDL or other service description only public methods may be used, 
         * so using the get_class_methods call ( which only lists public methods )
         * the filter removes any protected, or private methods.
         * Optionally, a service interface may be specified to further subset 
         * the methods exposed on the service.  This is done using the third
         * interfaceMethods argument.
         *
         * @param  array $public_list     1dim array of public methods
         * @param  array $allMethodsArray Array of ReflectionMethod objects
         * @param  array $interfaceMethods Array of ReflectionMethod objects
         * @return array Modified array of ReflectionMethod objects
         */
        public static function filterMethods($public_list,
        $allMethodsArray,
        $interfaceMethods=null)
        {
            $editedArray = array() ;

            // Build up an array of ReflectionMethod objects from the allMethods
            // array, that correspond to those in the interfaceMethods array.
            // We need the implementation methods because their docComments
            // are used to generate service descriptions.
            // TODO: allow docComments to come from the interface.
            if ($interfaceMethods != null) {
                foreach ($interfaceMethods as $interfaceMethod) {
                    if ((substr($interfaceMethod->name, 0, 2) != '__')) {
                        foreach ($allMethodsArray as $implementationMethod) {
                            if ($interfaceMethod->name == $implementationMethod->name) {
                                $editedArray[] = $implementationMethod;
                            }
                        }
                    }
                }
            }
            else {
                $elements    = count($public_list);
                $j           = 0 ;

                /* For all of the public methods of the service class */
                for ( $i = 0 ; $i < $elements ; $i++ ) {

                    /* Ignore the method if it's a magic method as defined by */
                    /* http://www.php.net/manual/en/language.oop5.magic.php   */
                    if ((substr($public_list[ $i ], 0, 2) != '__')) {

                        /*  Check each method has a reflection object */
                        foreach ( $allMethodsArray as $allMethod ) {

                            $objArray = get_object_vars($allMethod);

                            /* copy the relfection object to the filitered list if it does */
                            if ( strcmp($objArray[ 'name' ], $public_list[ $i ]) === 0 ) {
                                $editedArray[ $j++ ] = $allMethod ;

                            }
                        }
                    }
                }/* end all public methods */

            }

            return $editedArray ;

        }/* End filter methods function */

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
            $xmldas = SDO_DAS_XML::create($xsd_list);
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
         * slightly different from the one in Mapper in
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
        
        public function xmlToSdo($xml_das, $xml_string)
        {
            try{
                $doc = $xml_das->loadString($xml_string);
                $ret = $doc->getRootDataObject();
                return $ret;
            } catch( Exception $e ) {
                SCA::$logger->log("Exception converting XML to SDO: ".$e->getMessage()."\n");
                return $e->getMessage();
            }
        }

        public function sdoToXml($xml_das, $sdo)
        {
            try{
                $type       = $sdo->getTypeName();
                $xdoc       = $xml_das->createDocument('', $type, $sdo);
                $xml_string = $xml_das->saveString($xdoc,2);
                return  $xml_string;
            } catch (Exception $e) {
                SCA::$logger->log("Exception converting SDO to XML: ".$e->getMessage()."\n");
                return $e->getMessage();
            }
        }        

        private static $CD_START = '<![CDATA[';
        private static $CD_END = ']]>';
        
        /**
         * Escapes HTML special chars, excluding data in CDATA sections,
         * and avoiding double-escaping (that is, &amp; does NOT become &amp;amp;)
         */
        public static function encodeXmlData($raw = "")
        {

            if (!preg_match('/[&\'\"\<\>]/', $raw))
                return $raw;

            $out = "";
            $remaining = $raw;
            while (($cdata_pos = strpos($remaining, CD_START)) !== FALSE) {
                $out .= htmlspecialchars(substr($remaining, 0, $cdata_pos), 
                        ENT_QUOTES, NULL, 0);
                $remaining = substr($remaining, $cdata_pos);
                $cd_end_pos = strlen(CD_END) + strpos($remaining, CD_END);
                $out .= substr($remaining, 0, $cd_end_pos);
                $remaining = substr($remaining, $cd_end_pos);
            }
            $out .= htmlspecialchars($remaining, ENT_QUOTES, NULL, 0);
            return $out;
        }
        
    }/* End SCA_Helper class                                                       */

}/* End instance check                                                             */

?>
