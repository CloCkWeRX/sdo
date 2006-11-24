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
$Id$
*/

require_once 'SDO/DAS/Relational/ContainmentReference.php';

/**
 * Contains the model for the SDO containment references
 *
 * Contains an unordered set of containment references
 * Check the connections between elements in the model.
 *
 * We want to know that the relationships between the tables in the model will form a graph that
 * SDO will recognise as valid. There are two main requirements:
 * 1. The containment relationships must form a rooted tree: that is, a connected acyclic
 *   graph with a vertex singled out as the root. We will follow the 
 *   parent-child relationships and check that there are no cycles. 
 *
 * 2. The non-containment relationships must not automatically cause closure to be violated.
 *   For example if in the model a table contains a foreign key to another table but that other table is 
 *   not in the model, then no graph can ever satisfy closure, so we check for that too. Of course the 
 *   final test is once the data graph is populated - closure might still be violated then too - but 
 *   that is another check at another time. 
 */

class SDO_DAS_Relational_ContainmentReferencesModel {

    private $full_set_containment_references = array();     // Full set of SDO_DAS_Relational_ContainmentReference
    private $active_containment_references = array();   // Only those reachable from app_root_type
    private $active_types;          // potentially a subset of the full set of types - just those reachable from root
    private $app_root_type;
    private $database_model;

    public function __construct($app_root_type, $containment_references_metadata, $database_model)
    {
        if ( $app_root_type != null ) {
            assert(gettype($app_root_type) == 'string');
        }
        
        assert(gettype($containment_references_metadata) == 'array');

        $this->app_root_type  = $app_root_type;
        $this->database_model = $database_model;

        foreach ($containment_references_metadata as $cref) {
            $this->full_set_containment_references[] = new SDO_DAS_Relational_ContainmentReference($cref);
        }

        $reachable_types_already_visited = array();
        
        // maintain app_root_type here for backward compatibility
        if ( $app_root_type != null ) {
            $reachable_types_still_to_check = array($app_root_type);
        } else {
            // we need to check all the types and find all of the ones for which no 
            // containment references are specified
            $reachable_types_still_to_check = $this->getAllNonContainedTypes();
        }
        
        $references_traversed = array();
        while (count($reachable_types_still_to_check) > 0) {
            $type = array_shift($reachable_types_still_to_check);
            if (in_array($type, $reachable_types_already_visited)) {
                throw new SDO_DAS_Relational_Exception('A cycle was found within the containment references: table ' . $type . ' was reachable by more than one way from ' . $app_root_type . '. This is not valid. The containment references must form a tree.');
            }
            $reachable_types_already_visited[] = $type;
            foreach ($this->full_set_containment_references as $ref) {
                if ($type == $ref->getParentName() ) {
                    $references_traversed[] = $ref;
                    $reachable_types_still_to_check[] = $ref->getChildName();
                }
            }
        }
        $this->active_types = $reachable_types_already_visited;
        $this->active_containment_references = $references_traversed;
    }
    

    public function getActiveContainmentReferences()
    {
        return $this->active_containment_references;
    }

    public function getFullSetContainmentReferences()
    {
        return $this->full_set_containment_references;
    }

    public function getActiveTypes()
    {
        return $this->active_types;
    }

    public function getApplicationRootType()
    {
        return $this->app_root_type;
    }


    public function isValidParentName($name)
    {
        foreach ($this->active_containment_references as $ref) {
            if ($name == $ref->getParentName()) {
                return true;
            }
        }
        return false;
    }

    public function getReferenceByParentAndChild($parent,$child)
    {
        foreach ($this->active_containment_references as $ref) {
            if ($ref->getParentName() == $parent  && $ref->getChildName() == $child) {
                return $ref;
            }
        }
        return null;
    }

    public function getReferenceByChild($child)
    {
        foreach ($this->active_containment_references as $ref) {
            if ($ref->getChildName() == $child) {
                return $ref;
            }
        }
        return null;
    }
    
    public function getAllNonContainedTypes()
    {
        $non_contained_types = array();
        $all_types = $this->database_model->getAllTableNames();
        
        // loop through all the types. If a type name doesn't appear
        // as a child in the containment meta-data then the type 
        // is non contained
        foreach ( $all_types as $type ) {           
            $contained = false;
            foreach ( $this->full_set_containment_references as $ref ) {
                if ( $ref->getChildName() == $type ) {
                    $contained = true;
                    break;
                }
            }
            if ( $contained == false ) {
                $non_contained_types[] = $type;
            }
        }
        
        return $non_contained_types;
    }
}

?>