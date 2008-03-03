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
| Author: SL                                                           |
+----------------------------------------------------------------------+
$Id$
*/
include 'SCA/Bindings/restresource/ResourceTemplate.php';

/**
 * An example resource based service that provides access using the 
 * restresource binding and exercises a reference with the 
 * restresource binding
 * 
 * @service
 * @binding.restresource
 * @types urn::orderNS Orders.xsd
 */
class OrdersClient implements SCA_Bindings_restresource_ResourceTemplate
{
    /**
     * @reference
     * @binding.restresource http://localhost:80/examples/SCA/RestResource/Orders.php
     * @types urn::orderNS Orders.xsd
     */
    public $orders_service;
            
    /**
     * The constructure here loads the resource collection
     * from the file system
     */
    public function __construct()
    {
        SCA::$logger->log("Entering constructor");
    }
    
   /**
     * Insert $resource into the resource collection
     *
     * @param OrderType $resource urn::orderNS
     * @return string
     *
     **/
    public function create($resource){
        SCA::$logger->log("create resource");
        return $this->orders_service->create($resource);
    }

    /**
     * returns the resource identified by $id
     *
     * @param string $id
     * @return OrderType urn::orderNS    
     *
     **/
    public function retrieve($id){
        SCA::$logger->log("retrieve resource $id"); 
        return $this->orders_service->retrieve($id);
    }

    /**
     * $id is a string that identifies a resource, $resource
     * is the new version of the resource for this id
     * returns an sdo
     *
     * @param string $id     
     * @param OrderType $resource urn::orderNS     
     **/
    public function update($id, $resource){
        SCA::$logger->log("update resource");
        return $this->orders_service->update($id, $resource);          
    }

    /**
     * Deletes the resource for $id 
     * returns void
     *
     * @param string $id    
     **/        
    public function delete($id){
        SCA::$logger->log("delete resource");
        return $this->orders_service->delete($id);
    }

    /**
     * Returns all of the resources 
     *
     * @return OrdersType urn::orderNS
     *
     **/ 
    public function enumerate(){
        SCA::$logger->log("enumerate resource collection");
        return $this->orders_service->enumerate();
    }   
}

// There is a issue with the PHP class_exists test when a class implements
// an interface. I.e. PHP doesn't think the class exists untile after it is 
// declared in the script. Moving the SCA include to below our service
// declaration allows SCA to work normally
include 'SCA/SCA.php';
?>
