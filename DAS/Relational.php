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

require_once 'SDO/DAS/Relational/Exception.php';
require_once 'SDO/DAS/Relational/DatabaseModel.php';
require_once 'SDO/DAS/Relational/ContainmentReferencesModel.php';
require_once 'SDO/DAS/Relational/ObjectModel.php';
require_once 'SDO/DAS/Relational/InsertAction.php';
require_once 'SDO/DAS/Relational/UpdateAction.php';
require_once 'SDO/DAS/Relational/DeleteAction.php';
require_once 'SDO/DAS/Relational/Plan.php';
require_once 'SDO/DAS/Relational/KeyObjectMap.php';
require_once 'SDO/DAS/Relational/DataObjectHelper.php';

/**
 * SDO Relational Data Access Service.
 * 
 * The SDO Relational Data Access Service (SDO_DAS_Relational) moves data in the form of an SDO
 * data graph back and forth between the application and a relational database
 * 
 * The necessary tasks are:
 * -  Given an SQL query, retrieve the data and present it to the application as an SDO data graph
 * -  Given an SDO data graph containing a change summary, apply the changes back to the database
 *
 * There is also an assumption of "optimistic concurrency": if the data has been
 * modified by another process while it was out of the database, then this will be detected and 
 * the changes will not be applied.
 * 
 */

class SDO_DAS_Relational {

	const DEBUG_BUILD_SDO_MODEL = false;
	const DEBUG_BUILD_PLAN 		= false;
	const DEBUG_EXECUTE_PLAN 	= false;
	const DEBUG_CHANGE_SUMMARY 	= false;

	const DAS_NAMESPACE 	= "das_namespace";
	const APP_NAMESPACE 	= "app_namespace";
	const DAS_ROOT_TYPE 	= "SDO_DAS_Relational_RootType";

	private $database_model; // SDO_DAS_Relational_DatabaseModel
	private $containment_references_model;
	private $object_model;
	private $data_factory;
	private $application_root_type;

	public function __construct($database_metadata,$application_root_type = null,$containment_references_metadata = null)
	{
		if ($database_metadata == null) {
			throw new SDO_DAS_Relational_Exception('Database metadata (first argument to constructor) must not be null');
		}
		if ($containment_references_metadata == null) {
			$containment_references_metadata = array();
		}
		$this->database_model 			= new SDO_DAS_Relational_DatabaseModel($database_metadata);
		if ($application_root_type == null) {
			$all_tables_names = $this->database_model->getAllTableNames();
			if (count($all_tables_names) == 1) {
				$application_root_type = $all_tables_names[0];
			} else {
				throw new SDO_DAS_Relational_Exception('Application root type (second argument to constructor) can only be null when there is exactly one table in the database metadata');
			}
		}
		$this->application_root_type 	= $application_root_type;
		$this->containment_references_model = new SDO_DAS_Relational_ContainmentReferencesModel($application_root_type,$containment_references_metadata);
		$this->object_model 			= new SDO_DAS_Relational_ObjectModel($this->database_model, $this->containment_references_model);
		$this->data_factory 			= SDO_DAS_DataFactory::getDataFactory();
		$this->object_model	-> defineToSDO($this->data_factory);
	}

	/**
	 * Create a root object. Turn on logging on the root's change summary.
	 */
	public function createRootDataObject()
	{
		$root		 			= self::createRoot($this->data_factory);
		$root->getChangeSummary()->beginLogging();
		return $root;
	}

	public function ensureStatementIsAString($stmt)
	{
		if (gettype($stmt) != 'string') {
			throw new SDO_DAS_Relational_Exception('The SQL statement (second argument) passed to executeQuery* must be a string');
		}
	}

	public function ensureValueListIsNullOrAnArray($value_list)
	{
		if ($value_list != NULL && gettype($value_list) != 'array') {
			throw new SDO_DAS_Relational_Exception('The value list (third argument) passed to executeQuery* must be null or an array');
		}
	}

