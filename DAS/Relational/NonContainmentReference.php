<?php
/* 
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005.                                  |
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+
|                                                                      |
| Licensed under the Apache License, Version 2.0 (the "License"); you  |
| may not use this file except in compliance with the License. You may |
| obtain a copy of the License at                                      |
| http://www.apache.org/licenses/LICENSE-2.0                           |
|                                                                      |
| Unless required by applicable law or agreed to in writing, software  |
| distributed under the License is distributed on an "AS IS" BASIS,    |
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
| implied. See the License for the specific language governing         |
| permissions and limitations under the License.                       |
+----------------------------------------------------------------------+
| Author: Matthew Peters                                               |
+----------------------------------------------------------------------+
$Id$
*/

/**
 * Encapsulates one SDO non-containment reference
 * 
 */ 


class SDO_DAS_Relational_NonContainmentReference {

    // for the moment these instance variables are held exactly as the metadata. This may well change.

    private $type_name;
    private $property_name;
    private $to_type_name;

    public function __construct($type_name,$property_name,$to_type_name)
    {
        $this->type_name = $type_name;
        $this->property_name = $property_name;
        $this->to_type_name = $to_type_name;
    }

    public function getTypeName()
    {
        // for now return the name of the child
        return $this->type_name;
    }

    public function getPropertyName()
    {
        // for now return the name of the child
        return $this->property_name;
    }

    public function getToTypeName()
    {
        return $this->to_type_name;
    }


}

?>