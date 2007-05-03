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
$Id$
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
            $xsds       = $reader -> getXSDTypes(); // may be an emptry array if none to be found
            return      $xsds;
        }

        private function __getReflection()
        {
            if ($this->instance_of_the_component !== null) {
                $sca_name   = get_class($this->instance_of_the_component);
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

            if ($reader->isService())
                $service->binding = $reader->getBindings();

            if (count($service->binding) == 0) {
                throw new SCA_RuntimeException("No valid @binding annotation could be found for '{$sca_name}.php'.");
            }

            $service->xsd_types = $this->reflectXsdTypes();

            /* Filter reflected method array to show 'public' functions only   */
            $public_methods =
            SCA_Helper::filterMethods($this->__getMethods(),
            $reflection->getMethods());

            $operations = array();
            $comment    = null ;

            /* Check the comment of each method to find any annotations so that
            * a wsdl can be generated from the .php file.
            */
            foreach ( $public_methods as $public_method ) {
                $methodAnnotations =
                SCA_AnnotationRules::createEmptyAnnotationArray();
                $comment = $public_method->getDocComment();

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
                $operations[$public_method->getName()] = $methodAnnotations ;

            }/* End every method                                               */

            $service->operations = $operations;
            $service->binding_config = $reader->getNameValuePairs();

            return $service;


        }

        /**
        * Check that the parameter, or return namespace definition for an object
        * is also in the xsd array.
        *
        * @param array $xsdArray
        * @param string $namespace
        * @return boolean
        */
        private function _matchXsds( $xsdArray, $namespace )
        {
            foreach ($xsdArray as $index => $xsds) {
                if ( in_array($namespace, $xsds) ) {
                    return true;
                }
            }/* End all xsds                                                       */
            return false ;
        }/* End match xsds function                                                */

    }/* End SCA AnnotationReader Class                                             */

}/* End single instance check                                                      */

?>