	public function ensureColumnSpecifierContainsOnlyValidTableAndColumnNames($column_specifier)
	{
		if ($column_specifier != null) {
			if (gettype($column_specifier) != 'array') {
				throw new SDO_DAS_Relational_Exception('The column specifier (fourth argument) passed to executeQuery must be an array');
			}
			foreach($column_specifier as $cs) {
				if (gettype($cs) != 'string') {
					throw new SDO_DAS_Relational_Exception('Each entry in the column specifier must be a string');
				}
				list($table_name,$column_name) = split('[.]', $cs);
				if (! $this->database_model->isValidTableAndColumnPair($table_name,$column_name)) {
					throw new SDO_DAS_Relational_Exception('The column specifier contained an entry ' . $cs . ' that was not a valid table and column name pair in the form "table.column"');
				}
			}
		}
	}

	/**
	 * Given an SQL query in the form of a prepared stament and a value list, execute it, 
	 * normalise the result set into a data graph and return it.
	 */
	public function executeQuery($dbh, $stmt, $column_specifier = null)
	{
		$this->ensureStatementIsAString($stmt);
		$this->ensureColumnSpecifierContainsOnlyValidTableAndColumnNames($column_specifier);
		$dbh->beginTransaction();
		$pdo_stmt 		= $dbh->prepare($stmt);
		$rows_affected 	= $pdo_stmt->execute();
		$root = $this->normaliseResultSet($pdo_stmt, $column_specifier);
		$dbh->commit();
		return $root;
	}
	
	/**
	 * Given an SQL query as a string, execute it, normalise the result set into a data graph and return it.
	 */
	public function executePreparedQuery($dbh, PDOStatement $pdo_stmt, $value_list, $column_specifier = null)
	{
		$this->ensureValueListIsNullOrAnArray($value_list);
		$this->ensureColumnSpecifierContainsOnlyValidTableAndColumnNames($column_specifier);
		$dbh->beginTransaction();
		$rows_affected 	= $pdo_stmt->execute($value_list);
		$root = $this->normaliseResultSet($pdo_stmt, $column_specifier);
		$dbh->commit();
		return $root;
	}
	
	public function normaliseResultSet($pdo_stmt, $column_specifier) {
		if (gettype(PDO_FETCH_ASSOC) == 'string') {
			include_once "SDO/DAS/Relational/PDOConstants.colon.inc.php";
		} else {
			include_once "SDO/DAS/Relational/PDOConstants.underscore.inc.php";			
		}
		if ($column_specifier == null) {
			$all_rows = $pdo_stmt->fetchAll(SDO_DAS_Relational_PDO_FETCH_ASSOC);
		} else {
			$all_rows = $pdo_stmt->fetchAll(SDO_DAS_Relational_PDO_FETCH_NUM);
		}
		$root		 			= self::createRoot($this->data_factory);
		$table_names = $this->database_model->getAllTableNames();	//TODO make sure they come back in graph order
		assert ($table_names[0] == $this->application_root_type);
		$key_object_map = new SDO_DAS_Relational_KeyObjectMap();
		if ($all_rows) {
			$all_later_updates = array();
			foreach ($all_rows as $row) {
				if ($column_specifier == null) {
					$parsed_row = $this->breakRowIntoObjectsUsingPDOColumnNames($row);
				} else {
					$parsed_row = $this->breakRowIntoObjectsAccordingToColumnSpecifier($row, $column_specifier);
				}
				// TODO ensure PK is present for each type
				$current = $root;
				foreach ($table_names as $table_name) {
					// TODO various things to do with checking that PKs are present, that graph is well-formed
					// TODO only want tables in the model
					if (array_key_exists($table_name,$parsed_row)) {
						$row = $parsed_row[$table_name];
						$pk_name = $this->database_model->getPrimaryKeyFromTableName($table_name);
						if (!array_key_exists($pk_name, $row)) {
							throw new SDO_DAS_Relational_Exception("Data retrieved from table " . $table_name . " did not include the primary key for this table. Primary keys must always be included.\n");
						}
						$object = $key_object_map->findObjectByKeyAndType($row[$pk_name],$table_name);
						if ($object == null) {
							$current = $current->createDataObject($table_name);
							foreach ($row as $column_name=>$column_value) {
								if ( $this->object_model->isNonContainmentReferenceProperty($table_name, $column_name)) {
									// object that the n-c-ref points to may not exist yet so save as update for later
									$later_update = array();
									$later_update['object_to_update'] = $current;
									$later_update['column_to_update'] = $column_name;
									$later_update['key_of_object_to_point_to'] = $column_value;
									$to_type = $this->object_model->getToTypeOfNonContainmentReferenceProperty($table_name, $column_name);
									$later_update['type_of_object_to_point_to'] = $to_type;
									$all_later_updates[] = $later_update;
								}
								else {
									$current[$column_name] = $column_value;
								}
							}
							$key_object_map->storeObjectByKeyAndType($current,$row[$pk_name],$table_name);
						} else {
							$current = $object;
						}
					}
				}
			}
			foreach ($all_later_updates as $update) {
				$object_to_update 			= $update['object_to_update'];
				$column_to_update 			= $update['column_to_update'];
				$key_of_object_to_point_to 	= $update['key_of_object_to_point_to'];
				$type_of_object_to_point_to = $update['type_of_object_to_point_to'];
				$object_to_point_to 		= $key_object_map->findObjectByKeyAndType($key_of_object_to_point_to, $type_of_object_to_point_to);
				$object_to_update[$column_to_update] = $object_to_point_to;
			}
		}
		$root->getChangeSummary()->beginLogging();
		return $root;
	}

