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
$Id: SCA_ReferenceType.php 234864 2007-05-03 18:23:57Z mfp $
*/

/**
 * A container for and information specified in the doc comment
 * of a reference
 */

if (!class_exists('SCA_ReferenceType', false)) {

    class SCA_ReferenceType
    {
        private $binding_type;
        private $binding;
        private $types;
        private $class_name;
        private $binding_config;

        public function __construct()
        {
            $this->binding = null;
            $this->types   = null;
            $this->binding_type = null;
        }

        public function addClassName($class_name)
        {
            $this->class_name = $class_name;
        }

        public function getClassName()
        {
            return $this->class_name;
        }

        public function addTypes($types)
        {
            $this->types = $types;
        }

        public function getTypes()
        {
            return $this->types;
        }

        public function addBinding($binding)
        {
            $this->binding = $binding;
        }

        public function getBinding()
        {
            return $this->binding;
        }

        public function setBindingType($binding_type)
        {
            $this->binding_type = $binding_type;
        }

        /**
         * Set any other binding specific configuration (e.g. @id 12 would
         * result in an entry in binding config equivalent to array('id' => 12).
         *
         * @param array $binding_config
         */
        public function setBindingConfig($binding_config)
        {
            $this->binding_config = $binding_config;
        }

        public function getBindingConfig()
        {
            return $this->binding_config;
        }

        public function getBindingType()
        {
            return $this->binding_type;
        }

        public function getXmlDas()
        {
            $xsds   = $this->types;
            $xmldas = SDO_DAS_XML::create();
            foreach ($xsds as $index => $xsds) {
                list($namespace, $xsdfile) = $xsds;
                if (SCA_Helper::isARelativePath($xsdfile)) {
                    $xsd = SCA_Helper::constructAbsolutePath($xsdfile, $this->class_name);
                    $xmldas->addTypes($xsd);
                }
            }
            return $xmldas;
        }



    }/* End SCA_ReferenceType class */
}/* End instance check */
