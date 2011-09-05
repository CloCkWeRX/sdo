<?php
/*
+----------------------------------------------------------------------+
| Copyright IBM Corporation 2005, 2006.                                |
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
$Id: ObjectModel.php 223806 2006-11-24 10:44:42Z cem $
*/

/**
 * Encapsulates the combined knowledge of the database model and the references model.
 *
 * Knows about the database and references models and the aplication root type. Knows how to decide
 * which tables are active in the model and how to define the model to SDO.
 *
 * Knows how to navigate between the models, for example given a type to find the containing reference
 * and thence the supporting foreign key and thence the _from_ column
 */
require_once 'SDO/DAS/Relational/NonContainmentReference.php';

class SDO_DAS_Relational_ObjectModel {

    private $database_model;
    private $containment_references_model;
    private $non_containment_references;

    public function __construct($database_model, $containment_references_model)
    {
        $this->database_model = $database_model;
        $this->containment_references_model = $containment_references_model;
        $this->non_containment_references = array();
        $this->ensureBothModelsAgreeWithOneAnother();
    }

    public function getDatabaseModel()
    {
        return $this->database_model;
    }

    // tacit understanding that colnames = prop names and typenames = table names
    public function getPropertyRepresentingPrimaryKeyFromType($type)
    {
        $pk_column_name = $this->database_model->getPrimaryKeyFromTableName($type);
        return $pk_column_name;
    }

    public function getContainingReferenceFromChildType($type)
    {
        return $this->containment_references_model->getReferenceByChild($type);
    }

    public function getTheFKSupportingAContainmentReference($ref)
    {
        $parent = $ref->getParentName();
        $child =  $ref->getChildName();
        $fk_list = $this->database_model->getForeignKeys();
        foreach ($fk_list as $fk) {
            if ($fk->getToTableName() == $parent && $fk->getFromTableName() == $child) {
                return $fk;
            }
        }
        return null;
    }

    // TODO copied in from database model hence betrays heritage - confused over table/type, prop/column
    public function getTypesByColumnNameIgnoreCase($column_name)
    {
        foreach ($this->containment_references_model->getActiveTypes() as $type) {
            $columns = $this->database_model->getColumns($type);
            foreach ($columns as $col) {
                if (0==strcasecmp($col, $column_name)) {
                    $table_names_containing_column[$type] = $col;
                }
            }
        }
        return $table_names_containing_column;
    }

    public function isPrimitive($type_name,$property_name)
    {
        if ($this->isNonContainmentReferenceProperty($type_name, $property_name)) {
            return false;
        }
        if ($this->isContainmentReferenceProperty($type_name, $property_name)) {
            return false;
        }
        return true;
    }

    public function isNonContainmentReferenceProperty($type_name, $property_name)
    {
        foreach ($this->non_containment_references as $ncref) {
            if ($ncref->getTypeName() == $type_name && $ncref->getPropertyName() == $property_name) {
                return true;
            }
        }
        return false;
    }

    public function isContainmentReferenceProperty($type_name, $property_name)
    {
        foreach ($this->containment_references_model->getFullSetContainmentReferences() as $ref) {
            $parent = $ref->getParentName();
            $child =  $ref->getChildName();
            if ($parent == $type_name && $child == $property_name) {
                return true;
            }
        }
        return false;
    }

    public function getToTypeOfNonContainmentReferenceProperty($table_name, $column_name)
    {
        foreach ($this->non_containment_references as $ncref) {
            if ($ncref->getTypeName() == $table_name && $ncref->getPropertyName() == $column_name) {
                return $ncref->getToTypeName();
            }
        }
        assert(false);     // only called after test to ensure that it *is* a n-c-nref
    }

    private function ensureBothModelsAgreeWithOneAnother()
    {
        $this->ensureTheApplicationRootTypeIsAValidTable();
        $this->ensureTypesInReferencesModelAreValidTableNames();
        $this->ensureEachReferenceIsSupportedByAForeignKey();
    }

    private function ensureTheApplicationRootTypeIsAValidTable()
    {
        $app_root_type = $this->containment_references_model->getApplicationRootType();
        if ($app_root_type &&
             !$this->database_model->isValidTableName($app_root_type)) {
            throw new SDO_DAS_Relational_Exception('Application root type ' . $app_root_type . ' does not appear as a table name in the database metadata');
        }
    }

