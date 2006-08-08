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

/**
* Helper routines for use on or with an SDO data object. These are logically instance methods on a data object 
* but since we do not own the implementation of the SDO Data Object we put them in this helper class
*/

class SDO_DAS_Relational_DataObjectHelper
{

	public static function getCurrentPrimitiveSettings($data_object,$object_model)
	{
		$nvpairs = array();
		$type = self::getApplicationType($data_object);
		foreach($data_object as $prop => $value) {
			if ($object_model->isPrimitive($type,$prop)) {
				if (isset($data_object[$prop])) {
					$nvpairs[$prop] = $data_object[$prop];
				}
			}
		}
		return $nvpairs;
	}

	public static function getApplicationType($data_object)
	{
		$model_reflection_object = new SDO_Model_ReflectionDataObject($data_object);
		$type = $model_reflection_object->getType();
		return $type->name;
	}

	public static function getPrimaryKeyFromDataObject($object_model,$data_object)
	{
		$type = self::getApplicationType($data_object);
		$pk_property_name = $object_model->getPropertyRepresentingPrimaryKeyFromType($type);
		if (isset($data_object[$pk_property_name])) {
			$pk = $data_object[$pk_property_name];
			return $pk;
		} else {
			return null;
		}
	}

	public static function listNameValuePairs($data_object,$object_model)
	{
		$str = ' [';
		$nvpairs = self::getCurrentPrimitiveSettings($data_object,$object_model);
		foreach ($nvpairs as $n => $v) {
			$str .=  " $n => ";
			if ($v == null) $str .=  'NULL';  // TODO really think this should be === but does not compare OK hence bug 425
			else $str .=  $v;
		}
		$str .= ']';
		return $str;
	}

}

?>