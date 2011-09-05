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
$Id: SCA_TuscanyProxy.php 234945 2007-05-04 15:05:53Z mfp $
*/

/**
*
*/


class SCA_TuscanyProxy {

    private $containing_class_name          = null;
    private $reference_name                 = null;

    /**
      * Create the local proxy to the service given as an argument.
      *
      * @param string $absolute_path_to_component
      */
    public function __construct($reference_name)
    {
        $this->reference_name = $reference_name;

    }

    /**
     * The infrastructure provides us with an object that
     * represents the whole doc comment that this proxy
     * is configured with. We don't use it here at the
     * moment.
     */
    public function addReferenceType($reference_type)
    {
    }

    public function addContainingClassName($class_name)
    {
        $this->containing_class_name = $class_name;
    }

    /**
     * Invoke the method name in the target service.
     *
     * @param string $method_name
     * @param array $arguments
     * @return mixed
     */
    public function __call($method_name, $arguments)
    {
        // Invoke the Tuscany service

        $return = SCA_Tuscany::invoke($this->containing_class_name,
                                     $this->reference_name,
                                     $method_name,
                                     $arguments);
        return $return;
    }

    public function createDataObject($namespace_uri, $type_name )
    {
        try {
            return SCA_Helper::createDataObject(
                $namespace_uri, $type_name, $this->component_class_name);
        } catch( Exception $e ) {
            throw new SCA_RuntimeException($e->getMessage());
        }
        // following return logically unecessary but keeps the ZS code
        // analyzer happy
        return null;
    }

}

