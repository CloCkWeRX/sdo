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
 * restresource binding. The reosurces here are XML objects read from
 * and written to file using SDO. This does not have to be the case
 * of course and any resource management scheme can be used.
 *
 * @service
 * @binding.restresource
 * @types urn::orderNS Orders.xsd
 */
class Orders implements SCA_Bindings_restresource_ResourceTemplate
{
    private $xmldas = null;
    private $doc    = null;
    private $orders = null;

    /**
     * The constructure here loads the resource collection
     * from the file system
     */
    public function __construct()
    {
        SCA::$logger->log("Entering constructor");
        $this->readOrders();
    }

   /**
     * Insert $resource into the resource collection
     *
     * @param OrderType $resource urn::orderNS
     * @return string absolute URL to the new resource
     *
     **/
    public function create($resource) {
        SCA::$logger->log("create resource");
        $this->orders->order[] = $resource;
        srand(time());
        $resource_id = "order" . rand();
        $resource->orderId = $resource_id;
        $this->writeOrders();

        // return the absolute URL of where this new resource
        // can be found
        $resource_url =  "http://" .
                         $_SERVER['HTTP_HOST'] .
                         "/" .
                         $_SERVER['REQUEST_URI'] .
                         "/" .
                         $resource_id;
        return $resource_url;
    }

    /**
     * returns the resource identified by $id
     *
     * @param string $id
     * @return OrderType urn::orderNS
     *
     **/
    public function retrieve($id) {
        SCA::$logger->log("retrieve resource $id");

        $return_order = null;

        foreach ($this->orders->order as $order) {
          if ($order->orderId == $id ) {
              $return_order = $order;
          }
        }

        // return the successfully retrieve resource
        // or null otherwise
        return $return_order;
    }

    /**
     * $id is a string that identifies a resource, $resource
     * is the new version of the resource for this id
     * returns an sdo
     *
     * @param string $id
     * @param OrderType $resource urn::orderNS
     **/
    public function update($id, $resource) {
        SCA::$logger->log("update resource");

        // make sure that the id has not been changed
        $resource->orderId = $id;

        $orderIndex = 0;
        foreach ($this->orders->order as $order) {
          if ($order->orderId == $id ) {
              $this->orders->order[$orderIndex] = $resource;
          }
          $orderIndex = $orderIndex + 1;
        }
        $this->writeOrders();

        // return true to indicate that the resource
        // was successfully updated or false otherwise
        return true;
    }

    /**
     * Deletes the resource for $id
     * returns void
     *
     * @param string $id
     **/
    public function delete($id) {
        SCA::$logger->log("delete resource");

        $orderIndex = 0;
        foreach ($this->orders->order as $order) {
          if ($order->orderId == $id ) {
              unset($this->orders->order[$orderIndex]);
          }
          $orderIndex = $orderIndex + 1;
        }
        $this->writeOrders();

        // return true to indicate that the resource was
        // successfully deleted or false otherwise
        return true;
    }

    /**
     * Returns all of the resources
     *
     * @return OrdersType urn::orderNS
     *
     **/
    public function enumerate() {
        SCA::$logger->log("enumerate resource collection");

        // return the collection of resources or null if the
        // collection is not available
        return $this->orders;
    }

    /**
     * a helper method to read all of the orders in from file
     */
    private function readOrders() {
        $this->xmldas = SDO_DAS_XML::create("./Orders.xsd");
        $this->doc    = $this->xmldas->loadFile("./Orders.xml");
        $this->orders = $this->doc->getRootDataObject();
    }

    /**
     * a helper method to write all of the orders out to file
     */
    private function writeOrders() {
        $this->xmldas->saveFile($this->doc, "./Orders.xml",2);
    }

}

// There is a issue with the PHP class_exists test when a class implements
// an interface. I.e. PHP doesn't think the class exists untile after it is
// declared in the script. Moving the SCA include to below our service
// declaration allows SCA to work normally
require_once 'SCA/SCA.php';
?>
