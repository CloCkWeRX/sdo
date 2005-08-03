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
* Represent an update of a row in the database
*/

require_once 'SDO/DAS/Relational/Action.php';
require_once 'SDO/DAS/Relational/ObjectModel.php';
require_once 'SDO/DAS/Relational/UpdateNonContainmentReferenceAction.php';
require_once 'SDO/DAS/Relational/DataObjectHelper.php';

class SDO_DAS_Relational_UpdateAction extends SDO_DAS_Relational_Action {

	private $old_values;

	public function __construct($object_model,$do, $old_values)
	{
		parent::__construct($object_model,$do);
		$this->old_values = $old_values;
		$type = SDO_DAS_Relational_DataObjectHelper::getApplicationType($do);
		if (SDO_DAS_Relational::DEBUG_BUILD_PLAN ) {
			echo "adding update to plan for type $type\n";
		}
	}

	private function getOriginalSettingsOfChangedProperties($old_values)
	{
		foreach($old_values as $setting) {
			$property_name 	= $setting->getPropertyName();
			$old_value 		= $setting->getValue();
			$original_values_of_changed_properties[$property_name] = $old_value;
		}
		return $original_values_of_changed_properties;
	}

	public function execute($dbh)
	{
		$spawned_actions = array();
		$all_changed_settings	= array();
		$all_original_settings	= array();
		$type = SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do);
		$original_settings_of_changed_properties = $this->getOriginalSettingsOfChangedProperties($this->old_values);
		foreach($this->do as $prop => $value) {
			if (!isset($this->do[$prop])) {
				continue;
			}
			if ($this->object_model->isPrimitive($type, $prop) && isset($this->do[$prop])) {
				if (array_key_exists($prop, $original_settings_of_changed_properties)) {
					// it has probably changed - still need to check old and new not the same though
					$old = $original_settings_of_changed_properties[$prop];
					$new = $this->do[$prop];
					if ($new != $old) {
						$all_changed_settings[$prop] = $new;
						$all_original_settings[$prop] = $old;
					} else {
						$all_original_settings[$prop] = $old;
					}
				} else {
					// it hasn't changed so original value == current value
					$all_original_settings[$prop] = $this->do[$prop];
				}
				continue;
			}
			if ($this->object_model->isContainmentReferenceProperty($type, $prop)) {
				continue;
			}
			if ($this->object_model->isNonContainmentReferenceProperty($type, $prop)) {
				if (array_key_exists($prop, $original_settings_of_changed_properties)) {
					// it has probably changed - still need to check old and new not the same though
					$old_object = $original_settings_of_changed_properties[$prop];
					$new_object = $this->do[$prop];
					if ($new_object === $old_object) {
						$old_object = $this->do[$prop];
						$pk = SDO_DAS_Relational_DataObjectHelper::getPrimaryKeyFromDataObject($this->object_model, $old_object);
						$all_original_settings[$prop] = $pk;
					} else {
						// spawn an update
						if (isset($this->do[$prop])) {
							// TODO handle null
							$who_to = $this->do[$prop];
							// TODO we could check to see if the pk is already set and if so pick it up here and now
							$spawned_actions[] = new SDO_DAS_Relational_UpdateNonContainmentReferenceAction($this->object_model,$this->do, $prop, $who_to);
						}
					}
				} else {
					// it hasn't changed so original value == current value
					// because a n-c-ref, need the key, not the object
					$old_object = $this->do[$prop];
					$pk = SDO_DAS_Relational_DataObjectHelper::getPrimaryKeyFromDataObject($this->object_model, $old_object);
					$all_original_settings[$prop] = $pk;
				}
				continue;
			}
		}
		// can end up with empty $all_changed_settings if the only setting changed was a containment ref that does not
		// correspond to a column or if properties were updated but old == new
		if (count($all_changed_settings) > 0) {
			$stmt = $this->toSQL($all_changed_settings, $all_original_settings);
			foreach($all_changed_settings as $name => $value) {
				$value_list[] = $value;
			}
			foreach($all_original_settings as $name => $value) {
				$value_list[] = $value;
			}
			$this->executeStatement($dbh,$stmt,$value_list);
		}
		return $spawned_actions;
	}

	private function toSQL($changed_properties_as_nv_pairs,$all_original_values)
	{
		$table_name = SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do);
		$stmt   = 'UPDATE ' . $table_name . ' ';
		$stmt .= $this->constructSetClauseFromChangedValues($changed_properties_as_nv_pairs);
		$stmt .= ' ';
		$stmt .= $this->constructWhereClauseFromOriginalValues($all_original_values);
		$stmt .= ';'  ;
		return $stmt;
	}

	private function constructSetClauseFromChangedValues($changed_properties_as_nv_pairs)
	{
		foreach($changed_properties_as_nv_pairs as $name => $value) {
			$sql_settings[] = $this->makeAnSQLSetting($name, $value);
		}
		$set_clause = 'SET ';
		$set_clause .= implode( ',' , $sql_settings );
		return $set_clause;
	}

	private function constructWhereClauseFromOriginalValues($all_original_values)
	{
		foreach($all_original_values as $name => $value) {
			$sql_compares[] = $this->makeAnSQLCompare($name, $value);
		}
		$where_clause = 'WHERE ';
		$where_clause .= implode( ' AND ' , $sql_compares );
		return $where_clause;
	}

	private function makeAnSQLSetting($name, $value)
	{
		// 		TODO NULLS support needed before turning on this code hence asssert
		//		assert(false,"NULLS support needed before you can turn on this code");
		//		if (/* the value is NULL */) {
		//			$sql_setting = $name . '=NULL';
		//			return $sql_setting;
		//		}

		//      TODO convert booleans to 1 or 0. Not needed at the moment because all types in the DO are String
		//		if (gettype($value) == 'boolean') {
		//			if ($value) {
		//				$sql_setting = $name . '="1"';
		//			} else {
		//				$sql_setting = $name . '="0"';
		//			}
		//			return $sql_setting;
		//		}
		$sql_setting = "$name = ?";
		return $sql_setting;
	}

	private function makeAnSQLCompare($name, $value)
	{
		//		TODO NULLS support needed before turning on this code hence asssert
		//		assert(false,"NULLS support needed before you can turn on this code");
		//		if ($value === null) {
		//			$sql_setting = $name . ' IS NULL';
		//			return $sql_setting;
		//		}
		//
		//		TODO convert booleans to 1 or 0. Not needed at the moment because all types in the DO are String
		//		if (gettype($value) == 'boolean') {
		//			if ($value) {
		//				$sql_setting = $name . '="1"';
		//			} else {
		//				$sql_setting = $name . '="0"';
		//			}
		//			return $sql_setting;
		//		}
		$sql_setting = "$name = ?";
		return $sql_setting;
	}

	public function toString()
	{
		$str = '[UpdateAction: ';
		$str .= SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do) . ':';
		$str .= SDO_DAS_Relational_DataObjectHelper::listNameValuePairs($this->do, $this->object_model);
		$str .= ']';
		return $str;
	}

}

?>