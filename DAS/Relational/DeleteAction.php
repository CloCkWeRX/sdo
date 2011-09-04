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
$Id: DeleteAction.php 220738 2006-09-28 19:25:00Z cem $
*/

/**
* Delete a row in the database
*/

require_once 'SDO/DAS/Relational/Action.php';
require_once 'SDO/DAS/Relational/ObjectModel.php';
require_once 'SDO/DAS/Relational/DataObjectHelper.php';
require_once 'SDO/DAS/Relational/DatabaseHelper.php';


class SDO_DAS_Relational_DeleteAction extends SDO_DAS_Relational_Action {

    private $object_model;
    private $do;
    private $settings_for_where_clause = array(); // computed in constructor, settings to be tested in WHERE clause
    private $spawned_actions = array();
    private $stmt = '';
    private $value_list = array();


    public function __construct($object_model,$do, $old_values)
    {
        $this->object_model = $object_model;
        $this->do = $do;

        $type = SDO_DAS_Relational_DataObjectHelper::getApplicationType($do);

        $old_values_of_any_changed_properties = SDO_DAS_Relational_SettingListHelper::getSettingsAsArray($old_values);
        foreach($this->do as $prop => $value) {
            if ($this->object_model->isContainmentReferenceProperty($type, $prop)) {
                // We ignore containment references - any changes to them appear elsewhere in the C/S
                continue;
            }
            if (array_key_exists($prop, $old_values_of_any_changed_properties)) {
                $this->settings_for_where_clause[$prop] = $old_values_of_any_changed_properties[$prop];
            } else {
                $this->settings_for_where_clause[$prop] = $value;
            }
        }
        if (count($this->settings_for_where_clause) > 0) {
            $this->convertNonContainmentReferencesFromObjectToPK();
            $this->stmt = $this->toSQL($this->settings_for_where_clause);
            $this->value_list = $this->buildValueList();
        }

        if (SDO_DAS_Relational::DEBUG_BUILD_PLAN ) {
            echo "adding update to plan for type $type\n";
        }
    }

    public function execute($dbh)
    {
        if ($this ->stmt != '') {
            SDO_DAS_Relational_DatabaseHelper::executeStatementTestForCollision($dbh, $this->stmt, $this->value_list);
        }
        return $this->spawned_actions;
    }

    public function convertNonContainmentReferencesFromObjectToPK() 
    {
        $type = SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do);
        foreach($this->settings_for_where_clause as $prop => $value) {
            if ($value === null) continue;
            if ($this->object_model->isNonContainmentReferenceProperty($type, $prop)) {
                $pk = SDO_DAS_Relational_DataObjectHelper::getPrimaryKeyFromDataObject($this->object_model, $value);
                $this->settings_for_where_clause[$prop] = $pk;
            }
        }
    }

    public function buildValueList() 
    {
        $value_list = array();
        foreach($this->settings_for_where_clause as $name => $value) {
            if ($value === null) {
                // no-op - don't add to value list as we will have put IS NULL in the UPDATE statement
            } else {
                $value_list[] = $value;
            }
        }
        return $value_list;
    }

    public function toSQL()
    {
        $table_name = SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do);
        $stmt   = 'DELETE FROM ' . $table_name . ' ';
        $stmt .= $this->constructWhereClauseFromOriginalValues($this->settings_for_where_clause);
        $stmt .= ';'  ;
        return $stmt;
    }

    private function constructWhereClauseFromOriginalValues($original_values_as_nv_pairs)
    {
        foreach($original_values_as_nv_pairs as $name => $value) {
            $sql_compares[] = $this->makeAnSQLCompare($name, $value);
        }
        $where_clause = 'WHERE ';
        $where_clause .= implode(' AND ', $sql_compares);
        return $where_clause;
    }

    private function makeAnSQLCompare($name, $value)
    {
        if ($value === null) {
            $sql_setting = $name . ' IS NULL';
            return $sql_setting;
        }
        $sql_setting = "$name = ?";
        return $sql_setting;
    }

    public function toString()
    {
        $str = '[DeleteAction: ';
        $str .= SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do) . ':';
        $str .= SDO_DAS_Relational_DataObjectHelper::listNameValuePairs($this->do, $this->object_model);
        $str .= ']';
        return $str;
    }

    public function getSQL() // supplied for unit test
    {
        return $this->stmt;
    }

    public function getValueList() // supplied for unit test
    {
        return $this->value_list;
    }



}

?>
