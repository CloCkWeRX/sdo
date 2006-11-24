<?php
/*
+-----------------------------------------------------------------------------+
| Copyright IBM Corporation 2006.                                             |
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
| Author: Graham Charters,                                                    |
|         Matthew Peters,                                                     |
|         Megan Beynon,                                                       |
|         Chris Miller.                                                       |
|                                                                             |
+-----------------------------------------------------------------------------+
$Id$
*/

/**
 * This class is always called when an incoming soap request is for an SCA component
 * Because we always generate doc/lit wrapped WSDL for SCA components, the incoming 
 * request will always have named parameters e.g. ticker => IBM. 
 * We need to strip the names off to call the component, i.e. to turn the 
 * single array of named parameters into a list of positional parameters.
 * Also need to make the return back into an SDO.
 *
 * This is the opposite of what we do in the SoapProxy
 */

if ( ! class_exists('SCA_ServiceWrapper', false) ) {
    class SCA_ServiceWrapper {

        private $base_class         = null ;
        private $xmldas             = null ;

        /**
         * Create the service wrapper for a SCA Component. 
         *
         * @param string $class_name
         * @param string $wsdl_filename
         */
        public function __construct( $class_name, $wsdl_filename )
        {
            $this->base_class = SCA::createInstanceAndFillInReferences($class_name);
            $this->xmldas     = SDO_DAS_XML::create($wsdl_filename);
        }/* End service wrapper constructor  */

        /**
         * Pass the call on to the business method in the component
         *
         * Unwrap the arguments first e.g. when the argument array is 
         * array('ticker' =. 'IBM') pull off the name part to make it array('IBM')
         * Then pass to the method
         * Then wrap the return value back into an SDO. The element name is 
         * ...Response with a property ...Return which contains the return value.
         */
        public function __call($method_name, $arguments)
        {
            $new_arguments_array = array();
            foreach ($arguments[0] as $arg) {
                $new_arguments_array[] = $arg;
            }
            try {
                $return = call_user_func_array(array(&$this->base_class,
                $method_name), $new_arguments_array);
            } catch ( Exception $e ) {
                $serialized_exception =  base64_encode(serialize($e));
                throw new SoapFault("Client", $e->__toString(), null,
                SCA_SoapProxy::SERIALIZED_EXCEPTION_HEADER . $serialized_exception);
            }

            $xdoc = $this->xmldas->createDocument($method_name . "Response");
            $response_object = $xdoc->getRootDataObject();
            $response_object[$method_name."Return"] = is_object($return)
            ? clone $return
            : $return;

            return $response_object;

        }/* End of call function                                                   */

    }/* End Service Wrapper class                                                  */

}/* End instance check                                                             */
?>