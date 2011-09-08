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
 * $Id: Mapper.php 254122 2008-03-03 17:56:38Z mfp $
 *
 * PHP Version 5
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Matthew Peters <mfp@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */


/**
 * SCA_Bindings_soap_Mapper
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Matthew Peters <mfp@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */
class SCA_Bindings_Soap_Mapper
{

    const   SERVER     = "SoapServer";
    const   CLIENT     = "SoapClient";

    const   EOL               = "\n";

    protected $association  = null;
    protected $xmldas       = null;

    /**
     * Mapper
     *
     * @param mixed $association Association
     */
    public function __construct($association)
    {
        if ($association != self::CLIENT && $association != self::SERVER) {
            throw new SoapFault('Client', 'SCA_Bindings_soap_Mapper should be initialised with an association of SoapServer or SoapClient');
        }

        $this->association = $association;

    }

    /**
     * Provide access to the XML Data Access Service
     *
     * @return SDO_DAS_XML
     */
    public function getXmlDas()
    {
        return  $this->xmldas;
    }

    /**
     * Load the WSDL and hence initialise the SDO model
     *
     * @param string $wsdl WSDL
     *
     * @throws  SCA_RuntimeException
     * @return null
     */
    public function setWSDLTypes($wsdl)
    {
        SCA::$logger->log('Entering');
        SCA::$logger->log("wsdl is $wsdl");
        try {
            $this->xmldas = SDO_DAS_XML::create($wsdl);
        } catch (Exception $e) {
            $problem = $e->getMessage();
            SCA::$logger->log("exception thrown from create(): $problem");


            if ($e instanceof SDO_Exception) {
                $problem = "SDO_Exception in setWSDLTypes : " . $problem;
            }

            /**
             * Depending on whether the function is being used on the
             * client side or the server side either report the problem
             * to the client, or record the problem in the error.log
             */
            trigger_error($problem);

            /* When the 'TypeHandler is being used by the Soap Server */
            if (strcmp($this->association, self::SERVER) === 0) {
                throw new SoapFault("Client", "Invalid WSDL Type");
            }
        }
    }

    /**
     * Called to convert the content in an incoming request or response
     *
     * @param string $xml XML
     *
     * @return object
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
            if ($e instanceof SDO_Exception )

            /**
             * Depending on whether the function is being used on the client side
             * or the server side either report the problem to the client, or
             * record the problem in the error.log
             */
            trigger_error($problem);

            /* When the 'TypeHandler is being used by the Soap Server          */
            if (strcmp($this->association, self::SERVER) === 0) {
                throw new SoapFault(
                    "Client",
                    "Unable to decode the SOAP message from XML. The problem was: ".$problem
                );
            }

        }
    }

    /**
     * Called to convert contents (eg.SDOs) to an XML string to generate something
     * that represents the SOAP operation for outgoing request or response
     *
     * @param SDO $sdo SDO
     *
     * @return string
     */
    public function toXML($sdo)
    {
        SCA::$logger->log('Entering');
        SCA::$logger->log("sdo = $sdo");

        try
        {
            $xdoc   = $this->xmldas->createDocument('', 'BOGUS', $sdo);
            $xmlstr = $this->xmldas->saveString($xdoc, 0);

            /**
             * remove the xsi:type="<type of the top level element>" from the top
             * level element.
             *
             * For example, convert:
             * <?xml version="1.0" encoding="UTF-8"?>
               <BOGUS xsi:type="tns2:reverseResponse" xmlns:tns2="http://....
                  .../...
               </BOGUS>
             * to
             * <?xml version="1.0" encoding="UTF-8"?>
               <BOGUS xmlns:tns2="http://....
                  .../...
               </BOGUS>
             *
             * Be careful not to remove any other xsi:type's as they may be valid.
             *
             * Actually Tuscany SDO should not be putting these xsi:type attributes on
             * the top level element. They are invalid, and will cause the message
             * or reply to fail validation. The reason is that the messages and
             * reponses are defined as elements, not types. But it is hard to
             * argue the case when we are already building an sdo that would not
             * validate because the element name is BOGUS.
             * See PECL bug 11997
             *
             * Use preg to remove it. Note that we are relying on the xsi:type
             * always being the first attribute which is a bit dodgy, but it
             * always is.
             */
            $xmlstr = preg_replace('/BOGUS xsi:type="[^"]*"/', 'BOGUS', $xmlstr);

            SCA::$logger->log("xml = $xmlstr");
            return         $xmlstr;
        }
        catch( Exception $e )
        {
            $problem = $e->getMessage();
            if ($e instanceof SDO_Exception) {
                $problem = "SDO_Exception in toXML : " . $problem;
            }

            /**
             * Depending on whether the function is being used on the client side
             * or the server side either report the problem to the client, or
             * record the problem in the error.log
             */
            trigger_error($problem);

            /* When the 'TypeHandler is being used by the Soap Server          */
            if (strcmp($this->association, self::SERVER) === 0) {
                throw new SoapFault("Client", "Unable to encode to XML");
            }

        }
    }

    /**
     * Provide access to the createDataObject method of our encapsulated XML DAS,
     * so that the client or server can create the SDOs they want to send.
     *
     * @param string $ns       Namespace URI
     * @param string $typename Type name
     *
     * @return SDO
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
            if ($e instanceof SDO_Exception )
            $problem = "SDO_Exception in createDataObject : " . $problem;

            /**
             * Depending on whether the function is being used on the client side
             * or the server side either report the problem to the client, or
             * record the problem in the error.log
            */
            trigger_error($problem);

            /* When the 'TypeHandler is being used by the Soap Server          */
            if (strcmp($this->association, self::SERVER) === 0)
            throw new SoapFault("Client", "Unable to create data object");

        }

    }

    /**
     * Returns array of type handlers
     *
     * @return  array
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
            $ret[] = array(
                "type_ns"    => $type[0],
                "type_name"  => $type[1],
                "to_xml"     => $encoder_callback,
                "from_xml"   => $decoder_callback
            );
        }
        return $ret;
    }

    /**
     * Returns array of all types
     *
     * @return  array
     */
    public function getAllTypes()
    {
        $str   = $this->xmldas->__toString();
        $types = array();
        $line  = strtok($str, self::EOL);
        $line = strtok(self::EOL); // skip line that says this is an SDO
        $line = strtok(self::EOL); // skip line that says nn types have been defined

        while ($line !== false && strpos($line, 'commonj.sdo')) {
            $line = strtok(self::EOL);
        }
        while ($line !== false && $line != '}') {
            $trimmed_line = trim($line);
            $words        = explode(' ', $trimmed_line);
            if ($words[0] == '-') {
                $line = strtok(self::EOL);
                continue;
            }
            $namespace_and_type = $words[1];
            $pos_last_hash      = strrpos($namespace_and_type, '#');
            $namespace          = substr($namespace_and_type, 0, $pos_last_hash);
            $type               = substr($namespace_and_type, $pos_last_hash+1);
            if ($type != 'RootType') {
                $types[]        = array($namespace, $type);
            }
            $line               = strtok(self::EOL);
        }
        return $types;
    }

}
