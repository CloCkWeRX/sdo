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

/**
 * Purpose:
 * To ensure that SCA components are initialised and processed, and requests 
 * to them are correctly handled.
 *
 * Public Methods:
 *
 * initComponent()
 * This method is used to determine which of the three reasons why the SCA 
 * component calling us has been invoked. These reasons are:
 * 1) a POST request with a SOAP request was made to the component
 * 2) a GET request for WSDL was made to the component
 * 3) the component was simply included as a component to be called locally.
 * If the request is one of 1) or 2) the requests are processed. If the 
 * component was included in another SCA Component no additional processing is 
 * required.
 *
 * getService()
 * createInstanceAndFillInReferences()
 * constructServiceDescription()
 * generateWSDL()
 * createDataObject()
 *
 * Private Methods:
 *
 * _isSoapRequest()
 * This method is used to determine whether a SOAP POST request was made to 
 * the component. Additionally, this method detects whether the request is 
 * one that has been passed on to another component.
 *
 * _wsdlRequested()
 * This is used to determine whether WSDL for the component was requested.
 *
 * _handleRequestForWSDL()
 * This method is used to handle the case where WSDL for the component was 
 * requested. It echos the WSDL, and caches it locally to a file, so ?wsdl is also
 * the way to refresh the cached copy
 *
 * _handleSoapRequest()
 * This method is used to handle the SOAP request.
 *
 *
 * convertedSoapFault()
 * This method is used to convert a SOAP Fault into the appropriate 
 * SCA Exception.
 *
 */

require "SCA/SCA_RuntimeException.php" ;
require "SCA/SCA_AnnotationReader.php";
require "SCA/SCA_LocalProxy.php";
require "SCA/SCA_SoapProxy.php";
require "SCA/SCA_ServiceWrapper.php";
require "SCA/SCA_GenerateWsdl.php" ;
require "SCA/SCA_Helper.php" ;
require "SCA/SDO_TypeHandler.php";

