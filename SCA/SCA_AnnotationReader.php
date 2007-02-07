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

require 'SCA/SCA_CommentReader.php';
require 'SCA/SCA_RuntimeException.php';
require 'SCA/SCA_Helper.php';

if ( ! class_exists('SCA_AnnotationReader', false) ) {
    class SCA_AnnotationReader
    {

        protected $instance_of_the_component;

        /**
         * Create the Annotation Reader for this component
         *
         * @param string $component_instance
         */
        public function __construct($component_instance)
        {
            $this->instance_of_the_component = $component_instance;
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

        public function reflectXsdTypes()
        {

            $reflection = new ReflectionObject($this->instance_of_the_component);
            $reader     = new SCA_CommentReader($reflection->getDocComment());
            $xsds       = $reader -> getXSDTypes();
            return      $xsds;
        }

        /**
         * Checks the doc comment of each method to find annotations that can be used
         * to generated a WSDL from the php script.
         *
         * @return array  (Containing the annotations in the doc comment.)
         * @throws SCA_RuntimeException
         */
        public function reflectService()
        {

            $service = null ;

            $sca_name   = get_class($this->instance_of_the_component);
            $reflection = new ReflectionObject($this->instance_of_the_component);
            $reader     = new SCA_CommentReader($reflection->getDocComment());

            /**
             * Check that this object is defining a web service that will require a 
             * WSDL file to be generated.
             */
            if (!$reader->isService()) {
                throw new SCA_RuntimeException("Class $sca_name does not contain an @service annotation.");
            }
            
            if ($reader->isWebService()) {

                $service              = array();
                $service['binding']   = 'ws';
                $service['xsd_types'] = $this->reflectXsdTypes();

                /* Filter reflected method array to show 'public' functions only   */
                $public_methods =
                SCA_Helper::filterMethods(get_class_methods($this->instance_of_the_component),
                $reflection->getMethods());

                $operations = array();
                $comment    = null ;

                /* Check the comment of each method to find any annotations so that
                * a wsdl can be generated from the .php file.
                */
                foreach ( $public_methods as $public_method ) {
                    $methodAnnotations =
                    SCA_AnnotationRules::createEmptyAnnotationArray();
                    $comment           = $public_method->getDocComment();

                    /* When the method has a doc comment ....                             */
                    if ( $comment != false ) {
                        $method_reader = new SCA_CommentReader($comment);

                        /* ... and the method a web service method ....                     */
                        if ($method_reader->isWebMethod()) {
                            /* ... decode any method annotations.                             */
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
                                                if ( ! $this->_matchXsds($service[ 'xsd_types' ], $annotation[ 'namespace' ]) ) {
                                                    throw new SCA_RuntimeException("Namespace defined in {$annotation[ 'annotationType' ]} not found in @type annotation: '{$annotation[ 'namespace' ]}'");

                                                }/* End xsd - namespace exists         */

                                            }/* End only for object descriptions       */

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

                if ( is_array($service) ) {
                    $service['operations'] = $operations;
                }

            } else {
                throw new SCA_RuntimeException("You need to include '@binding.ws' if you want to use '{$sca_name}.php' as a web service.\nRefer to the User-Spec");

            }/* End this is not bound to a 'ws'                                    */
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