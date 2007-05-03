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

require "SCA/SCA_Exceptions.php" ;
require "SCA/SCA_AnnotationReader.php";
require "SCA/SCA_Helper.php" ;
require "SCA/SCA_LogFactory.php" ;
require "SCA/SCA_BindingFactory.php" ;
require "SCA/SCA_HttpHeaderCatcher.php";

/* TODO remove this once the Tuscany binding is converted to
* the pluggable model
*/
require "SCA/Bindings/tuscany/SCA_TuscanyProxy.php";

/* Service Component Architecture class                                       */
if (! class_exists('SCA', false)) {
    class SCA
    {
        const DEBUG = false;
        public static $logger;
        public static $xml_das_array  = array() ;

        public static $http_header_catcher = null;
        public static function sendHttpHeader($header)
        {
            if (self::$http_header_catcher === null) {
                header ($header);
            } else {
                self::$http_header_catcher->catchHeader($header);
            }
        }

        public static function setHttpHeaderCatcher($catcher)
        {
            self::$http_header_catcher = $catcher;
        }

        // When set true this flag indicates that SCA is being used
        // as an embedded component of the Tuscany C++ SCA runtime
        // and it affects how references are created on services
        private static $is_embedded = false;

        public static function setIsEmbedded($is_embedded)
        {
            self::$is_embedded = $is_embedded;
        }
        public static function initComponent($calling_component_filename)
        {

            //Create the logging mechanism
            self::$logger = SCA_LogFactory::create();

            // Turn on logging here by removing the comment from the following line
//                 self::$logger->startLog();

            self::$logger->log('Entering');
            self::$logger->log("Called from $calling_component_filename");

            if (isset($_SERVER['HTTP_HOST']))
            self::$logger->log('$_SERVER[\'HTTP_HOST\'] = ' .  $_SERVER['HTTP_HOST'] );

            if (isset($_SERVER['REQUEST_METHOD']))
            self::$logger->log('$_SERVER[\'REQUEST_METHOD\'] = ' .  $_SERVER['REQUEST_METHOD'] );

            if (isset($_SERVER['CONTENT_TYPE']) )
            self::$logger->log('$_SERVER[\'CONTENT_TYPE\'] = ' .  $_SERVER['CONTENT_TYPE'] );

            // contains the X.wsdl in http://..../X.php/X.wsdl
            if (isset($_SERVER[ 'PATH_INFO' ]))
            self::$logger->log('$_SERVER[\'PATH_INFO\'] = ' .  $_SERVER['PATH_INFO'] );

            if (isset($_SERVER[ 'PHP_SELF' ]))
            self::$logger->log('$_SERVER[\'PHP_SELF\'] = ' .  $_SERVER['PHP_SELF'] );

            if (isset($_SERVER[ 'REQUEST_URI' ]))
            self::$logger->log('$_SERVER[\'REQUEST_URI\'] = ' .  $_SERVER['REQUEST_URI'] );

            if (isset($_GET[ 'wsdl' ]))
            self::$logger->log('$_GET[\'wsdl\'] = ' .  $_GET['wsdl'] );

            /**
             * The instance check around the class - if (!class_exists... -  
             * makes sure that we get called here once and once only in any instance 
             * of php - i.e. by the first non-SCA client to include SCA, or the target
             * component in a web request.
             * 
             * There are three different ways we can find ourselves here.
             * 1. We have been included by a non-SCA client script. It is presumably
             *    later going to call getService() and/or createDataObject().
             * 2. We are the target of an HTTP request for WSDL, SMD, etc. i.e. a service file
             * 3. We are the target of a web request of some sort: WS, JSON, etc.
             * 
             * How do we distinguish these to do the right thing?
             * 1. Generate a class name from the name of the including file and see 
             *    if it exists. If not, then we are in a plain old client script. 
             *    If the class does exist but doesn't have @service then it is still 
             *    just a plain old client script. 
             * 2. This is a request for a service file if we are the target of an 
             *    HTTP request and we have the expected ?wsdl, ?smd etc. on the URL
             * 3. Consider this is a web request otherwise, since we have been included
             *    
             * We would get caught out if a non-SCA script were simply to simply 
             * to include a component rather than including SCA and using getService 
             * to get a proxy to it.
             */

            if (SCA::_includedByAClientScriptThatIsNotAComponent($calling_component_filename)) {
                SCA::$logger->log('included by a client script that is not a component');
                return;
            }

            $service_description = self::constructServiceDescription($calling_component_filename);
            if ( isset( $_SERVER['HTTP_HOST'] ) ) {
                $http_host = $_SERVER['HTTP_HOST'];
            } else {
                $http_host = "localhost";
            }
            $service_description->script_name = $_SERVER['SCRIPT_NAME'];
            $service_description->http_host   = $http_host;

            $class_name = SCA_Helper::guessClassName($calling_component_filename);

            foreach ($service_description->binding as $binding_string) {

                SCA::$logger->log("Applying tests for a $binding_string binding");
                $request_tester = SCA_Binding_Factory::createRequestTester($binding_string);
                if ($request_tester->isServiceDescriptionRequest($calling_component_filename)) {
                    SCA::$logger->log("The request is a service description request for $binding_string");
                    $service_description_generator = SCA_Binding_Factory::createServiceDescriptionGenerator($binding_string);
                    $service_description_generator->generate($service_description);
                    SCA::$logger->log('After having generated service description');
                    return;
                }
                if ($request_tester->isServiceRequest($calling_component_filename)) {
                    SCA::$logger->log("The request is a service request for $binding_string");
                    $service_request_handler = SCA_Binding_Factory::createServiceRequestHandler($binding_string);
                    $service_request_handler->handle($calling_component_filename, $service_description);
                    SCA::$logger->log('After having handled service request');
                    return;
                }
            }

            /**
              * There are other reasons you can get to here - a component loaded
              * locally, for example, or loaded as a result of a SOAP request
              * but some other component is the real destination.
              * None of them are errors though, so nothing needs to be done.
              */
            self::$logger->log('Request was not ATOM, JSON, SOAP, or a request for a .smd or .wsdl file.');
        }

        private static function _includedByAClientScriptThatIsNotAComponent($calling_component_filename)
        {
            $class_name = SCA_Helper::guessClassName($calling_component_filename);
            if (!class_exists($class_name)) {
                return true;
            }

            if (class_exists($class_name)) {
                $reflection = new ReflectionClass($class_name);
                $reader     = new SCA_CommentReader($reflection->getDocComment());
                if (!$reader->isService()) {
                    return true;
                }
            }
            return false;
        }

        /**
         * This is where decisions about what type of service is to be made, 
         * are made.
         *
         * @param string $target
         * @return proxy
         * @throws SCA_RuntimeException
         */
        public static function getService($target, $type = null, $binding_config = null)
        {
            self::$logger->log("Entering");
            self::$logger->log("Target is $target , Type is $type");

            // automatically create a tuscany proxy if SCA is embedded in tuscany C++ SCA
            // there isn;t really a sound reason for doing this but the following
            // path manipulation code crashes with php running in embedded mode.
            // needs further investigation
            if (self::$is_embedded ) {
                return new SCA_TuscanyProxy($target);
            }
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

            $immediate_caller_directory = '';

            SCA::$logger->log("immediate caller = $immediate_caller_filename");

            if (SCA_Helper::isARelativePath($target)) {
                $immediate_caller_directory = dirname($immediate_caller_filename);
                $absolute_path = realpath("$immediate_caller_directory/$target");
                if ($absolute_path === false) {
                    $msg = "file '$immediate_caller_directory/$target' could not be found";
                    throw new SCA_RuntimeException($msg);
                }
                $target = $absolute_path;
            }

            /* If the target is a local file, check it exists      */
            if (!strstr($target, 'http:') && !strstr($target,'https:')) {
                if (!file_exists($target)) {
                    $msg = "file '$target' could not be found";
                    throw new SCA_RuntimeException($msg);
                }
            }


            // set up the type in the case where getService has been
            // call from a client script and the type has defaulted to null
            if ($type == null) {
                if (strstr($target, '.wsdl') == '.wsdl'
                || strstr($target, '?wsdl') == '?wsdl') { // end with .wsdl or ?wsdl
                    SCA::$logger->log("Inferring from presence of .wsdl or ?wsdl that a soap proxy is required for this target.");
                    $type = 'soap';
                } else if (strstr($target, '.smd') == '.smd' || strstr($target,'?smd') == '?smd') {
                    SCA::$logger->log("Inferring from presence of .smd or ?smd that a jsonrpc proxy is required for this target.");
                    $type = 'jsonrpc';
                } else if (strstr($target, '.php') == '.php') { // .php on the end
                    SCA::$logger->log("A local proxy is required for this target.");
                    $type = 'local';
                    // better be local
                    if (strstr($target, 'http:') || strstr($target,'https:')) {
                        throw new SCA_RuntimeException("The target $target appears to be for a remote component, but needs a binding to be specified");
                    }
                }
            } else {
                // type remains null and the error message at the bottom will
                // kick in
            }


            if (isset($type) && $type !== null) {
                if (!isset($binding_config)) {
                    $binding_config = null;
                }
                SCA::$logger->log("A $type proxy is required for target $target.");
                return SCA_Binding_Factory::createProxy($type, $target,
                $immediate_caller_directory, $binding_config);
            }

            $file = basename($target);
            $dir  = dirname($target);
            $msg  = "The right binding to use could not be inferred from the target {$target}. The binding must be specified as the second argument to getService()." ;

            throw new SCA_RuntimeException($msg);

            self::$logger->log("Exiting");
        }

        /**
         * THE OLD VERSION OF createInstanceAndFillInReferences(). INCLUDES
         * FUNCTIONALITY REQUIRED WHEN SCA IS RUNNING EMBEDDED IN TUSCANY SCA C++
         *
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
            self::$logger->log("Entering");
            self::$logger->log("Class name of component to instantiate is $class_name");
            $instance   = new $class_name;
            $reader     = new SCA_AnnotationReader($instance);
            $references = $reader->reflectReferencesFull($instance);
            self::$logger->log("There are " . count($references) . " references to be filled in");
            $reflection = new ReflectionObject($instance);
            foreach ($references as $ref_name => $ref_type) {
                $ref_value = $ref_type->getBinding();
                self::$logger->log("Reference name = $ref_name, binding = $ref_value");
                $reference_proxy = null;
                if (self::$is_embedded) {
                    $reference_proxy = new SCA_TuscanyProxy($ref_value);
                } else {
                    if (SCA_Helper::isARelativePath($ref_value)) {
                        $ref_value = SCA_Helper::constructAbsolutePath($ref_value,
                        $class_name);
                    }
                    $reference_proxy = SCA::getService($ref_value);
                }

                $prop            = $reflection->getProperty($ref_name);

                // add the reference information to the proxy
                // this is added just in case there are any
                // extra types specified in the doc comment
                // for this reference
                $ref_type->addClassName($class_name);
                $reference_proxy->addReferenceType($ref_type);

                $prop->setValue($instance, $reference_proxy); // NB recursion here
            }

            self::$logger->log("Exiting");
            return $instance;
        }


        /**
         * Instantiate the component
         */
        public static function createInstance($class_name)
        {
            self::$logger->log("Entering");
            self::$logger->log("Class name of component to instantiate is $class_name");
            $instance   = new $class_name;
            return      $instance;
        }

        /**
         * Examine the annotations, find the dependencies,
         * call getService to create a proxy for each one, and assign to the
         * instance variables. The call(s) to getService may recurse back through here
         * if those dependencies also have dependencies
         *
         * @param  instance of a class
         * @return class instance
         * @throws SCA_Exeption
         */
        public static function fillInReferences($instance)
        {
            self::$logger->log("Entering");
            // When I run PHP complains about converting $instance to a string
            //self::$logger->log("The instance of the class that will be used in the construction of the annotation reader: $instance");
            $reader     = new SCA_AnnotationReader($instance);
            $references = $reader->reflectReferencesFull();
            self::$logger->log("Number of references to be filled in: ".count($references));
            $reflection = new ReflectionObject($instance);

            foreach ($references as $ref_name => $ref_type) {
                self::$logger->log("Reference name = $ref_name, ref_type = " . print_r($ref_type,true));
                $ref_value = $ref_type->getBinding();
                if (SCA_Helper::isARelativePath($ref_value)) {
                    $ref_value = SCA_Helper::constructAbsolutePath($ref_value,
                    get_class($instance));
                }
                $prop            = $reflection->getProperty($ref_name);
                $reference_proxy = SCA::getService($ref_value,
                $ref_type->getBindingType(),
                $ref_type->getBindingConfig());

                // add the reference information to the proxy
                // this is added just in case there are any
                // extra types specified in the doc comment
                // for this reference
                $ref_type->addClassName(get_class($instance));
                $reference_proxy->addReferenceType($ref_type);

                $prop->setValue($instance, $reference_proxy); // NB recursion here
            }

            self::$logger->log("Exiting");
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

                $service_description->class_name      = $class_name;
                $service_description->realpath        = realpath($class_file);
                $service_description->targetnamespace = "http://$class_name" ;

            } else {
                throw new SCA_RuntimeException("Invalid Classname: $class_name");
            }

            return $service_description;
        }


        /**
         * This function can be called directly by a component to
         * create a dataobject from the namespaces defined in the @types annotations.
         *
         * @param       string  $namespace_uri     Namespace identifying the xsd
         * @param       string  $type_name         Element being reference in the xsd
         * @return      object                     Empty Data Object structure
         */
        public static function createDataObject( $namespace_uri, $type_name )
        {
            $xmldas     = null ;
            // Find out who/what called this function so that the type annotations
            // that define the xml used to create a 'das' can be scanned.
            $backtrace  = debug_backtrace();
            $caller     = $backtrace[0];
            $filepath   = $caller['file'];
            // the key to the xmldas array.
            $keyname    = md5( serialize( $filepath ) ) ;

            // Check if there is a matching xsd in the xmldas array
            if ( array_key_exists( $keyname, self::$xml_das_array ) ) {
                $xmldas = self::$xml_das_array[ $keyname ] ;

            } else {
                // The trap will only trigger if the Annotations cannot be found
                // normally this is because a SCA Client Component has incorrectly
                // attempted to use this method, rather than the 'createDataObject'
                // method of either the 'Proxy, or LocalProxy.
                try {
                    $class_name = SCA_Helper::guessClassName( $filepath );
                    $xmldas = SCA_Helper::getXmldas( $class_name, null ) ;
                    self::$xml_das_array[ $keyname ] = $xmldas ;

                } catch( ReflectionException $e ) {
                    $msg =  $e->getMessage() ;
                    throw new SCA_RuntimeException( "SCA::createdDataObject can only be used from a Service Component" );
                }
            }

            return $xmldas->createDataObject($namespace_uri, $type_name);

        }
        //        public static function createDataObject( $namespace_uri, $type_name)
        //        {
        //            /* we have been called directly by SCA::createDataObject(), find calling class */
        //            $backtrace  = debug_backtrace();
        //            $caller     = $backtrace[0];
        //            $file_name  = $caller['file'];
        //            $class_name = SCA_Helper::guessClassName($file_name);
        //
        //            return SCA_Helper::createDataObject($namespace_uri,
        //            $type_name, $class_name);
        //        }

    }/* End SCA Class                                                         */


    /**
    * Check that the correct extensions have been loaded before starting, and
    * initialise the sca class to the script file including SCA.
    */
    SCA_Helper::checkSdoExtensionLoaded();

    $backtrace        = debug_backtrace();
    $immediate_caller = $backtrace[ 0 ][ 'file' ] ;

    SCA::initComponent($immediate_caller);

}/* End instance check                                                        */

?>
