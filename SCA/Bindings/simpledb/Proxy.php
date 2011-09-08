<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                         |
| All Rights Reserved.                                                        |
+-----------------------------------------------------------------------------+
| Licensed under the Apache License, Version 2.0 (the "License"); you may not |
| use this file except in compliance with the License. You may obtain a copy  |
| of the License at -                                                         |
|                                                                             |
|                   http://www.apache.org/licenses/LICENSE-2.0                |
|                                                                             |
| Unless required by applicable law or agreed to in writing, software         |
| distributed under the License is distributed on an "AS IS" BASIS, WITHOUT   |
| WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.            |
| See the License for the specific language governing  permissions and        |
| limitations under the License.                                              |
+-----------------------------------------------------------------------------+
| Author: Rajini Sivaram,                                                     |
|         Graham Charters                                                     |
+-----------------------------------------------------------------------------+
*/



class SCA_Bindings_simpledb_Proxy
{
    private $datafactory;
    private $table;
    private $primary_key;
    private $config;
    private $pdo;
    private $pdo_driver;
    private $namespace = '';
    private $isIBM = false;
    private $table_name;
    private $case;

    const ROOT_NS   = 'urn:rootns';
    const ROOT_TYPE = 'RootType';

    /**
     * Constructor for simpledb Proxy
     * Use the values from the configuration file provided to create a PDO for the database
     * Query the database to obtain column metadata and primary key
     *
     */
    public function __construct($target, $immediate_caller_directory, $binding_config)
    {
        SCA::$logger->log('Entering constructor');

        try {

            $this->table = $target;

            $this->config =
                SCA_Helper::mergeBindingIniAndConfig($binding_config,
                                                     $immediate_caller_directory);

            if (array_key_exists('username', $this->config))
                $username = $this->config['username'];
            else
                $username = null;

            if (array_key_exists('password', $this->config))
                $password = $this->config['password'];
            else
                $password = null;

            if (array_key_exists('namespace', $this->config))
                $this->namespace = $this->config['namespace'];

            if (array_key_exists('case', $this->config))
                $this->case = $this->config['case'];
            else
                $this->case = 'lower';

            if (!array_key_exists('dsn', $this->config)) {
                throw new SCA_RuntimeException("Data source name should be specified");
            }

            $tableName = $this->table;

            // Special processing for IBM databases:
            // IBM table names can contain schema name as prefix
            // Column metadata returned by pdo_ibm does not specify the primary key
            // Hence primary key for IBM databases has to be obtained using
            // db2_primary_key.

            if (strpos($this->config["dsn"], "ibm:") === 0 ||
            strpos($this->config["dsn"], "IBM:") === 0) {

                $this->isIBM = true;

                // Table could be of format schemaName.tableName
                $schemaName = null;
                if (($pos = strrpos($tableName, '.')) !== false) {
                    $schemaName = substr($tableName, 0, $pos);
                    $tableName = substr($tableName, $pos+1);
                }

                // DSN for IBM databases can be a database name or a connection string
                // Both can be passed onto db2_connect. Remove the dsn prefix if specified

                $database = substr($this->config["dsn"], 4);
                if (strpos($database, "dsn=") === 0 ||
                strpos($database, "DSN=") === 0) {

                    $database = substr($database, 4);
                }

                // Need to make sure the name is in DB2 uppercase style
                $db2TableName = strtoupper($tableName);

                $conn = db2_connect($database, $username, $password);
                $stmt = db2_primary_keys($conn, null, $schemaName, $db2TableName);
                $keys = db2_fetch_array($stmt);
                if (count($keys) > 3)
                    $this->primary_key = $keys[3];
                else
                    throw new SCA_RuntimeException("Table '$tableName' does not appear to have a primary key.");

            }
            $this->table_name = $this->__getName($tableName);

            if ($username != null)
              $this->pdo = new PDO($this->config["dsn"], $username, $password, $this->config);
            else
              $this->pdo = new PDO($this->config["dsn"]);


            $this->pdo_driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

            $stmt = $this->pdo->prepare('SELECT * FROM '.$this->table);
            if (!$stmt->execute()) {
                throw new SCA_RuntimeException(self::__getPDOError($stmt, "select"));
            }


            $columns = array();
            for ($i = 0; $i < $stmt->columnCount(); $i++) {
                $meta = $stmt->getColumnMeta($i);

                $name = $this->__getName($meta["name"]);

                if (in_array("primary_key",  $meta["flags"], true)) {
                    $this->primary_key = $name;

                }

                $columns[] = $name;

            }

            //$pk = $this->__getName($this->primary_key);

            SCA::$logger->log("Table $tableName PrimaryKey $this->primary_key");

            /*
            $metadata = array(
            'name' => $this->table_name,
            'columns' => $columns,
            'PK' => $pk
           );
            */

            $this->datafactory = SDO_DAS_DataFactory::getDataFactory();

            // Define the model on the data factory (from the database)
            $this->datafactory->addType(SCA_Bindings_simpledb_Proxy::ROOT_NS,
                                        SCA_Bindings_simpledb_Proxy::ROOT_TYPE);
            $this->datafactory->addType($this->namespace, $this->table_name);

            foreach ($columns as $name) {
                $this->datafactory->
                addPropertyToType($this->namespace, $this->table_name,
                $name, 'commonj.sdo', 'String');
            }

            $this->datafactory->addPropertyToType(SCA_Bindings_simpledb_Proxy::ROOT_NS,
                                                  SCA_Bindings_simpledb_Proxy::ROOT_TYPE,
                                                  $this->table_name, $this->namespace,
                                                  $this->table_name, array('many' => true));


        } catch (Exception $e) {

            throw new SCA_RuntimeException($e->getMessage());
        }

        SCA::$logger->log("Exiting constructor");
    }

