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

*/

require_once 'SDO/DAS/Relational/PrimaryKey.php';
require_once 'SDO/DAS/Relational/Exception.php';

/**
 * SDO_DAS_Relational_Table encapsulates all the metadata about one table
 * 1 a table has a name
 * 2 the name must be a string
 * 3 a table has an unordered set of columns
 * 4 the list of columns must be all strings
 * 5 column names must be unique
 * 6 a table must have a primary key (which may be more than one column but not in this implementation
 * 7 the PK is a name
 * 8 the PK name must be one of the columns
 * 99 there are four valid settings in the array: name, columns, PK, FK
 */ 


class SDO_DAS_Relational_Table {

	// for the moment these instance variables are held exactly as the metadata. This may well change.

	private $table_name;
	private $columns = array();
	private $primary_key;

	public function __construct($table_metadata) 
	{
		/*
		* Check metadata specifies a table name and assign
		*/
		if (array_key_exists("name",$table_metadata)){
			$this->table_name 	= $table_metadata['name'];
		} else {
			throw new SDO_DAS_Relational_Exception('The metadata for one table did not contain a table name.');
		}

		/*
		* Check name is a string
		*/
		if (gettype($this->table_name) != "string"){
			throw new SDO_DAS_Relational_Exception('The metadata for table '.$table_metadata['name'].' specified a table name that was not a string.');
		}

		/*
		* Check table has a column list, then assign
		*/
		if (array_key_exists("columns",$table_metadata)){
			$this->columns 		= $table_metadata['columns'];
		} else {
			throw new SDO_DAS_Relational_Exception('The metadata for table '.$table_metadata['name'].' did not contain a column list.');
		}

		/*
		* Check column list is an array
		*/
		if (gettype($this->columns) != 'array') {
			throw new SDO_DAS_Relational_Exception('The metadata for table '.$table_metadata['name'].' specified a column list that was not an array.');
		}


		/*
		* Check column names are all strings
		*/
		foreach ($this->columns as $col) {
			if (gettype($col) != 'string') {
				throw new SDO_DAS_Relational_Exception('The metadata for table '.$table_metadata['name'].' contained a column name, " .$col . " that was not a string.');
			}
		}

		/*
		* Check column names are all unique
		*/
		for ($i = 0 ; $i < count($this->columns); $i++) {
			$checking_col = $this->columns[$i];
			$remaining_slice = array_slice($this->columns,$i+1);
			if (in_array($checking_col,$remaining_slice)) {
				throw new SDO_DAS_Relational_Exception('The metadata for table '.$table_metadata['name'].' contained a duplicate column name, ' .$checking_col . '.');
			}
		}

		/*
		* Check metadata specifies a PK and assign
		*/
		if (array_key_exists("PK",$table_metadata)){
			$this->primary_key 	= $table_metadata['PK'];
		} else {
			throw new SDO_DAS_Relational_Exception('The metadata for one table did not contain a PK.');
		}

		/*
		* Check PK is a string
		*/
		if (gettype($this->primary_key) != "string"){
			throw new SDO_DAS_Relational_Exception('The metadata for table '.$table_metadata['name'].' specified a PK name that was not a string.');
		}

		/*
		* Check PK column name is one of the columns
		*/
		if (!in_array($this->primary_key,$this->columns)){
			throw new SDO_DAS_Relational_Exception('The metadata for table '.$table_metadata['name'].' specified a primary key with a name that was not one of the columns.');
		}
				
		/*
		* Once everything else has passed, check we have only valid keys in the metadata
		*/
		$valid_keys = array('name', 'columns', 'PK' , 'FK');
		$supplied_keys = array_keys($table_metadata);
		
		if (count(array_diff($supplied_keys, $valid_keys))) {
			throw new SDO_DAS_Relational_Exception('The metadata for table '.$table_metadata['name'].' contained an invalid key. The only valid keys are: name, columns, PK, or FK.');
		}

	}

	public function getTableName() 
	{
		return $this->table_name;
	}

	public function getColumns() 
	{
		return $this->columns;
	}

	public function getPrimaryKey() 
	{
		return $this->primary_key;
	}
}

?>