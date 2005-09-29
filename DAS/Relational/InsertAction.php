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
* Represent an insert of a row to the database
*
* Holds the name of the table into which we will insert, and an array of name=>value pairs to represent the 
* columns that we want to set: the name is the name of the column and the value is that to which it must be set.
* When it comes to creating the SQL statement to insert the data, most values just need to be enclosed in double quotes.
* This works for strings, integers and floats.
* If the value is the PHP null value, an SQL NULL will be inserted. 
* If the value is a PHP boolean true or false, it will be converted to "1" or "0".
*/

require_once 'SDO/DAS/Relational/Action.php';
require_once 'SDO/DAS/Relational/ObjectModel.php';
require_once 'SDO/DAS/Relational/UpdateNonContainmentReferenceAction.php';
require_once 'SDO/DAS/Relational/DataObjectHelper.php';

class SDO_DAS_Relational_InsertAction extends SDO_DAS_Relational_Action {

	public function __construct($object_model,$do)
	{
		parent::__construct($object_model,$do);
		$type = SDO_DAS_Relational_DataObjectHelper::getApplicationType($do);
		if (SDO_DAS_Relational::DEBUG_BUILD_PLAN ) {
			echo "adding insert to plan for type $type\n";
		}
	}

	/**
	* Break object into name-value pairs, add a parent PK if necessary, insert, and retrieve and store own PK
	*/
	public function execute($dbh)
	{
		$name_value_pairs 	= SDO_DAS_Relational_DataObjectHelper::getCurrentPrimitiveSettings($this->do, $this->object_model);
		$name_value_pairs   = $this->addFKToParentToNameValuePairs($this->do,$name_value_pairs);
		$spawned_actions 	= $this->spawnLaterUpdatesForNonContainmentReferences($this->do);

		$stmt = $this->toSQL($name_value_pairs);
		foreach($name_value_pairs as $name => $value) {
			$value_list[] = $value;
		}
		$this->executeStatement($dbh,$stmt,$value_list);
		$this->storeThisObjectsPrimaryKey($dbh);

		return $spawned_actions;
	}

	private function addFKToParentToNameValuePairs($do,$name_value_pairs)
	{
		$type = SDO_DAS_Relational_DataObjectHelper::getApplicationType($do);
		$containing_reference = $this->object_model->getContainingReferenceFromChildType($type);
		if ($containing_reference != null) {
			$fk 				= $this->object_model->getTheFKSupportingAContainmentReference($containing_reference);
			$from_column_name 	= $fk->getFromColumnName();
			$parent_do 			= $do->getContainer();
			$parentPK 			= SDO_DAS_Relational_DataObjectHelper::getPrimaryKeyFromDataObject($this->object_model,$parent_do);
			$name_value_pairs[$from_column_name] = $parentPK;
		}
		return $name_value_pairs;
	}

	private function spawnLaterUpdatesForNonContainmentReferences($do)
	{
		$spawned_actions = null;
		$type = SDO_DAS_Relational_DataObjectHelper::getApplicationType($do);
		foreach($do as $prop => $value) {
			if ($this->object_model->isNonContainmentReferenceProperty($type,$prop)) {
				if (isset($do[$prop])) {
					// TODO handle null
					$who_to = $do[$prop];
					// TODO we could check to see if the pk is already set and if so pick it up here and now 
					$spawned_actions[] = new SDO_DAS_Relational_UpdateNonContainmentReferenceAction($this->object_model,$do, $prop, $who_to);
				}

			}
		}
		return $spawned_actions;
	}

	public function toSQL($name_value_pairs)
	{
		foreach($name_value_pairs as $name => $value) {
			$name_list[] = $name;
			$placeholder_list[] = "?";
		}
		$table_name = SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do);
		$stmt  = 'INSERT INTO ' . $table_name;
		$stmt .= " (" . implode( "," , $name_list ) . ") ";
		$stmt .= "VALUES ";
		$stmt .= "(" . implode( "," , $placeholder_list ) . ")";
		$stmt .= ";";
		return $stmt;
	}

	private function storeThisObjectsPrimaryKey($dbh)
	{
		if (gettype(PDO_FETCH_ASSOC) == 'string') {
			include_once "SDO/DAS/Relational/PDOConstants.colon.inc.php";
		} else {
			include_once "SDO/DAS/Relational/PDOConstants.underscore.inc.php";			
		}		$type = SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do);
		$pk_property_name = $this->object_model->getPropertyRepresentingPrimaryKeyFromType($type);
		if (!isset($this->do[$pk_property_name]) /* && not null */) {
			$pdo_client_version = $dbh->getAttribute(SDO_DAS_Relational_PDO_ATTR_CLIENT_VERSION);
			if (substr($pdo_client_version,0,4) == 'ODBC') {
				// looks like DB2
				foreach($dbh->query('values identity_val_local()') as $row) {
  					$last_insert_id = $row[1]; 
				}				
			} else {
				// assume MySQL
				$last_insert_id = $dbh->lastInsertId();
				if ($last_insert_id == "%ld") {
					$bug_msg = "A call to PDO's lastInsertId() method returned the string '%ld'. ";
					$bug_msg .= "This is a known problem in PHP 5.1 beta. ";
					$bug_msg .= "You will need to move to a more recent version of PHP 5.1. ";
					$bug_msg .= "This problem was known to be fixed in the 200507110630 build of PHP 5.1. ";
					$bug_msg .= "Please see http://bugs.php.net/bug.php?id=33618";
					throw new SDO_DAS_Relational_Exception($bug_msg);
				}
			}
			
			if (SDO_DAS_Relational::DEBUG_EXECUTE_PLAN) {
				echo "executed obtainLastInsertId() and obtained $last_insert_id\n";
			}
			$this->do[$pk_property_name] = $last_insert_id;
		}
	}

	public function toString()
	{
		$str = '[InsertAction: ';
		$str .= SDO_DAS_Relational_DataObjectHelper::getApplicationType($this->do) . ':';
		$str .= SDO_DAS_Relational_DataObjectHelper::listNameValuePairs($this->do, $this->object_model);
		$str .= ']';
		return $str;
	}

}

?>