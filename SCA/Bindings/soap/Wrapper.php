<?php
/**
 * +-----------------------------------------------------------------------------+
 * | (c) Copyright IBM Corporation 2006, 2007.                                   |
 * | All Rights Reserved.                                                        |
 * +-----------------------------------------------------------------------------+
 * | Licensed under the Apache License, Version 2.0 (the "License"); you may not |
 * | use this file except in compliance with the License. You may obtain a copy  |
 * | of the License at -                                                         |
 * |                                                                             |
 * |                   http://www.apache.org/licenses/LICENSE-2.0                |
 * |                                                                             |
 * | Unless required by applicable law or agreed to in writing, software         |
 * | distributed under the License is distributed on an "AS IS" BASIS, WITHOUT   |
 * | WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.            |
 * | See the License for the specific language governing  permissions and        |
 * | limitations under the License.                                              |
 * +-----------------------------------------------------------------------------+
 * | Author: Graham Charters,                                                    |
 * |         Matthew Peters,                                                     |
 * |         Megan Beynon,                                                       |
 * |         Chris Miller,                                                       |
 * |         Caroline Maynard,                                                   |
 * |         Simon Laws                                                          |
 * +-----------------------------------------------------------------------------+
 * $Id: Wrapper.php 254122 2008-03-03 17:56:38Z mfp $
 *
 * PHP Version 5
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Matthew Peters <mfp@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */

require_once "SCA/Bindings/soap/Proxy.php";

/**
 * This class is always called when an incoming soap request is for an SCA component
 * Because we always generate doc/lit wrapped WSDL for SCA components, the incoming
 * request will always have named parameters e.g. ticker => IBM.
 * We need to strip the names off to call the component, i.e. to turn the
 * single array of named parameters into a list of positional parameters.
 * Also need to make the return back into an SDO.
 *
 * This is the opposite of what we do in the SoapProxy
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Matthew Peters <mfp@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */
class SCA_Bindings_Soap_Wrapper
{
    protected $instance_of_the_base_class = null;
    protected $xmldas                     = null;
    protected $class_name                 = null;

    /**
     * Class constructor
     *
     * @param string $class_name Class name
     * @param string $handler    Handler
     */
    public function __construct($class_name, $handler)
    {
        SCA::$logger->log('Entering');
        SCA::$logger->log("class name = $class_name");

        $this->class_name = $class_name;

        $this->xmldas                     = $handler->getXmlDas();
        $this->instance_of_the_base_class = SCA::createInstance($class_name);
        SCA::fillInReferences($this->instance_of_the_base_class);

        SCA::$logger->log('Exiting');
    }

    /**
     * Pass the call on to the business method in the component
     *
     * Unwrap the arguments first e.g. when the argument array is
     * array('ticker' =. 'IBM') pull off the name part to make it array('IBM')
     * Then pass to the method
     * Then wrap the return value back into an SDO. The element name is
     * ...Response with a property ...Return which contains the return value.
     *
     * @param string $method_name Method Name
     * @param string $arguments   Arguments
     *
     * @return mixed
     */
    public function __call($method_name, $arguments)
    {
        $new_arguments_array = array();
        foreach ($arguments[0] as $arg) {
            $new_arguments_array[] = $arg;
        }

        try {
            $return = call_user_func_array(
                array(&$this->instance_of_the_base_class, $method_name),
                $new_arguments_array
            );
        } catch (Exception $e) {
            if ($e instanceof SoapFault) {
                throw $e;
            } else {
                throw new SoapFault('Client', $e->getMessage());
            }
        }

        $namespace = 'http://' . $this->class_name;
        $xdoc = $this->xmldas->createDocument($namespace, $method_name . "Response");
        $response_object = $xdoc->getRootDataObject();

        $response_object[$method_name."Return"] = is_object($return) ? clone $return : $return;

        return $response_object;
    }
}
