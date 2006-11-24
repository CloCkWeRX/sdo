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

require "SCA_ServiceUnavailableException.php";

/**
 *
 * Purpose:
 * To ensure that a SOAP client is initialised to provide access to the 
 * remote target.
 * Also acts as a data factory for complex data types. 
 * Note the script has a error handler to trap 'trigger_error' responses 
 * from the SDO_TypeHandler.
 *
 * Public Methods:
 *
 * __construct()
 * This method initialises a SOAP client that will be able to handle SDOs 
 * passed to it.
 *
 * __call()
 * passes the call on to the soap client but also decides if the call is to an 
 * SCA component - and if so packs up the parameter list into an SDO and unpacks 
 * the return value
 *
 * createDataOject()
 * Calls the method on SOAP client. If the target is an SCA Component, 
 * the arguments are forwarded within an SDO conforming to the structure of the 
 * SOAP message defined in the WSDL. 
 * If the target is not an SCA Component, the arguments are forwarded unchanged.
 *
 * createDataOject()
 * This method returns an SDO conforming to the data model specified in the 
 * parameters.
 *
 *
 * getLastSoapRequest() and getLastSoapRequestHeaders()
 * getLastSoapResponse() and getLastSoapResponseHeaders()
 * These methods enable the client to inspect the header and message parts of the 
 * SOAP request and response.
 *
 *
 * Private Methods:
 *
 * _getSoapOperationSdo()
 * _copyPositionalArgumentsIntoSdoProperties()
 * These methods are used when making a call to an SCA Component, 
 * to combine the method name and argument list into a single SDO matching the 
 * corresponding data structure described in the WSDL.
 *
 * _passTheCallToTheSoapClient()
 * pass the call, wrapped in a try catch block
 * _convertedSoapFault()
 * This method is used to convert a SOAP Fault into the appropriate SCA Exception.
 *
 */

