<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2007.                                         |
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
|         Chris Miller,                                                       |
|         Caroline Maynard,                                                   |
|         Simon Laws                                                          |
+-----------------------------------------------------------------------------+
$Id: Mapper.php 238265 2007-06-22 14:32:40Z mfp $
*/

if ( ! class_exists('SCA_Bindings_ebaysoap_Mapper', false) ) {
    class SCA_Bindings_ebaysoap_Mapper extends SCA_Bindings_soap_Mapper 
    {
       /**
         * Load the WSDL and hence initialise the SDO model
         *
         * @param string $wsdl
         * @throws  SCA_RuntimeException
         */
        public function setWSDLTypes($wsdl)
        {
            SCA::$logger->log('Entering');
            SCA::$logger->log("wsdl is $wsdl");
            try {
                $this->xmldas = @SDO_DAS_XML::create($wsdl,"WEARETHEEBAYSOAPBINDING");
            } catch ( Exception $e ) {
                $problem = $e->getMessage();
                SCA::$logger->log("exception thrown from create(): $problem");


                if ( $e instanceof SDO_Exception )
                $problem = "SDO_Exception in setWSDLTypes : " . $problem ;

                /**
                     * Depending on whether the function is being used on the
                     * client side or the server side either report the problem
                     * to the client, or record the problem in the error.log
                     */
                trigger_error($problem);

                /* When the 'TypeHandler is being used by the Soap Server */
                if ( strcmp($this->association, self::SERVER) === 0 )
                throw new SoapFault("Client", "Invalid WSDL Type");
            }

        }

    }/* End instance check                                                         */
}/* End SCA_Bindings_soap_SDO_TypeHandler class                                                      */