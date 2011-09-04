<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                         |
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
$Id: SCA_AnnotationReader.php 254122 2008-03-03 17:56:38Z mfp $
*/

require 'SCA/SCA_ServiceDescription.php';
require 'SCA/SCA_CommentReader.php';
require 'SCA/SCA_Exceptions.php';
require 'SCA/SCA_Helper.php';

if ( ! class_exists('SCA_AnnotationReader', false)) {
    class SCA_AnnotationReader
    {

        protected $instance_of_the_component = null;
        protected $class_name = null;

        /**
         * Create the Annotation Reader for this component
         *
         * @param string $component_instance
         */
        public function __construct($component_instance_or_class_name)
        {
            if (gettype($component_instance_or_class_name) == 'string') {
                $this->class_name = $component_instance_or_class_name;
            } else {
                $this->instance_of_the_component = $component_instance_or_class_name;
            }

        }

        /**
         * Create the references for each annotation.
         *
         * @return array
         */
        public function reflectReferences()
        {

            $references = array();
            $reflection = new ReflectionObject($this->instance_of_the_component);
            $ref_props  = $reflection->getProperties();

            try {
                foreach ($ref_props as $ref_prop) {
                    $reader = new SCA_CommentReader($ref_prop->getDocComment());
                    if ($reader->isReference()) {
                        $ref = $reader->getReference();
                        if ($ref == "") {
                            throw new SCA_RuntimeException("Instance variable " . $ref_prop->getName() . " has a binding annotation with no following value");
                        }
                        $references[$ref_prop->getName()] = $ref;
                    } else {
                        if ($reader->hasBinding()) {
                            throw new SCA_RuntimeException("Instance variable " . $ref_prop->getName() . " appears to have a @binding but no @reference");
                        }
                    }
                }
            } catch( SCA_RuntimeException $se ) {
                throw $se ;
            } catch ( Exception $e ) {
                throw new SCA_RuntimeException($e->getMessage());
            }

            return $references;
        }

        /**
         * A new version of reflectReferences that reads all of the data that 
         * may appear 
         *
         * @return array
         */
        public function reflectReferencesFull()
        {

            $references = array();
            $reflection = new ReflectionObject($this->instance_of_the_component);
            $ref_props  = $reflection->getProperties();

            try {
                foreach ( $ref_props as $ref_prop ) {
                    $reader = new SCA_CommentReader($ref_prop->getDocComment());
                    if ($reader->isReference()) {
                        $reference_type = $reader->getReferenceFull();
                        $references[$ref_prop->getName()] = $reference_type;
                    }
                }
            } catch ( Exception $e ) {
                throw new SCA_RuntimeException(
                "The following error occured while examining the comment block for instance variable "
                . $ref_prop->getName() . ": " .
                $e->getMessage()) ;
            }

            return $references;
        }

        public function reflectXsdTypes()
        {

            $reflection = $this->__getReflection();
            $reader     = new SCA_CommentReader($reflection->getDocComment());
            $xsds       = $reader -> getXSDTypes(); // may be an empty array if none to be found
            return      $xsds;
        }

        private function __getReflection()
        {
            if ($this->instance_of_the_component !== null) {
                $reflection = new ReflectionObject($this->instance_of_the_component);
            } else {
                $reflection = new ReflectionClass($this->class_name);
            }
            return $reflection;
        }

        private function __getMethods()
        {
            if ($this->instance_of_the_component !== null) {
                return get_class_methods($this->instance_of_the_component);
            } else {
                return get_class_methods($this->class_name);
            }
        }

        private function __getClassName()
        {
            if ($this->instance_of_the_component !== null) {
                return get_class($this->instance_of_the_component);
            } else {
                return $this->class_name;
            }
        }

        public function reflectAllXsdTypes()
        {
            $xsd_types_array = null;

            // get xsds from the class level doc comment
            $reflection = $this->__getReflection();
            //            new ReflectionObject($this->instance_of_the_component);
            $reader          = new SCA_CommentReader($reflection->getDocComment());
            $xsd_types_array = $reader->getXSDTypes();

            // get xsds from any reference doc comments


            return $xsd_types_array;
        }

        /**
         * Get a ReflectionObject for a service interface from a 
         * ReflectionObject for a service implementation
         *
         * @param ReflectionObject $reflection The reflection for the service implementation
         * @param string $interface_name The service interface name
         * @return ReflectionObject The reflection for the service interface
         */
        private function __getInterfaceReflection($reflection, $interface_name) {
            foreach ($reflection->getInterfaces() as $interface_reflection) {
                if ($interface_reflection->getName() == $interface_name) {
                    return $interface_reflection;
                }
            }
            return null;
        }

        /**
         *
         * @return object SCA_ServiceDescription
         * @throws SCA_RuntimeException
         */
        public function reflectService()
        {

            $reflection = $this->__getReflection();

            $reader     = new SCA_CommentReader($reflection->getDocComment());
            $sca_name = $this->__getClassName();

            /**
             * Check that this object is defining a service that will be
             * exposed to callers
             * [TODO] - this check needs converting to if ( ! local service )
             */
            if (!$reader->isService()) {
                throw new SCA_RuntimeException("Class $sca_name does not contain an @service annotation.");
            }

            $service = new SCA_ServiceDescription();

            $interface_reflection = null;

            if ($reader->isService()) {
                $service->interface_name = $reader->getServiceInterface();


                // Get the ReflectionObject for any service interface
                $interface_reflection =
                $this->__getInterfaceReflection($reflection,
                $service->interface_name);

                if (is_null($interface_reflection)) {
                    $interface_methods = null;
                    if (!empty($service->interface_name)) {
                        throw new SCA_RuntimeException("Service interface {$service->interface_name} specified by @service does not match any interface implemented by {$sca_name}.php'.");
                    }
                } else {
                    $interface_methods = $interface_reflection->getMethods();
                }

                $service->binding = $reader->getBindings();
            }

            if (count($service->binding) == 0) {
                throw new SCA_RuntimeException("No valid @binding annotation could be found for '{$sca_name}.php'.");
            }

            $service->xsd_types = $this->reflectXsdTypes();

            /* Filter reflected method array to show 'public' functions only   */
            $service_methods =
            SCA_Helper::filterMethods($this->__getMethods(),
            $reflection->getMethods(),
            $interface_methods);

            $operations = array();
            $comment    = null ;

            /* Check the comment of each method to find any annotations so that
            * a wsdl can be generated from the .php file.
            */
            foreach ( $service_methods as $service_method ) {
                $methodAnnotations =
                SCA_AnnotationRules::createEmptyAnnotationArray();
                $comment = $service_method->getDocComment();

                /* When the method has a doc comment ....                     */
                if ( $comment != false ) {
                    $method_reader = new SCA_CommentReader($comment);

                    /* ... and the method a web service method ....           */
                    if ($method_reader->isWebMethod()) {
                        /* ... decode any method annotations.                 */
                        $methodAnnotations =
                        $method_reader->getMethodAnnotations();

                        if ( $methodAnnotations != null ) {
                            $thisElement = 0 ;

                            /* Each set of method annotations contain a set of 1
                            * or more  parameter annotations, and 1 return
                            * annotation.
                            */
                            foreach ( $methodAnnotations as $annotationSet ) {

                                // check that $annotationSet is not null to
                                // take account of the situation where no
                                // @return or @param annotation is specified

                                if ( $annotationSet ) {
                                    /* Clean off the dollar sign from the variable name, and do */
                                    /* a namespace check as appropriate.                        */
                                    foreach ( $annotationSet as $annotation ) {
                                        if ( strcmp($annotation['annotationType' ], SCA_AnnotationRules::PARAM) === 0 ) {
                                            if ( strpos($annotation[ 'name' ], SCA_AnnotationRules::DOLLAR) === false ) {
                                                throw new SCA_RuntimeException("Invalid syntax '{$annotation[ 'name' ]}' is not a php variable name");
                                            } else {
                                                $methodAnnotations[ 'parameters' ][ $thisElement ][ 'name' ] = trim($annotation[ 'name' ], SCA_AnnotationRules::DOLLAR);
                                            }/* End variable name check                             */

                                        }/* End parameter annotation test                         */

                                        /* When the array is formatted for SDO objects            */
                                        if ( array_key_exists('namespace', $annotation) ) {
                                            /* .... check that the xsd is defined for the namespace */
                                            if ( ! $this->_matchXsds($service->xsd_types, $annotation[ 'namespace' ]) ) {
                                                //TODO: noticed potential defect that if a method A has no @param, the @param of another method is being picked up instead and this error being thrown for method A

                                                throw new SCA_RuntimeException("Namespace defined in {$annotation[ 'annotationType' ]} not found in @types annotation: '{$annotation[ 'namespace' ]}'");

                                            }/* End xsd - namespace exists     */

                                        }/* End only for object descriptions   */

                                        $thisElement++ ; // next annotation
                                    }/* End all of the sub-sets                */
                                }
                            }/* End all of method annotation set               */
                        }/* End annotations check                              */
                    }/* End is a web method                                    */
                }/* End comment has value                                      */

                /* Save the annotation set indexed by the method name.         */
                $operations[$service_method->getName()] = $methodAnnotations ;

            }/* End every method                                               */

            $service->operations = $operations;
            $service->binding_config = $reader->getNameValuePairs();

            return $service;


        }

        /**
        * Check that the parameter, or return namespace definition for an object
        * is also in the xsd array.
        * 
        * Note that the array is an odd arrangement, it is really meant to represent
        * a set of (namespace -> xsd file). Because more than one xsd can contain
        * definitions for the same namespace it cannot be a simple associative array
        * (actually it could have done if we had thought to make it xsd->namespace :-()
        *     @types urn://namespace anything.xsd
        *     @types urn://namespace more.xsd
        * So it is actually an array (sequence) where each element is a two-element
        * array (another sequence) of namespace, then xsd name
        *
        * @param array $xsdArray
        * @param string $namespace
        * @return boolean
        */
        private function _matchXsds( $xsdArray, $namespace )
        {
            foreach ($xsdArray as $xsd_namespace_pair) {
                if (in_array($namespace, $xsd_namespace_pair)) {
                    return true;
                }
            }
            return false ;
        }
    }
}

?>
