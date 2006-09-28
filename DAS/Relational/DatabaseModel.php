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

require_once 'SDO/DAS/Relational/Table.php';
require_once 'SDO/DAS/Relational/ForeignKey.php';

/**
 * Contains the model for the database: tables, columns, primary keys, foreign keys
 *
 * contains an undordered set of tables which in turn contain their name, columns, PK.
 * contains an undordered set of foreign keys which contain which table and column they are from and which table they are to.
 */

class SDO_DAS_Relational_DatabaseModel {

    /**
    * set of SDO_DAS_Relational_Table
    */
    private $tables = array(); // Set of SDO_DAS_Relational_Table
    private $foreign_keys = array(); // Set of SDO_DAS_Relational_Table

    public function __construct($database_metadata) 
    {
        foreach ($database_metadata as $tdef) {

            $table = new SDO_DAS_Relational_Table($tdef);   // extracts name, cols, PK
            $this->tables[] = $table;

            if (key_exists('FK', $tdef)) {
                $fk = new SDO_DAS_Relational_ForeignKey($tdef); // extracts FK info
                $this->foreign_keys[] = $fk;
            }
        }

        $this->checkFKsPointToValidTables();
    }

    private function checkFKsPointToValidTables() 
    {
        foreach ($this->tables as $t) {
            $all_table_names[] = $t->getTableName();
        }
        foreach ($this->foreign_keys as $fk) {
            $to_table_name = $fk->getToTableName();
            if (!in_array($to_table_name, $all_table_names)){
                throw new SDO_DAS_Relational_Exception('A foreign key specifies a to field of ' . $to_table_name . ', which is not a valid table name');
            }
        }
    }
    
    public function getAllTableNames() 
    {
        foreach ($this->tables as $t) {
            $all_table_names[] = $t->getTableName();
        }
        return $all_table_names;
    }

    public function isValidTableName($name) 
    {
        foreach ($this->tables as $table) {
            if ($name == $table->getTableName()) {
                return true;
            }
        }
        return false;
    }

    public function isValidTableAndColumnPair($table_name, $column_name) 
    {
        $table = $this->getTableByName($table_name);
        if ($table == null) return false;
        $column_names = $table->getColumns();
        if (in_array($column_name, $column_names)) return true;
        else return false;
    }

    public function getTableByName($name) 
    {
        foreach ($this->tables as $table) {
            if ($table->getTableName() == $name) {
                return $table;
            }
        }
        return null;
    }

    public function getColumns($table_name) 
    {
        foreach($this->tables as $table) {
            if ($table->getTableName() == $table_name) {
                return $table->getColumns();
            }
        }
        return null;
    }

    public function getForeignKeys() 
    {
        return $this->foreign_keys;
    }

    public function getForeignKeyByFromTableNameAndColumnName($table_name,$column_name) 
    {
        foreach($this->foreign_keys as $fk) {
            if ($fk->getFromTableName() == $table_name && $fk->getFromColumnName() == $column_name) {
                return $fk;
            }
        }
        return null;
    }

    public function getPrimaryKeyFromTableName($table_name) 
    {
        $table = $this->getTableByName($table_name);
        assert($table != null);
        return $table->getPrimaryKey();
    }
}

?>