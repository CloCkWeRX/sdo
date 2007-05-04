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
|         Simon Laws                                                          |
+-----------------------------------------------------------------------------+
$Id: SDO_TypeHandler.php,v 1.1.2.3.4.1 2007/04/05 14:15:58 mfp Exp $
*/

if ( ! class_exists('SCA_Bindings_soap_SDO_TypeHandler', false) ) {
    class SCA_Bindings_soap_SDO_TypeHandler /*implements TypeHandler*/ {

        const   SERVER     = "SoapServer" ;
        const   CLIENT     = "SoapClient" ;

        private $association  = null ;
        private $xmldas       = null ;

        public function __construct( $association )
        {
            if ($association != self::CLIENT && $association != self::SERVER )
            throw new SoapFault('Client', 'SCA_Bindings_soap_SDO_TypeHandler should be initialised with an association of SoapServer or SoapClient');
            $this->association = $association ;

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
                $this->xmldas = SDO_DAS_XML::create($wsdl,$wsdl);
                // looks crazy, I know. The first $wsdl is the file to
                // be parsed, the second is using the filename as a key
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
                SoapServer::fault("Client", "Invalid WSDL Type");
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

                /**
                 * Depending on whether the function is being used on the client side
                 * or the server side either report the problem to the client, or
                 * record the problem in the error.log
                 */
                trigger_error($problem);

                /* When the 'TypeHandler is being used by the Soap Server          */
                if (strcmp($this->association, self::SERVER) === 0)
                SoapServer::fault("Client", "Unable to decode from XML");

            }/* End trap the problem                                               */


        }/* End fromXML function                                                   */

        /**
        * Called to convert contents (eg.SDOs) to an XML string to generate something
        * that represents the SOAP operation for outgoing request or response
        *
        */
        public function toXML($sdo)
        {
            SCA::$logger->log('Entering');
            SCA::$logger->log("sdo = $sdo");

            try
            {
                $xdoc   = $this->xmldas->createDocument('', 'BOGUS', $sdo);
                $xmlstr = $this->xmldas->saveString($xdoc, 0);
                SCA::$logger->log("xml = $xmlstr");
                return         $xmlstr;
            }
            catch( Exception $e )
            {
                $problem = $e->getMessage();
                if ( $e instanceof SDO_Exception )
                $problem = "SDO_Exception in toXML : " . $problem ;

                /**
                 * Depending on whether the function is being used on the client side
                 * or the server side either report the problem to the client, or
                 * record the problem in the error.log
                 */
                trigger_error($problem);

                /* When the 'TypeHandler is being used by the Soap Server          */
                if ( strcmp($this->association, self::SERVER) === 0)
                SoapServer::fault("Client", "Unable to encode to XML");

            }/* End trap problem                                                   */
        }/* End toXML function                                                     */



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

                /**
                 * Depending on whether the function is being used on the client side
                 * or the server side either report the problem to the client, or
                 * record the problem in the error.log
                */
                trigger_error($problem);

                /* When the 'TypeHandler is being used by the Soap Server          */
                if (strcmp($this->association, self::SERVER) === 0)
                SoapServer::fault("Client", "Unable to create data object");

            }/* End of trap                                                        */

        }/* End create data object function                                        */

        /**
        * Returns array of type handlers
        */
        public function getTypeMap()
        {
            SCA::$logger->log('Entering');
            $encoder_callback = array($this,"toXML");
            $decoder_callback = array($this,"fromXML");
            $types            = $this->getAllTypes();


            $ret = array();
            foreach ($types as $type) {
                SCA::$logger->log("Adding callback for $type[0]#$type[1]");
                $ret[] =
                array(
                "type_ns"    => $type[0],
                "type_name"  => $type[1],
                "to_xml"     => $encoder_callback,
                "from_xml"   => $decoder_callback) ;
            }
            return $ret;
        }

        const   EOL               = "\n" ;

        public function getAllTypes()
        {
            $str   = $this->xmldas->__toString();
            $types = array();
            $line  = strtok($str, self::EOL);
            while ($line !== false) {
                if (strpos($line, 'RootType') !== false)
                break;
                $line = strtok(self::EOL);
            }
            $line = strtok(self::EOL);

            while ($line !== false) {
                // format changed recently - namespace and type separated by #
                // instead of :
                // quick fix to try and deal with either
                if (strpos($line,'#') !== false) {
                    $trimmed_line = trim($line);
                    $words        = explode(' ', $trimmed_line);
                    if ($words[0] !== '-') break;
                    $namespace_and_type_in_parens = $words[2];
                    $namespace_and_type           = substr($namespace_and_type_in_parens, 1, strlen($namespace_and_type_in_parens)-2);
                    $pos_last_hash                = strrpos($namespace_and_type, '#');
                    $namespace                    = substr($namespace_and_type, 0, $pos_last_hash);
                    $type                         = substr($namespace_and_type, $pos_last_hash+1);
                    $types[]                      = array($namespace, $type);
                    $line                         = strtok(self::EOL);
                } else {
                    $trimmed_line = trim($line);
                    $words        = explode(' ', $trimmed_line);
                    if ($words[0] !== '-') break;
                    $namespace_and_type_in_parens = $words[2];
                    $namespace_and_type           = substr($namespace_and_type_in_parens, 1, strlen($namespace_and_type_in_parens)-2);
                    $pos_last_colon               = strrpos($namespace_and_type, ':');
                    $namespace                    = substr($namespace_and_type, 0, $pos_last_colon);
                    $type                         = substr($namespace_and_type, $pos_last_colon+1);
                    $types[]                      = array($namespace, $type);
                    $line                         = strtok(self::EOL);
                }
            }
            return $types;

        }

    }/* End instance check                                                         */
}/* End SCA_Bindings_soap_SDO_TypeHandler class                                                      */

?>