    /**
     * Implements the name mangling rules based on configuration (e.g. lower case)
     *
     * @param string $name The input property name
     * @return string The mangled name
     */
    private function __getName($name) {

        if (strlen($name) == 0)
            return $name;

        switch ($this->case) {
           case "upper":
               return strtoupper($name);
               break;
           case "mixed":
               $tmpname = strtolower($name);
               $tmpname[0] = strtoupper($tmpname[0]);
               return $tmpname;
               break;
           default: // lower
               return strtolower($name);
       }
    }

    private function __createRoot() {
        return $this->datafactory->create(SCA_Bindings_simpledb_Proxy::ROOT_NS,
                                          SCA_Bindings_simpledb_Proxy::ROOT_TYPE);
    }


    /**
     * Add reference type - this is not used by this proxy
     *
     * @param SCA_ReferenceType $reference_type Reference type
     *
     * @return null
     */
    public function addReferenceType(SCA_ReferenceType $reference_type)
    {
        SCA::$logger->log("Entering");

        $this->reference_type = $reference_type;

    }

    private function __executePreparedQuery($stmt, $id) {

        $success = $stmt->execute(array($id));

        // Test if the SQL execute was successful
        if (0 == $success) {
            // there is a problem so get the SQL error code and report it
            throw new SCA_RuntimeException(self::__getPDOError($stmt, "retrieve"));
        }

        $rows_affected = $stmt->rowCount();


        $all_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (sizeof($all_rows) == 0) {
            throw new SCA_NotFoundException("Entry $id was not found.");
        }

        $root = $this->__createRoot();

        if ($all_rows) {

            foreach ($all_rows as $row) {

                $sdo = $root->createDataObject($this->table_name);

                foreach ($row as $name => $value) {
                    $property = $this->__getName($name);
                    $sdo[$property] = $value;
                }
            }

        }

        return $root;
    }


    private static function __getPDOError($stmt, $op) {

        $errorInfo = $stmt->errorInfo();

        $error = <<< END
Error encountered while executing $op
Error information from PDOStatement->errorInfo() :
SQLSTATE error code: $errorInfo[0]
Driver-specific error code:  $errorInfo[1]
Driver-specific error message:  $errorInfo[2]
END;

        return $error;
    }

    /**
     * Create an entry in the table
     *
     * @param SDO_DataObjectImpl $entry
     *
     * @return mixed Value of primary key of entry created
     */
    public function create($entry)
    {
        SCA::$logger->log("Entering");

        $statement = "INSERT INTO ".$this->table;

        foreach ($entry as $key => $value) {

            if (isset($keys))
            $keys = "$keys, $key";
            else
            $keys = $key;

            $values[] = $value;

            if (isset($q))
            $q = "$q, ?";
            else
            $q = "?";
        }

        $statement = "$statement ($keys) VALUES ($q)";


        try {

            $this->pdo->beginTransaction();

            // lastInsertId returns 0 for DB2. Get primary key
            // of the inserted entry using SELECT FROM FINAL TABLE
            // Since this SELECT does not work for MYSQL, use lastInsertId for MYSQL
            if ($this->isIBM)
                $statement = "SELECT ".$this->primary_key." FROM FINAL TABLE ($statement)";

            $stmt = $this->pdo->prepare($statement);

            if (!$stmt->execute($values)) {
                $exception = new SCA_RuntimeException(self::__getPDOError($stmt, "create"));
                $this->pdo->rollback();
                throw $exception;
            }

            $id = 0;


            if (!$this->isIBM) {
                $id = $this->pdo->lastInsertId();
            } else {

                // Select should have returned an array with one element - which is an array
                // containing the primary key and its value
                $rows = $stmt->fetchAll(PDO::FETCH_BOTH);
                if (count($rows) > 0 && count($rows[0]) > 0) {
                    $id = $rows[0][$this->primary_key];
                }

            }

        } catch (PDOException $e) {
            $exception = new SCA_NotFoundException("PDO Exception - ".$e->getMessage());
            $this->pdo->rollback();
            throw $exception;
        }

        $this->pdo->commit();

        return $id;

    }


