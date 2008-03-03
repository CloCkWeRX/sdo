<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006, 2007.                                   |
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
|         Simon Laws,                                                         |
|         Wangkai Zai.                                                        |
+-----------------------------------------------------------------------------+
*/
/*
        This class is a modified version of SCA_Bindings_soap_Mapper
*/

if ( ! class_exists('SCA_Bindings_message_Mapper', false) ) { 
    class SCA_Bindings_message_Mapper {

        protected $xmldas       = null ;

        public function __construct()
        {
        }

        /**
         * Provide access to the XML Data Access Service
         *
         * @return SDO_DAS_XML
         */
        public function getXmlDas() {
            return  $this->xmldas;
        }

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
                $this->xmldas = @SDO_DAS_XML::create($wsdl);
            } catch ( Exception $e ) {
                $problem = $e->getMessage();
                SCA::$logger->log("exception thrown from create(): $problem");

                if ( $e instanceof SDO_Exception )
                $problem = "SDO_Exception in setWSDLTypes : " . $problem ;

                trigger_error($problem);

            }
        }

        /**
         * Called to convert the content in an incoming request or response
         */
        public function fromXML($xml)
        {
            SCA::$logger->log('Entering');
            SCA::$logger->log("xml = $xml");

            try
            {
                $doc = $this->xmldas->loadString($xml);
                $ret = $doc->getRootDataObject();
                return         $ret;
            }
            catch( Exception $e )
            {
                $problem = $e->getMessage();
                if ( $e instanceof SDO_Exception )
                $problem = "SDO_Exception in fromXML : " . $problem ;

                trigger_error($problem);

            }/* End trap the problem                                               */


        }/* End fromXML function                                                   */

        /**
        * Provide access to the createDataObject method of our encapsulated XML DAS,
        * so that the client or server can create the SDOs they want to send.
        */
        public function createDataObject($ns, $typename)
        {
            try
            {
                return  $this->xmldas->createDataObject($ns, $typename);

            }
            catch( Exception $e )
            {
                $problem = $e->getMessage();
                if ( $e instanceof SDO_Exception )
                $problem = "SDO_Exception in createDataObject : " . $problem ;

                trigger_error($problem);

            }

        }/* End create data object function                                        */


    }/* End instance check                                                         */
}/* End SCA_Bindings_message_Mapper class                                                      */

?>