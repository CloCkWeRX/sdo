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
* Delete a row in the database
*/

require_once 'SDO/DAS/Relational/Action.php';
require_once 'SDO/DAS/Relational/ObjectModel.php';
require_once 'SDO/DAS/Relational/DataObjectHelper.php';

class SDO_DAS_Relational_DeleteAction extends SDO_DAS_Relational_Action {

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
			if ($old_value === null) {
				// ignore
			} else {
				$original_settings_of_changed_properties[$property_name] = $old_value;
			}
		}
		return $original_settings_of_changed_properties;
	}

	public function getAllOriginalSettings()
	{
		$current_settings = SDO_DAS_Relational_DataObjectHelper::getCurrentPrimitiveSettings($this->do, $this->object_model);
		$original_settings_of_changed_properties = $this->getOriginalSettingsOfChangedProperties($this->old_values);
		foreach($current_settings as $name => $value) {
			if (array_key_exists($name, $original_settings_of_changed_properties)) {
				$original_settings_of_all_properties[$name] = $original_settings_of_changed_properties[$name];
			} else {
				$original_settings_of_all_properties[$name] = $this->do[$name];
			}
		}
		return $original_settings_of_all_properties;
	}

	public function execute($dbh)
	{
		$test = $this->getOriginalSettingsOfChangedProperties($this->old_values);
		$all_original_settings 			= $this->getAllOriginalSettings();
		// can end up with empty $updated settings if the only setting changed was a containment ref that does not
		// correspond to a column
		if (count($all_original_settings) > 0) {
			$stmt = $this->toSQL($all_original_settings);
			foreach($all_original_settings as $name => $value) {
				$value_list[] = $value;
			}
			$this->executeStatement($dbh,$stmt,$value_list);
		}
		$spawned_actions = array();
		return $spawned_actions;
	}

	private function toSQL($original_values_as_nv_pairs)
	{
		$table_name = SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do);
		$stmt   = 'DELETE FROM ' . $table_name . ' ';
		$stmt .= $this->constructWhereClauseFromOriginalValues($original_values_as_nv_pairs);
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

	private function constructWhereClauseFromOriginalValues($original_values_as_nv_pairs)
	{
		foreach($original_values_as_nv_pairs as $name => $value) {
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
		$sql_setting = $name . '="' . $value . '"';
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
		$str = '[DeleteAction: ';
		$str .= SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do) . ':';
		$str .= SDO_DAS_Relational_DataObjectHelper::listNameValuePairs($this->do, $this->object_model);
		$str .= ']';
		return $str;
	}

}

?>