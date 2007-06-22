<?php
/*
+----------------------------------------------------------------------+
| Copyright IBM Corporation 2007.                                      |
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
| Author: Graham Charters                                              |
+----------------------------------------------------------------------+
$Id$
*/

include 'SCA/SCA.php';

/**
 * An SCA component that uses a database service
 * 
 * @service
 *
 */
class DbConsumer {
    
    /**
     * The database service
     *
     * @reference
     * @binding.simpledb contact
     * 
     * @config config/mysql_config.ini
     */
    public $dbservice;

    public function create($entry) {
        return $this->dbservice->create($entry);
    }

    public function retrieve($id) {
        return $this->dbservice->retrieve($id);
    }
    
    public function update($id, $entry) {
        return $this->dbservice->update($id, $entry);
    }
    
    public function delete($id) {
        return $this->dbservice->delete($id);
    }
}


?>