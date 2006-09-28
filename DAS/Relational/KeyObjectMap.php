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
* Maintain a map of primary key and type to object.
*
* Used when building a normalised data graph from a result set.
*/

class SDO_DAS_Relational_KeyObjectMap {

    private $map;
    
    public function __construct() 
    {
        $this->map = array();
    }

    public function storeObjectByKeyAndType($do,$key,$type) 
    {
        $this->map[$type][$key] = $do;
    }

    public function findObjectByKeyAndType($key, $type) 
    {
        if (array_key_exists($type, $this->map)) {
            $key_map_for_type = $this->map[$type];
            if (array_key_exists($key, $key_map_for_type)) {
                $data_object = $key_map_for_type[$key];
                return $data_object;
            }
        }
        return null;
    }
}

?>