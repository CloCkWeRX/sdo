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
$Id: UpdateAction.php 220738 2006-09-28 19:25:00Z cem $
*/

/**
* Represent an update of a row in the database
*/

require_once 'SDO/DAS/Relational/Action.php';
require_once 'SDO/DAS/Relational/ObjectModel.php';
require_once 'SDO/DAS/Relational/UpdateNonContainmentReferenceAction.php';
require_once 'SDO/DAS/Relational/DataObjectHelper.php';
require_once 'SDO/DAS/Relational/SettingListHelper.php';
require_once 'SDO/DAS/Relational/DatabaseHelper.php';

class SDO_DAS_Relational_UpdateAction extends SDO_DAS_Relational_Action {

    private $object_model;
    private $do;
    private $spawned_actions = array();
    private $settings_for_set_clause = array(); // computed in constructor, settings that are needed for SET clause
    private $settings_for_where_clause = array(); // computed in constructor, settings to be tested in WHERE clause
    private $stmt = '';
    private $value_list = array();

    public function __construct($object_model,$do, $old_values)
    {
        $this->object_model = $object_model;
        $this->do = $do;
        $this->computeSettingsForSetAndWhereClauses($object_model, $do, $old_values);
        if (count($this->settings_for_set_clause) > 0) { // can end up with no meaningful things to SET, if so then
            $this->convertNonContainmentReferencesFromObjectToPK();
            $this->stmt = $this->toSQL();
            $this->value_list = $this->buildValueList();
        }
        if (SDO_DAS_Relational::DEBUG_BUILD_PLAN ) {
            $type = SDO_DAS_Relational_DataObjectHelper::getApplicationType($do);
            echo "adding update to plan for type $type\n";
        }

    }

    //TODO no need to pass these arguments in
    public function computeSettingsForSetAndWhereClauses($object_model, $do, $old_values)
    {
        $old_values_of_any_changed_properties = SDO_DAS_Relational_SettingListHelper::getSettingsAsArray($old_values);
        // iterate through data object and examine the primitive properties and non-containment references
        // 1a. if there is an old value, and it differs from new value, then save the current value for the SET clause and the old value for the WHERE clause
        // 1b. if there is an old value, but happens to be the same as the new value, ignore it - rare case
        // 2. if there is no old value it has not changed so save the current value for the WHERE clause
        // Properties that have been unset will not be seen when we iterate with foreach but that's OK, because:
        //   - we assign no meaning to unsetting a primitive or non-containment reference, so ignore it
        //   - unsetting a containment reference results in a delete appearing elsewhere in the change summary
        $type = SDO_DAS_Relational_DataObjectHelper::getApplicationType($do);
        foreach($this->do as $prop => $value) {
            if ($this->object_model->isContainmentReferenceProperty($type, $prop)) {
                // We ignore containment references - updates to them will appear as creates or deletes elsewhere in the C/S
                continue;
            }

            if (array_key_exists($prop, $old_values_of_any_changed_properties)) {
                $old = $old_values_of_any_changed_properties[$prop];
                $new = $this->do[$prop];
                if ($new === $old) {
                    // it appeared to change but old === new so use whichever one you like for WHERE clause
                    $this->settings_for_where_clause[$prop] = $old;
                } else {
                    // this is more likely - it did change
                    $this->settings_for_set_clause[$prop] = $new;
                    $this->settings_for_where_clause[$prop] = $old;
                }
            } else {
                $this->settings_for_where_clause[$prop] = $do[$prop];
            }
        }
    }

    public function convertNonContainmentReferencesFromObjectToPK()
    {
        $type = SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do);
        foreach($this->settings_for_set_clause as $prop => $value) {
            if ($value === null) continue;
            if ($this->object_model->isNonContainmentReferenceProperty($type, $prop)) {
                $pk = SDO_DAS_Relational_DataObjectHelper::getPrimaryKeyFromDataObject($this->object_model, $value);
                if ($pk === null) { // this must point to an object just created with no PK set yet, so spawn a later update
                    $who_to = $this->settings_for_set_clause[$prop];
                    $this->spawned_actions[] = new SDO_DAS_Relational_UpdateNonContainmentReferenceAction($this->object_model, $this->do, $prop, $who_to);
                    unset($this->settings_for_set_clause[$prop]);
                } else {
                    $this->settings_for_set_clause[$prop] = $pk;
                }
            }
        }
        foreach($this->settings_for_where_clause as $prop => $value) {
            if ($value === null) continue;
            if ($this->object_model->isNonContainmentReferenceProperty($type, $prop)) {
                $pk = SDO_DAS_Relational_DataObjectHelper::getPrimaryKeyFromDataObject($this->object_model, $value);
                $this->settings_for_where_clause[$prop] = $pk;
            }
        }
    }

    public function toSQL()
    {
        $table_name = SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do);
        $stmt   = 'UPDATE ' . $table_name . ' ';
        $stmt .= $this->constructSetClauseFromChangedValues($this->settings_for_set_clause);
        $stmt .= ' ';
        $stmt .= $this->constructWhereClauseFromOriginalValues($this->settings_for_where_clause);
        $stmt .= ';';
        return $stmt;
    }

    public function buildValueList()
    {
        $value_list = array();
        foreach($this->settings_for_set_clause as $name => $value) {
            $value_list[] = $value;
        }
        foreach($this->settings_for_where_clause as $name => $value) {
            if ($value === null) {
                // no-op - don't add to value list as we will have put IS NULL in the UPDATE statement
            } else {
                $value_list[] = $value;
            }
        }
        return $value_list;
    }

    private function constructSetClauseFromChangedValues($changed_properties_as_nv_pairs)
    {
        $sql_settings = array();
        foreach($this->settings_for_set_clause as $name => $value) {
            $sql_settings[] = $this->makeAnSQLSetting($name, $value);
        }
        $set_clause = 'SET ';
        $set_clause .= implode(',', $sql_settings);
        return $set_clause;
    }

    private function constructWhereClauseFromOriginalValues($all_original_values)
    {
        foreach($all_original_values as $name => $value) {
            $sql_compares[] = $this->makeAnSQLCompare($name, $value);
        }
        $where_clause = 'WHERE ';
        $where_clause .= implode(' AND ', $sql_compares);
        return $where_clause;
    }

    private function makeAnSQLSetting($name, $value)
    {
        $sql_setting = "$name = ?";
        return $sql_setting;
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

    public function execute($dbh)
    {
        if ($this->stmt != '') {
            SDO_DAS_Relational_DatabaseHelper::executeStatementTestForCollision($dbh, $this->stmt, $this->value_list);
        }
        return $this->spawned_actions;
    }


    public function toString()
    {
        $str = '[UpdateAction: ';
        $str .= SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do) . ':';
        $str .= SDO_DAS_Relational_DataObjectHelper::listNameValuePairs($this->do, $this->object_model);
        $str .= ']';
        return $str;
    }

    // Following functions supplied so unit test can inspect the update
    // Ideally they would not be public but no choice
    public function getValueList() // supplied for unit test
    {
        return $this->value_list;
    }

    public function getSQL() // supplied for unit test
    {
        return $this->stmt;
    }

}

?>