	public function breakRowIntoObjectsUsingPDOColumnNames($row)
	{
		$parsed_row = array();
		foreach($row as $col => $value) {
			$table_names_with_this_column = $this->object_model->getTypesByColumnNameIgnoreCase($col);
			switch (count($table_names_with_this_column)) {
				case 0:
				throw new SDO_DAS_Relational_Exception('The result set from ExecuteQuery contained a column with name ' . $col . ' but there is no table with a column with this name.');
				case 1:
				foreach ($table_names_with_this_column as $table_name => $property_name) // only one entry
					$parsed_row[$table_name][$property_name] = $value;
				break;
				default:
				throw new SDO_DAS_Relational_Exception('The result set from ExecuteQuery contained a column with name ' . $col . ' but there is more than one table with a column with this name. You need to pass a column specifier to resolve the ambiguity.');
			}
		}
		return $parsed_row;
	}

	public function breakRowIntoObjectsAccordingToColumnSpecifier($row, $column_specifier)
	{
		$parsed_row = array();
		for ($i=0; $i < count($row); $i++) {
			$cs = $column_specifier[$i];
			list($table_name,$column_name) = split('[.]', $cs);
			$parsed_row[$table_name][$column_name] = $row[$i];
		}
		return $parsed_row;
	}

	/**
	* Given a datagraph containing a change summary, and a PDO database handle, apply the changes to the database.
	*
	* Initialise an empty Plan and then for each change in the change summary augment the plan with
	* the database interactions needed. These may be inserts, updates or deletes. Some changes can
	* cause more than one interaction with the database. A non-exhaustive list of the sort of things
	* we might need to do are (just inserts at the moment):
	* 1. given a data object that has been created, insert a row to the database. Interpret PHP null as
	*    a null in the datbase, and an unset property as one that must not be set in the SQL
	*    insert statement
	* 2. insert a row and obtain the autogenerated primary key, Remember the key in the object-key map
	*    (keeps track of data objects -> PKs) for later use.
	* 3. insert a row using a mixture of data from the data object and a previously obtained key which we
	*    retrieve from the identity map. This will occur when we come to insert a row that contains a
	*    foreign key to a row in another table that we have only just inserted
	* 4. go back and update a row we inserted earlier with a key that we have only just obtained.
	*    this is an unusual scenario but it occurs for example when inserting a company/department/employee
	*    combination where all the keys are autogenerated and hence you do not know the key to put in the
	*    employee of the month field in the company record until after you have inserted the employee row.
	*/
	public function applyChanges(PDO $dbh, $data_object)
	{
		$root			= self::goUpTheTreeToTheRoot($data_object);
		$change_summary			= $root->getChangeSummary();
		if (self::DEBUG_CHANGE_SUMMARY) {
			self::displayChangeSummary($change_summary);
		}
		$changed_data_objects 	= $change_summary->getChangedDataObjects();
		$plan 					= new SDO_DAS_Relational_Plan();
		foreach ($changed_data_objects as $do) {
			switch ($change_summary->getChangeType($do)) {
				case SDO_DAS_ChangeSummary::ADDITION:
				$plan->addAction(new SDO_DAS_Relational_InsertAction($this->object_model, $do));
				break;
				case SDO_DAS_ChangeSummary::MODIFICATION:
				if (self::isRoot($do)) {
					continue;
					// currently not interested in modifications on the root - they appear as creates/deletes in their own right
					// TODO the way the change summary works is going to change and this modification will in future be the right way to find the create
				}
				$old_values = $change_summary->getOldValues($do);
				$plan->addAction(new SDO_DAS_Relational_UpdateAction($this->object_model, $do,$old_values));
				break;
				case SDO_DAS_ChangeSummary::DELETION:
				$old_values = $change_summary->getOldValues($do);
				$plan->addAction(new SDO_DAS_Relational_DeleteAction($this->object_model, $do, $old_values));
				break;
				default:
				assert(false,'SDO_DAS_Relational.php found a change in the change summary with an improper type');
			}
		}
		$dbh->beginTransaction();
		$plan->execute($dbh);
		$dbh->commit();
		// turn logging off and on again to clear out the change summary. The user can now continue to work with the data graph.
		$root->getChangeSummary()->endLogging();
		$root->getChangeSummary()->beginLogging();

	}

