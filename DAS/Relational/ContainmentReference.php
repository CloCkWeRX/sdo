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

/**
 * Encapsulates one SDO containment reference
 * 
 * has a parent, a child
 *
 * TODO could have a name if the user wants to specify a different name for the reference. For now it is the name of the 
 * child. 
 */ 


class SDO_DAS_Relational_ContainmentReference {

	// for the moment these instance variables are held exactly as the metadata. This may well change.

	private $parent;
	private $child;

	public function __construct($ref_metadata)
	{

		/*
		* Check metadata specifies a parent field
		*/
		if (array_key_exists('parent',$ref_metadata)){
			$this->parent 	= $ref_metadata['parent'];
		} else {
			throw new SDO_DAS_Relational_Exception('The metadata for a reference did not contain a parent field.');
		}

		/*
		* Check parent is a string
		*/
		if (gettype($this->parent) != 'string'){
			throw new SDO_DAS_Relational_Exception('The metadata for a reference specified a parent field ' . $this->parent . ' that was not a string.');
		}

		/*
		* Check metadata specifies a child field
		*/
		if (array_key_exists('child',$ref_metadata)){
			$this->child 	= $ref_metadata['child'];
		} else {
			throw new SDO_DAS_Relational_Exception('The metadata for a reference with parent field '.$this->parent.' did not contain a child field.');
		}

		/*
		* Check parent is a string
		*/
		if (gettype($this->child) != 'string'){
			throw new SDO_DAS_Relational_Exception('The metadata for a reference specified a child field ' . $this->child . ' that was not a string.');
		}

		/*
		* Once everything else has passed, check we have only valid keys in the metadata
		*/
		$valid_keys = array('parent', 'child');
		$supplied_keys = array_keys($ref_metadata);

		if (count(array_diff($supplied_keys, $valid_keys))) {
			throw new SDO_DAS_Relational_Exception('The metadata for reference with parent field '.$this->parent.' contained an invalid key. The only valid keys are parent or child.');
		}

	}

	public function getParentName()
	{
		// for now return the name of the child
		return $this->parent;
	}

	public function getChildName()
	{
		// for now return the name of the child
		return $this->child;
	}

	public function getReferenceName()
	{
		// for now return the name of the child. In due course might allow user to specify a name they prefer
		return $this->child;
	}

}

?>