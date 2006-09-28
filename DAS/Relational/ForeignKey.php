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
* ForeignKey encapsulates all the information about a foreign key
* 
* 1 it has a from table name
* 2 it has a from column
* 3 it has a to table name (necessarily to the table's primary key)
*/

class SDO_DAS_Relational_ForeignKey {
    private $from_table_name;
    private $from_column_name;
    private $to_table_name;

    public function __construct($table_metadata) 
    {

        /**
        * We assume that the metadata has already been inspected by the Table constructor so there will be a name
        * and a column list
        * We assume that we don't get called unless there is a 'FK' key in the metadata
        */
        assert(array_key_exists('name', $table_metadata));
        assert(array_key_exists('columns', $table_metadata));
        assert(array_key_exists('FK', $table_metadata));

        $this->from_table_name = $table_metadata['name'];
        $table_name = $this->from_table_name; // for messages below

        $columns = $table_metadata['columns'];
        $fk_metadata = $table_metadata['FK'];

        /*
        * Check FK metadata is an array
        */
        if (gettype($fk_metadata) != 'array'){
            $msg = "The metadata for table ".$table_name." specified foreign key metadata that was not an array.";
            throw new SDO_DAS_Relational_Exception($msg);
        }

        /*
        * Check FK metadata contains 'from'
        */
        if (array_key_exists('from', $fk_metadata)) {
            $this->from_column_name = $fk_metadata['from'];
        } else {
            $msg = "The metadata for table ".$table_name." specified foreign key metadata that did not contain a from field.";
            throw new SDO_DAS_Relational_Exception($msg);
        }

        /*
        * Check from field is a valid column
        */
        if (!in_array($this->from_column_name, $columns)) {
            $msg = "The metadata for table ".$table_name." specified foreign key metadata with a column name that was not in the list of columns for the table.";
            throw new SDO_DAS_Relational_Exception($msg);
        }

        /*
        * Check FK metadata contains 'to'
        */
        if (array_key_exists('to', $fk_metadata)) {
            $this->to_table_name = $fk_metadata['to'];
        } else {
            $msg = "The metadata for table ".$table_name." specified foreign key metadata that did not contain a to field.";
            throw new SDO_DAS_Relational_Exception($msg);
        }

        /*
        * Check we have only 'from'and 'to'
        */
        $valid_keys = array('from', 'to');
        $supplied_keys = array_keys($fk_metadata);

        if (count(array_diff($supplied_keys, $valid_keys))) {
            throw new SDO_DAS_Relational_Exception('The metadata for table '.$table_metadata['name'].' specified foreign key metadata that contained an invalid key. The only valid keys are from and to.');
        }

        /**
        * At this point we now have a valid from column and a plausible to field.
        * We cannot check that the to field is a valid table name until all the table definitions have been loaded
        */

    }
    
    public function getToTableName() 
    {
        return $this->to_table_name;
    }

    public function getFromTableName() 
    {
        return $this->from_table_name;
    }

    public function getFromColumnName() 
    {
        return $this->from_column_name;
    }

}

?>