    /**
     * Retrieve an entry from the table
     *
     * @param mixed $id Value of primary key of entry to be retrieved
     *
     * @return SDO_DataObjectImpl
     */
    public function retrieve($id=null)
    {
        SCA::$logger->log("Entering");

        try {

            $stmt = $this->pdo->prepare('SELECT * FROM '.$this->table.' WHERE '.$this->primary_key.' = ?');

            // Use executePreparedQuery on the DAS to directly create an SDO
            $sdo = $this->__executePreparedQuery($stmt, $id);

            if ($sdo instanceof SDO_DataObjectImpl && array_key_exists($this->table_name, $sdo)) {
                $sdo = $sdo[$this->table_name];

                if ($sdo instanceof SDO_DataObjectList && count($sdo) == 1) {
                    $sdo = $sdo[0];
                }
            }
            else
            $sdo = null;

            return $sdo;

        } catch (PDOException $e) {
            throw new SCA_NotFoundException("PDO Exception - ".$e->getMessage());
        }

    }

    /**
     * Update an entry
     *
     * @param mixed $id Value of primary key of entry to be updated
     * @param SDO_DataObjectImpl $entry New value for entry
     *
     * @return bool
     */
    public function update($id, $entry)
    {
        SCA::$logger->log("Entering");

        try {

            $this->pdo->beginTransaction();

            $statement = "UPDATE ".$this->table." SET ";

            foreach ($entry as $key => $value) {

                if (isset($keys))
                $keys = "$keys, $key = ?";
                else
                $keys = "$key = ?";

                $values[] = $value;

            }
            $values[] = $id;


            $statement = "$statement $keys WHERE ".$this->primary_key." = ?;";

            $stmt = $this->pdo->prepare($statement);

            if (!$stmt->execute($values)) {
                $exception = new SCA_RuntimeException(self::__getPDOError($stmt, "update"));
                $this->pdo->rollback();
                throw $exception;
            }

            $this->pdo->commit();

            return true;

        } catch (PDOException $e) {
            $exception = new SCA_NotFoundException("PDO Exception - ".$e->getMessage());
            $this->pdo->rollback();
            throw $exception;
        }
    }


    /**
     * Delete an entry
     *
     * @param mixed $id Value of primary key of entry to be deleted
     *
     * @return bool
     */
    public function delete($id)
    {
        SCA::$logger->log("Entering");

        try {

            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare('DELETE FROM '.$this->table.' WHERE '.$this->primary_key.' = ?');

            if (!$stmt->execute(array($id))) {
                $exception = new SCA_RuntimeException(self::__getPDOError($stmt, "delete"));
                $this->pdo->rollback();
                throw $exception;
            }

        } catch (PDOException $e) {
            $exception = new SCA_NotFoundException("PDO Exception - ".$e->getMessage());
            $this->pdo->rollback();
            throw $exception;
        }

        $this->pdo->commit();
        return true;

    }

    /**
     * Allows the reference user to create a data object
     * based on a type that is expected to form part of
     * a message to reference
     *
     * @param string $namespace_uri Namespace URI
     * @param string $type_name     Type name
     *
     * @return SDO
     */
    public function createDataObject($namespace_uri, $type_name)
    {
        SCA::$logger->log("Entering");

        try {
            $root = $this->__createRoot();
            return $root->createDataObject($type_name);

        } catch( Exception $e) {
            throw new SCA_RuntimeException($e->getMessage());
        }
        return null;
    }

    public function __call($method_name, $arguments) {
        SCA::$logger->log("Call to invalid method $method_name.");
        throw SCA_MethodNotAllowedException("Call to invalid method $method_name.");
    }


}

