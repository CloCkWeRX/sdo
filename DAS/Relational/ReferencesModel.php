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
 * Contains the model for the SDO containment references
 *
 * Contains an unordered set of containment references
 */

class SDO_DAS_Relational_ReferencesModel {

	private $references = array(); // Set of SDO_DAS_Relational_ContainmentReference

	public function __construct($SDO_references_metadata) 
	{
		foreach ($SDO_references_metadata as $rdef) {
			$this->references[] = new SDO_DAS_Relational_ContainmentReference($rdef);
		}

	}
	
	public function checkTableNamesAreValid(SDO_DAS_Relational_DatabaseModel $model) 
	{
		foreach ($this->references as $ref) {
			$parent = $ref->getParentName();
			$child =  $ref->getChildName();
			if (!$model->isValidTableName($parent)  || !$model->isValidTableName($child)) {
				throw new SDO_DAS_Relational_Exception('A reference specified a table name of ' . $parent . ' that was not specified in the database metadata');
			}
		}

	}
		
	/**
	* Check that each reference in the references model can be supported by a foreign key in the 
	* database model.
	*/
	public function checkAgainstForeignKeys(SDO_DAS_Relational_DatabaseModel $model) 
	{
		foreach ($this->references as $ref) {
			$parent = $ref->getParentName();
			$child =  $ref->getChildName();
			/**
			* Now we look for a foreign key that suppports this parent-child relationship
			* The foreign key we want will point from the child to the parent 
			* (yes I did mean that, FKs go upwards when references go downwards)
			* i.e. we want a FK where toTableName == our $parent and fromTableName == our $child
			*/
			$fk_list = $model->getForeignKeys();
			$found = false;
			foreach ($fk_list as $fk) {
				if ($fk->getToTableName() == $parent && $fk->getFromTableName() == $child) {
					$found = true;
					break;
				}
			}
			if (! $found) {
				throw new SDO_DAS_Relational_Exception('No foreign key was found in the database model to support the reference with (parent => '.$parent. ', child => '. $child .')');
			}
		}

	}

	public function isValidParentName($name) 
	{
		foreach ($this->references as $ref) {
			if ($name == $ref->getParentName()) {
				return true;
			}
		}
		return false;
	}

	public function getNeighbours($parent_name) 
	{
		foreach ($this->references as $ref) {
			if ($parent_name == $ref->getParentName() ) {
				$neighbours[] = $ref->getChildName();
			}
		}
		return $neighbours;
	}
}

?>