    public function ensureTypesInReferencesModelAreValidTableNames()
    {
        foreach ($this->containment_references_model->getFullSetContainmentReferences() as $ref) {
            $parent = $ref->getParentName();
            $child =  $ref->getChildName();
            if (!$this->database_model->isValidTableName($parent)) {
                throw new SDO_DAS_Relational_Exception('A reference specified a table name of ' . $parent . ' that was not specified in the database metadata');
            }
            if (!$this->database_model->isValidTableName($child)) {
                throw new SDO_DAS_Relational_Exception('A reference specified a table name of ' . $child . ' that was not specified in the database metadata');
            }
        }
    }

    private function ensureEachReferenceIsSupportedByAForeignKey()
    {
        foreach ($this->containment_references_model->getActiveContainmentReferences() as $ref) {
            if ($this->getTheFKSupportingAContainmentReference($ref) == null) {
                $parent = $ref->getParentName();
                $child =  $ref->getChildName();
                throw new SDO_DAS_Relational_Exception('No foreign key was found in the database model to support the reference with (parent => '.$parent. ', child => '. $child .')');
            }
        }
    }

    public function defineToSDO($data_factory)
    {
        if (SDO_DAS_Relational::DEBUG_BUILD_SDO_MODEL) {
            echo "===============================\n";
            echo "Building SDO model as follows:\n";
        }
        $this->defineTheRootTypeToSDO($data_factory);
        //$this->addAllTheActiveTypesToSDO($data_factory);
        $this->addAllTypesToSDO($data_factory);
        //$this->addTopLevelContainmentPropertyToSDO($data_factory);
        $this->addTopLevelContainmentPropertiesToSDO($data_factory);
        $this->addAllTheColumnsAsPropertiesToSDO($data_factory);
    }

    private function defineTheRootTypeToSDO($data_factory)
    {
        if (SDO_DAS_Relational::DEBUG_BUILD_SDO_MODEL) {
            echo "adding root type\n";
        }
        $data_factory->addType(SDO_DAS_Relational::DAS_NAMESPACE, SDO_DAS_Relational::DAS_ROOT_TYPE);
        $data_factory->addPropertyToType(SDO_DAS_Relational::DAS_NAMESPACE, SDO_DAS_Relational::DAS_ROOT_TYPE, 'cs', SDO_TYPE_NAMESPACE_URI, 'ChangeSummary');

    }

    private function addAllTheActiveTypesToSDO($data_factory)
    {
        $active_types = $this->containment_references_model->getActiveTypes();
        foreach ($active_types as $type) {
            if (SDO_DAS_Relational::DEBUG_BUILD_SDO_MODEL) {
                echo "adding type $type\n";
            }
            $data_factory->addType(SDO_DAS_Relational::APP_NAMESPACE, $type);
        }
    }

    private function addAllTypesToSDO($data_factory)
    {
        $all_types = $this->database_model->getAllTableNames();
        foreach ($all_types as $type) {
            if (SDO_DAS_Relational::DEBUG_BUILD_SDO_MODEL) {
                echo "adding type $type\n";
            }
            $data_factory->addType(SDO_DAS_Relational::APP_NAMESPACE, $type);
        }
    }

    private function addTopLevelContainmentPropertyToSDO($data_factory)
    {
        $app_root_type = $this->containment_references_model->getApplicationRootType();
        if (SDO_DAS_Relational::DEBUG_BUILD_SDO_MODEL) {
            echo "adding containment property $app_root_type to hidden root type\n";
        }
        $data_factory->addPropertyToType(
            SDO_DAS_Relational::DAS_NAMESPACE, SDO_DAS_Relational::DAS_ROOT_TYPE,
            $app_root_type,
            SDO_DAS_Relational::APP_NAMESPACE, $app_root_type,
            array('many' => true, 'containment' => true));
    }

    private function addTopLevelContainmentPropertiesToSDO($data_factory)
    {
        $non_contained_types = $this->containment_references_model->getAllNonContainedTypes();

        foreach ($non_contained_types  as $type ) {
            if (SDO_DAS_Relational::DEBUG_BUILD_SDO_MODEL) {
                echo "adding containment property $type to hidden root type\n";
            }

            $data_factory->addPropertyToType(
                SDO_DAS_Relational::DAS_NAMESPACE, SDO_DAS_Relational::DAS_ROOT_TYPE,
                $type,
                SDO_DAS_Relational::APP_NAMESPACE, $type,
                array('many' => true, 'containment' => true));
        }
    }