if ( ! class_exists('SCA_SoapProxy', false) ) {
     /**
      * Callback Error Handler to trap trigger_error calls from the SDO_TypHandler
      * any other trigger_error event will be handled by the default handler in
      * the normal manner.
      *
      * @param int $errno        Error Level
      * @param string $errstr    Error Message
      * @param string $errfile   File in which the error occured
      * @param string $errline   Line number on which the error occured
      * @return boolean          'false' to chain to default handler
      */
    function errorHandler(  $errno, $errstr, $errfile, $errline )
    {
        if ( strpos($errstr, "SDO_Exception") !== false )
        throw new SCA_RuntimeException($errstr);

        return false ;

    }/* End callback error handler                                                 */


    class SCA_SoapProxy
    {
        const SERIALIZED_EXCEPTION_HEADER = "Here follows a serialized and byte64-encoded PHP exception, placed here by SCA for PHP: ";
        private $soap_client;
        private $wsdl_file_name;

        private $previousErrorHandler ;

        public function __construct($absolute_path_to_target_wsdl)
        {
            /* Catch trigger_errors from SDO_TypeHandler                           */
            $this->previousErrorHandler = set_error_handler('errorHandler');

            //TODO recast these two lines into a call to the constructor.
            $handler = new SDO_TypeHandler("SoapClient");

            try {
                $handler -> setWSDLTypes($absolute_path_to_target_wsdl);
            } catch( SCA_RuntimeException $se ) {
                if (substr($absolute_path_to_target_wsdl, 0, 5) == 'http:'
                && strpos($se->getMessage(), 'SDO_Exception') !== false
                && strpos($se->getMessage(), 'Unable to parse') !== false
                && strpos($se->getMessage(), 'Document is empty') !== false) {
                    throw new SCA_RuntimeException('A call to SCA specified a URL: '
                    . $absolute_path_to_target_wsdl
                    . " The document returned was empty. One explanation for this may be apache bug 39662. See http://issues.apache.org/bugzilla/show_bug.cgi?id=36692. You may need to obtain the WSDL in a browser and save it as a local file.");
                }
                throw $se ;

            }
            $this->soap_client = new SoapClient($absolute_path_to_target_wsdl,
            array ( "trace" => 1, "exceptions" => 1,
            'typemap' => $handler->getTypeMap()));

            if ($this->soap_client === null) {
                throw new SCA_RuntimeException("Creation of Soap Client for target $absolute_path_to_target_wsdl failed.");
            }

            $this->wsdl_file_name    = $absolute_path_to_target_wsdl;

        }

        /**
         * Here is where we turn the list of positional parameters into a
         * single array of name parameters e.g. 'IBM' into array('ticker'=>'IBM')
         * This is the opposite of what we do in the ServiceWrapper
         *
         * We have to find the names to be given to each parameter
         * which we currently do by creating an SDO document and an SDO within it
         * then assigning the parameters one by one to the properties of the data object
         *
         * There is an inherent assumption that as we iterate through the
         * properties of the object we will get them in the same order as the
         * wsdl, and that this order is in turn the same as the order of the
         * parameters in the call. This relies on the user to put the @param
         * annotations in the right order in the annotations.
         */
        public function __call($method_name, $arguments)
        {
            if (SCA_Helper::wsdlWasGeneratedForAnScaComponent($this->wsdl_file_name)) {
                $return        = null ;
                $operation_sdo = null ;

                /* Break out of the 'call' in the event of an SDO problem               */
                try
                {
                    $operation_sdo = $this->_getSoapOperationSdo($method_name, $arguments);
                }
                catch( SDO_Exception $sdoe )
                {
                    // TODO - the carefully constructed logic below would seem to
                    // present a good change of losing exception detail so
                    // I'm commenting it out for the time being
                    //
                    //         $reason =  $sdoe->__toString();

                    //         if ( strpos($reason, $method_name) !== false ) {
                    //             throw new SCA_RuntimeException("Method '{$method_name}' could not be found in your Service pointed at by {$this->wsdl_file_name}", SCA_RuntimeException::SEVERE);
                    //         } else {
                    throw new SCA_RuntimeException($sdoe->getMessage());
                    //         }
                }

                $operation_array = array($operation_sdo);
                $return          = $this->_passTheCallToTheSoapClient($method_name,
                $operation_array);

                return $return[$method_name.'Return'];

            } else {
                return $this->_passTheCallToTheSoapClient($method_name, $arguments);
            }
        }

        private function _passTheCallToTheSoapClient($method_name, $arguments)
        {
            try
            {
                $return = call_user_func_array(array(&$this->soap_client, $method_name), $arguments);
            }
            catch( SoapFault $sfe )
            {
                $converted_soap_fault = $this->_convertedSoapFault($sfe);
                throw $converted_soap_fault;
            }
            return $return;

        }
        private function _getSoapOperationSdo($method_name, $arguments)
        {
            $xmldas        = SDO_DAS_XML::create($this->wsdl_file_name);
            $xdoc          = $xmldas->createDocument($method_name);
            $operation_sdo = $xdoc->getRootDataObject();
            $operation_sdo = $this->_copyPositionalArgumentsIntoSdoProperties($operation_sdo, $arguments);
            return         $operation_sdo;
        }

        private function _copyPositionalArgumentsIntoSdoProperties($operation_sdo, $arguments)
        {
            $reflection = new SDO_Model_ReflectionDataObject($operation_sdo);
            $type       = $reflection->getType();
            $i          = 0;
            foreach ($type->getProperties() as $property) {
                $arg                                 = $arguments[$i];
                $operation_sdo[$property->getName()] = is_object($arg)
                ? clone $arg : $arg;
                $i++;
            }
            return $operation_sdo;

        }


        //SoapClient was checked when created, so it must be there.
        //Four methods used to forward requests to the equivalent method on the soap client:
        //names are derived from the soap client methods: add 'soap' and remove __
        public function getLastSoapResponse()
        {
            $response = $this->soap_client->__getLastResponse();
            return    $response ;
        }

        public function getLastSoapResponseHeaders()
        {
            $response = $this->soap_client->__getLastResponseHeaders();
            return    $response ;
        }

        public function getLastSoapRequest()
        {
            $response = $this->soap_client->__getLastRequest();
            return    $response ;
        }

        public function getLastSoapRequestHeaders()
        {
            $response = $this->soap_client->__getLastRequestHeaders();
            return    $response ;
        }



        public function createDataObject($namespace_uri, $type_name)
        {
            try {
                $xmldas = SDO_DAS_XML::create($this->wsdl_file_name);
                $object = $xmldas->createDataObject($namespace_uri, $type_name);
                return  $object;
            } catch( Exception $e ) {
                throw new SCA_RuntimeException($e->getMessage());
            }
        }

        /**
         * Convert the Soap Fault to the Exception that is serialized in
         * the 'faultstring'
         *
         * @param  SoapFault  ( Contains a serialized exception )
         * @return  Exception   ( Unserialized Exception  or  an Exception about an Exception )
         */
        private function _convertedSoapFault( $fault )
        {
            $unable_to_deserialize_msg = "A remote SCA component threw an exception. "
            . "An attempt was made to pass back the exception and rethrow it but this failed. "
            . "Sometimes this is because the definition of the exception is not known at the calling end. "
            . "The text of the original exception was: \n";
            $soap_client_noretry_error_msg = "The PHP SOAP client threw an exception with faultcode HTTP "
            . "and faultstring Client Error. This usually indicates this request "
            . "is not worth retrying. "
            . "The faultstring from the soap fault was: \n";
            $soap_client_retryable_error_msg = "The PHP SOAP client threw an exception with faultcode HTTP "
            . "but the fault string did not say Client. "
            . "This indicates the request may be worth retrying. "
            . "The faultstring from the soap fault was: \n";
            $remote_service_threw_soap_fault = "The remote service threw a soap fault. "
            . "The faultstring from the soap fault was: \n";

            // might be "SOAP-ENV:Client" if thrown by soap extension, or "Client" if thrown by SCA_ServiceWrapper
            if ( strpos($fault->faultcode, "Client") !== false) {
                if (strpos($fault->detail, "SCA for PHP") !== false) {
                    $headerlen            = strlen(self::SERIALIZED_EXCEPTION_HEADER);
                    $serialized_exception = substr($fault->detail, $headerlen);
                    $recreateExpn         = unserialize(base64_decode($serialized_exception));
                    if ( $recreateExpn instanceof Exception ) {
                        return $recreateExpn;
                    } else { // we were unable to de-serialize it - likely because no definition exists at this end
                        return new SCA_RuntimeException($unable_to_deserialize_msg
                        . $fault->faultstring);
                    }
                } else { // the soap fault did not contain a serialized exception
                    return new SCA_RuntimeException($remote_service_threw_soap_fault
                    . $fault->faultstring);
                }
            } else if ( strpos($fault->faultcode, "HTTP") !== false) {
                if (strpos($fault->faultstring, "Client") !== false) {
                    return new SCA_RuntimeException($soap_client_noretry_error_msg 
                    . $fault->faultstring);
                } else {
                    return new SCA_ServiceUnavailableException($soap_client_retryable_error_msg
                    . $fault->faultstring);
                }
            } else {
                return new SCA_RuntimeException($remote_service_threw_soap_fault 
                . $fault->faultstring);
            }
        }
    }/* End soap proxy class                                                       */

}/* End instance check                                                             */

?>