	private static function displaySettingsList($cs,$cdo) {
		$settings = $cs->getOldValues($cdo);
		echo "    the settings list contains " . count($settings) . " old values:\n";

		foreach ($settings as $setting) {
			echo "      the property " . $setting->getPropertyName();
			if ($setting->isSet()) {
				$original_value = $setting->getValue();
				echo ", which had original value ";
	  			ob_start();
	  			var_dump($original_value);
  				$content = ob_get_contents();
  				ob_end_clean();
  				echo "$content\n";
			} else {
				echo ", which was originally not set\n";
			}
		}
	}

	private static function displayChangeSummary($cs)
	{
		$changed_data_objects 	= $cs->getChangedDataObjects();
		echo "Change Summary contains " . count($changed_data_objects) . " objects:\n";
		foreach($changed_data_objects as $cdo) {
			echo '  Object of type ' . SDO_DAS_Relational_DataObjectHelper::getApplicationType($cdo) . "\n";
			$change_type = $cs->getChangeType($cdo);
			switch ($change_type) {
				case SDO_DAS_ChangeSummary::ADDITION:
				echo "    change type = addition\n";
				break;
				case SDO_DAS_ChangeSummary::MODIFICATION:
				echo "    the type of the change was Update\n";
				self::displaySettingsList($cs,$cdo);
				break;
				case SDO_DAS_ChangeSummary::DELETION:
				echo "    change type = delete\n";
				self::displaySettingsList($cs,$cdo);
				break;
				default:
				// TODO assume delete for the moment			assert (false);
				echo "    change type = something unrecognised\n";
				break;
			}
		}
		echo "End of Change Summary\n";
	}


	private static function goUpTheTreeToTheRoot($do)
	{
		$current = $do;
		while (!self::isRoot($current)) {
			$current = $current->getContainer();
		}
		return $current;
	}

	private static function isRoot($do)
	{
		return ($do->getType() == array(SDO_DAS_Relational::DAS_NAMESPACE , SDO_DAS_Relational::DAS_ROOT_TYPE));
	}

	private static function createRoot($df)
	{
		return $df->create(SDO_DAS_Relational::DAS_NAMESPACE, SDO_DAS_Relational::DAS_ROOT_TYPE);
	}

}

?>