/* Service Component Architecture class                                       */
if (! class_exists('SCA', false)) {
    class SCA
    {

        public static function initComponent($calling_component_filename)
        {
            if (self::_isSoapRequest($calling_component_filename)) {
                self::_handleSoapRequest($calling_component_filename);
                return;
            }

            if (self::_wsdlRequested($calling_component_filename)) {
                self::_handleRequestForWSDL($calling_component_filename);
                return;
            }
            /**
              * There are other reasons you can get to here - a component loaded
              * locally, for example, or loaded as a result of a SOAP request
              * but some other component is the real destination.
              * None of them are errors though, so nothing needs to be done.
              */
        }

        private static function _isSoapRequest($calling_component_filename)
        {
            if (isset($_SERVER['HTTP_HOST'])) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $p1 = realpath($calling_component_filename);
                    $p2 = realpath($_SERVER['SCRIPT_FILENAME']);
                    if ($p1 == $p2)
                    return true;
                }
            }
            return false;
        }

        private static function _handleSoapRequest($calling_component_filename)
        {
            $wsdl_filename = str_replace('.php', '.wsdl',
            $calling_component_filename);
            
            if (!file_exists($wsdl_filename)) {
                file_put_contents($wsdl_filename,
                self::generateWSDL($calling_component_filename));
            }
            
            $handler = new SDO_TypeHandler("SoapServer");
            try {
                $handler->setWSDLTypes($wsdl_filename);
            } catch( SCA_RuntimeException $wsdlerror ) {
                echo $wsdlerror->exceptionString() . "\n" ;
            }

            if (SCA_Helper::wsdlWasGeneratedForAnScaComponent($wsdl_filename)) {
                $server = new SoapServer($wsdl_filename,
                array('typemap' => $handler->getTypeMap()));
            }
            $class_name    = SCA_Helper::guessClassName($calling_component_filename);
            $service_proxy = new SCA_ServiceWrapper($class_name, $wsdl_filename);
            $server->setObject($service_proxy);
            $server->handle();
        }

        private static function _wsdlRequested($calling_component_filename)
        {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                    $p1 = realpath($calling_component_filename);
                    $p2 = realpath($_SERVER['SCRIPT_FILENAME']);
                    if ($p1 == $p2 && isset($_GET['wsdl']))
                    return true;
                }
            }
            return false;
        }

        private static function _handleRequestForWSDL($calling_component_filename)
        {
            try
            {
                $str           = self::generateWSDL($calling_component_filename);
                $wsdl_filename = str_replace('.php', '.wsdl', 
                    $calling_component_filename);
                file_put_contents($wsdl_filename, $str);
                header('Content-type: text/xml');
                echo $str;
            }
            catch (SCA_RuntimeException $se )
            {
                echo $se->exceptionString() . "\n" ;
            }
        }

        public static function generateWSDL($class_file)
        {
            if ( isset( $_SERVER['HTTP_HOST'] ) ) {
                $http_host = $_SERVER['HTTP_HOST'];
            } else {
                $http_host = "localhost";
            }

            try {
                $service_description = 
                    self::constructServiceDescription($class_file);
                $service_description['script_name'] = $_SERVER['SCRIPT_NAME'];
                $service_description['http_host']   = $http_host;

                return SCA_GenerateWsdl::generateDocumentLiteralWrappedWsdl($service_description);
            } catch( SDO_DAS_XML_FileException $e) {
                throw new SCA_RuntimeException("{$e->getMessage()} in {$e->getFile()}");
            }
            return null; // this return is logically unecessary but is here to keep the code analyzer happy
        }

        /**
         * This is where decisions about what type of service is to be made, 
         * are made.
         *
         * @param string $target
         * @return proxy
         * @throws SCA_RuntimeException
         */
        public static function getService($target)
        {
            $backtrace                 = debug_backtrace();
            $immediate_caller_filename = $backtrace[0]['file'];
            if ($target === null) {
                $msg = "SCA::getService was called from $immediate_caller_filename with a null argument";
                throw new SCA_RuntimeException($msg);
            }
            if (strlen($target) == 0) {
                $msg = "SCA::getService was called from $immediate_caller_filename with an empty argument";
                throw new SCA_RuntimeException($msg);
            }

            if (SCA_Helper::isARelativePath($target)) {
                $immediate_caller_directory = dirname($immediate_caller_filename);
                $absolute_path              = 
                    realpath("$immediate_caller_directory/$target");
                if ($absolute_path === false) {
                    $msg = "file '$immediate_caller_directory/$target' could not be found";
                    throw new SCA_RuntimeException($msg);
                }
                $target = $absolute_path;
            }

            /* If the target does not begin with "http:" look for it locally */
            if (strpos($target, 'http:') !== 0) {
                if (!file_exists($target)) {
                    $msg = "file '$target' could not be found";
                    throw new SCA_RuntimeException($msg);
                }
            }

            if (strstr($target, '.wsdl')) {
                return new SCA_SoapProxy($target);
            } else if (strstr($target, '.php')) {
                return new SCA_LocalProxy($target);
            } else {
                $file = basename($target);
                $dir  = dirname($target);
                $msg  = "{$file}' in '{$dir}' has the incorrect extension - '.php' or '.wsdl' extensions only" ;
                throw new SCA_RuntimeException($msg);
            }
        }

        /**
         * Instantiate the component, examine the annotations, find the dependencies,
         * call getService to create a proxy for each one, and assign to the 
         * instance variables. The call(s) to getService may recurse back through here
         * if those dependencies also have dependencies
         * 
         * @param  name of the class
         * @return class instance
         * @throws SCA_Exeption
         */
        public static function createInstanceAndFillInReferences($class_name)
        {
            $instance   = new $class_name;
            $reader     = new SCA_AnnotationReader($instance);
            $references = $reader->reflectReferences($instance);
            $reflection = new ReflectionObject($instance);

            foreach ($references as $ref_name => $ref_value) {
                if (SCA_Helper::isARelativePath($ref_value)) {
                    $ref_value = SCA_Helper::constructAbsolutePath($ref_value,
                    $class_name);
                }
                $prop = $reflection->getProperty($ref_name);
                $prop->setValue($instance, SCA::getService($ref_value)); // NB recursion here
            }

            return $instance;
        }

        /**
         * Create an array containing the service descriptions from the annotations 
         * found in the class file.
         *
         * @param  object ( Class file containing the service annotations )
         * @return array  ( Containing the service decscriptions )
         * @throws SCA_RuntimeException ... when things go wrong
         */
        public static function constructServiceDescription( $class_file )
        {
            $class_name = SCA_Helper::guessClassName($class_file);

            if ( ! class_exists($class_name, false))
            // The code analyzer marks the following include with a variable name as
            // unsafe. It is safde, however as the class file name can only come from
            // a getService call or an annotation.
            include "$class_file";

            if ( class_exists($class_name) ) {
                $instance            = new $class_name;
                $reader              = new SCA_AnnotationReader($instance);
                $service_description = $reader->reflectService();

                $service_description['class_name']      = $class_name;
                $service_description['realpath']        = realpath($class_file);
                $service_description['targetnamespace'] = "http://$class_name" ;

            } else {
                throw new SCA_RuntimeException("Invalid Classname: $class_name");
            }

            return $service_description;
        }


        /**
        * This function can be called directly by a component to
        * create a dataobject from the namespaces defined in the @types annotations.
        *
        */
        public static function createDataObject( $namespace_uri, $type_name)
        {
            /* we have been called directly by SCA::createDataObject(), find calling class */
            $backtrace  = debug_backtrace();
            $caller     = $backtrace[0];
            $file_name  = $caller['file'];
            $class_name = SCA_Helper::guessClassName($file_name);

            return SCA_Helper::createDataObject($namespace_uri, 
                $type_name, $class_name);
        }

    }/* End SCA Class                                                         */


    /**
    * Check that the correct extensions have been loaded before starting, and
    * initialise the sca class to the script file including SCA.
    */
    SCA_Helper::checkExtensionsLoaded();

    $backtrace        = debug_backtrace();
    $immediate_caller = $backtrace[ 0 ][ 'file' ] ;

    SCA::initComponent($immediate_caller);

}/* End instance check                                                        */

?>