    private function addAllTheColumnsAsPropertiesToSDO($data_factory)
    {
        //////////////////////////////////////////////////////////////////////////
        // add all the columns as properties, taking special notice of columns which are
        // at the _from_ end of a foreign key. There are four cases to consider. It helps to have a
        // diagram in front of you. I will illustrate using the company/department/employee database.
        // 1. The column is the _from_ end of a FK, the _to_ table is in the active types,
        //    and there is no corresponding containment reference. An example is the employee_of_the_month
        //    reference in the company table.
        //    This is a non-containment reference from the type we are currently examining to the type of the _to_ table
        // 2. The column is the _from_ end of a FK, the to_table is in the model
        //    and there is a supporting containment reference; add as a containment reference on the
        //    to_table, pointing back to us.
        //    An example is when we are examining the columns of the department table and we come across the
        //    co_id column which points to the company table. Assuming the user wanted the company table as their root
        //    type, so that company is in the active types, then define a containment relationship from company to department.
        // 3. The column is the _from_ end of a FK but the to_table is not in the active types. This could occur if the user
        //    says they want the department to be their root type - they are going to work only with department and employee
        //    types and they do not want the company type in the model. In this case when we come across the co_id column int he
        //    the department table we cannot make it a containment relationship, we must just expose the field as a primitive
        //    and trust them to update it as they want. So, add as a primitive
        // 4. The column is not the _from_ end of a FK. This is everything else: add as a primitive
        //
        // The pseudocode is:
        // for each active type
        //     for each column within that type
        //         if there is a foreign key from this column (case 1,2,3)
        //             if the _to_ table of the foreign key is in the active type (case 1,2)
        //                 if there is a containment reference corresponding to the foreign key (case 2)
        //                     represent this column by a containment ref from the _to_table back to this type
        //                 else (case 1)
        //                     represent this column by a non-containment ref from this type to the _to_ table
        //              else (case 3)
        //                 represent this column by a primitive
        //          else (case 4)
        //              represent this column by a primitive
        //      end for each this column
        // end for each active type
        //////////////////////////////////////////////////////////////////////////
        $active_types = $this->containment_references_model->getActiveTypes();
        foreach ($active_types as $type) {
            $table      = $this->database_model->getTableByName($type);
            $columns    = $table->getColumns();
            foreach ($columns as $column) {
                $fk = $this->database_model->getForeignKeyByFromTableNameAndColumnName($type, $column);
                if ($fk != null) {
                    $to_table_name = $fk->getToTableName();
                    if (in_array($to_table_name, $active_types)) {
                        if ($this->containment_references_model->getReferenceByParentAndChild($to_table_name, $type)) {
                            $this->addContainmentRef($data_factory, $to_table_name, $type, $type); // ref name and to-type both == $type name
                        } else {
                            $this->addNonContainmentRef($data_factory, $type, $column, $to_table_name);
                            $this->non_containment_references[] = new SDO_DAS_Relational_NonContainmentReference($type, $column, $to_table_name);
                        }
                    } else {
                        $this->addPrimitive($data_factory, $type, $column);
                    }
                } else {
                    $this->addPrimitive($data_factory, $type, $column);
                }

            }
        }
    }

    private function addContainmentRef($data_factory, $from_type,$ref_name,$to_type)
    {
        if (SDO_DAS_Relational::DEBUG_BUILD_SDO_MODEL) {
            echo "add a containment ref from type $from_type to type $to_type called $ref_name\n";
        }
        $data_factory->addPropertyToType(
        SDO_DAS_Relational::APP_NAMESPACE, $from_type,
        $ref_name,
        SDO_DAS_Relational::APP_NAMESPACE, $to_type,
        array('many' => true, 'containment' => true));
    }

    private function addNonContainmentRef($data_factory, $from_type, $ref_name, $to_type)
    {
        if (SDO_DAS_Relational::DEBUG_BUILD_SDO_MODEL) {
            echo "add a non-containment ref from type $from_type to type $to_type called $ref_name\n";
        }
        $data_factory->addPropertyToType(
            SDO_DAS_Relational::APP_NAMESPACE, $from_type,
            $ref_name,
            SDO_DAS_Relational::APP_NAMESPACE, $to_type,
            array('many' => false, 'containment' => false));
    }

    private function addPrimitive($data_factory, $type,$prim_name)
    {
        if (SDO_DAS_Relational::DEBUG_BUILD_SDO_MODEL) {
            echo "add a primitive $prim_name to $type\n";
        }
        $data_factory->addPropertyToType(
            SDO_DAS_Relational::APP_NAMESPACE, $type,
            $prim_name,
            SDO_TYPE_NAMESPACE_URI, 'String',
            array('many' => false, 'containment' => false));
    }
}

?>