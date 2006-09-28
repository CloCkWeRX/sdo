<?php
/* 
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005, 2006.                            |
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

require_once 'SDO/DAS/Relational/DataObjectHelper.php';
require_once 'SDO/DAS/Relational/DatabaseHelper.php';

class SDO_DAS_Relational_UpdateNonContainmentReferenceAction extends SDO_DAS_Relational_Action {
    
    private $object_model;
    private $from_who;       // the object containing the n-c-ref 
    private $property_name;  // the name of the property which is the n-c-ref
    private $who_to;         // the object it points to
    
    public function __construct($object_model, $from_who, $property_name, $who_to) 
    {
        $this->object_model = $object_model;
        $this->from_who = $from_who;
        $this->property_name = $property_name;
        $this->who_to = $who_to;
    }
    
    public function execute($dbh) 
    {   
        $pk_from    = SDO_DAS_Relational_DataObjectHelper::getPrimaryKeyFromDataObject($this->object_model, $this->from_who);
        $pk_to      = SDO_DAS_Relational_DataObjectHelper::getPrimaryKeyFromDataObject($this->object_model, $this->who_to);
        $type_name  = SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->from_who);
        $name_of_the_pk_column = $this->object_model->getPropertyRepresentingPrimaryKeyFromType($type_name);
        $stmt   =  "UPDATE $type_name SET $this->property_name = ? WHERE $name_of_the_pk_column = ?" ;
        $value_list = array($pk_to, $pk_from);
        SDO_DAS_Relational_DatabaseHelper::executeStatementTestForCollision($dbh, $stmt, $value_list);
    }
    
    public function toString() 
    {
        return '[UpdateNonContainmentReference: ' . $this->type_name . ': ' . $this->property_name . ']';
    }
}

?>
