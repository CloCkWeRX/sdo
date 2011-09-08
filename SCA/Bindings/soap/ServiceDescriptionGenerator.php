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
 * $Id: ServiceDescriptionGenerator.php 254122 2008-03-03 17:56:38Z mfp $
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
 * SCA_Bindings_Soap_ServiceDescriptionGenerator
 *
 * @category SCA
 * @package  SCA_SDO
 * @author   Matthew Peters <mfp@php.net>
 * @license  Apache http://www.apache.org/licenses/LICENSE-2.0
 * @link     http://www.osoa.org/display/PHP/
 */
class SCA_Bindings_Soap_ServiceDescriptionGenerator
{
    /**
     * Generate
     *
     * @param string $service_description Service Description
     *
     * @return null
     */
    public function generate($service_description)
    {
        SCA::$logger->log('Entering');
        try {
            $str =  $this->generateDocumentLiteralWrappedWsdl($service_description);
            header('Content-type: text/xml');
            echo $str;
            SCA::$logger->log('Exiting having generated wsdl');
            //            SCA::$logger->log('The wsdl is ' . $str);
        }
        catch (SCA_RuntimeException $se )
        {
            echo $se->exceptionString() . "\n";
        } catch( SDO_DAS_XML_FileException $e) {
            echo "{$e->getMessage()} in {$e->getFile()}";
        }
    }

    const SOAP_NAMESPACE = 'http://schemas.xmlsoap.org/wsdl/soap/';
    const QUOTES         = '"';
    const COLON          = ':';
    const XS             = 'xs';

    /**
     * Generate Document/Literal Wrapped Wsdl
     *
     * @param string $service_desc Service Desc
     *
     * @return string
     */
    public static function generateDocumentLiteralWrappedWsdl($service_desc)
    {

        /***********************************************************************
        * Get a DAS, initialise a wsdl document, get the document element
        ***********************************************************************/
        $xmldas = SDO_DAS_XML::create(
            array(
                dirname(__FILE__) . '/2003-02-11.xsd',
                dirname(__FILE__) . '/soap/2003-02-11.xsd'
            )
        );
        // expect to find xsds along with the SCA code
        $wsdl_doc = $xmldas->createDocument();
        $wsdl     = $wsdl_doc->getRootDataObject();
        // this is the <definitions> element


        /***********************************************************************
        * Find our class name
        ***********************************************************************/
        $class_name = $service_desc->class_name;


        /***********************************************************************
        * Construct target namespace for this service
        ***********************************************************************/
        //        created in SCA bug #54277 $wsdl->targetNamespace = "http://{$class_name}/{$class_name}";
        $wsdl->targetNamespace = $service_desc->targetnamespace;

        /***********************************************************************
        * Create the types element
        * For the moment this is empty. This gets replaced at the bottom of this method.
        ***********************************************************************/
        $types = $wsdl->createDataObject('types');


        /***********************************************************************
        * Work out the location for the generated php file
        ***********************************************************************/
        if (array_key_exists('location', $service_desc->binding_config)) {
            $location = str_replace(' ', '%20', $service_desc->binding_config['location']);
        } else {
            $location = str_replace(' ', '%20', 'http://' . $service_desc->http_host . $service_desc->script_name);
        }


        /************************************************************************
        * Create the <service> element
        ***********************************************************************/
        $service         = $wsdl->createDataObject('service');
        $port             = $service->createDataObject('port');
        $soap_address     = $xmldas->createDataObject(self::SOAP_NAMESPACE, 'tAddress');
        $port->address     = $soap_address;

        $service->name     = "{$class_name}Service";
        $port->name     = "{$class_name}Port";
        $port->binding     = "{$wsdl->targetNamespace}#{$class_name}Binding";
        $soap_address->location = $location;


        /***********************************************************************
        * Create the <binding> element
        ***********************************************************************/
        $binding                     = $wsdl->createDataObject('binding');
        $binding->name                 = "{$class_name}Binding";
        $binding->type                 = "{$wsdl->targetNamespace}#{$class_name}PortType";
        $soap_binding                 = $xmldas->createDataObject(self::SOAP_NAMESPACE, 'tBinding');
        $soap_binding->style         = 'document';
        $soap_binding->transport     = 'http://schemas.xmlsoap.org/soap/http';
        $binding->binding             = $soap_binding;
        foreach ($service_desc->operations as $op_name => $params) {
            $binding_operation             = $binding->createDataObject('operation');
            $binding_operation->name     = $op_name;

            $soap_operation             = $xmldas->createDataObject(self::SOAP_NAMESPACE, 'tOperation');
            $soap_operation->soapAction = "";
            $binding_operation->operation    = $soap_operation;

            $bo_input                     = $binding_operation->createDataObject('input');
            $soap_body                     = $xmldas->createDataObject(self::SOAP_NAMESPACE, 'tBody');
            $bo_input->body             = $soap_body;
            $soap_body->use             = 'literal';

            $bo_output                     = $binding_operation->createDataObject('output');
            $soap_body                     = $xmldas->createDataObject(self::SOAP_NAMESPACE, 'tBody');
            $bo_output->body             = $soap_body;
            $soap_body->use             = 'literal';
        }


        /***********************************************************************
        * Create the <portType> element
        ***********************************************************************/
        $portType                     = $wsdl->createDataObject('portType');
        $portType->name             = "{$class_name}PortType";
        foreach ($service_desc->operations as $op_name => $params) {
            $portType_operation         = $portType->createDataObject('operation');
            $portType_operation->name     = $op_name;
            $pt_input                     = $portType_operation->createDataObject('input');
            $pt_input->message             = "{$wsdl->targetNamespace}#{$op_name}Request";
            $pt_output                     = $portType_operation->createDataObject('output');
            $pt_output->message         = "{$wsdl->targetNamespace}#{$op_name}Response";
        }


        /***********************************************************************
        * Create the <message> elements
        ***********************************************************************/
        foreach ($service_desc->operations as $op_name => $params) {
            $message = $wsdl->createDataObject('message');
            $part = $message->createDataObject('part');
            $message->name = "{$op_name}Request";
            $part->name = "{$op_name}Request";
            $part->element = "{$wsdl->targetNamespace}#{$op_name}";

            $message = $wsdl->createDataObject('message');
            $part = $message->createDataObject('part');
            $message->name = "{$op_name}Response";
            $part->name = "return";
            $part->element = "{$wsdl->targetNamespace}#{$op_name}Response";
        }


        /***********************************************************************
        * Generate the wsdl into $str
        ***********************************************************************/
        $str = $xmldas->saveString($wsdl_doc, 2);


        /***********************************************************************
        * Generate a <types> element and all contents
        ***********************************************************************/
        //Variable naming problem here - this $namespace is nothing to do with
        //$namespace in the foreach below? Will be affected by the foreach if it
        //did have something in it? Confusing.
        if (isset($service_desc->namespace)) {
            $namespace = $service_desc->namespace;
        } else {
            $namespace = null;
        }

        $types_element  = '<wsdl:types>'                                                         . "\n";
        $types_element .= '    <xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" '             . "\n";

        $namespace_to_prefix_map = array();

        /* When namespace annotation has been captured from the php file ....     */
        if (count($service_desc->xsd_types) > 0) {
            $xmlns_types  = "";
            $schema_types = "";
            $xsd_count    = 0;
            //            $namespace_to_prefix_map = array();
            foreach ($service_desc->xsd_types as $index => $xsds) {
                list($namespace, $xsdfile) = $xsds;
                $prefix = "ns$xsd_count";
                $xsd_count++;
                $namespace_to_prefix_map[$namespace] = $prefix;
                /* Create a dummy entry for each namespace and schema location        */
                $namespace_uri_in_quotes    = self::QUOTES . $namespace . self::QUOTES;
                $xmlns_types               .= "      xmlns:$prefix=$namespace_uri_in_quotes\n";
                $namespace_uri_in_quotes    = self::QUOTES . $namespace . self::QUOTES;
                $schema_location_in_quotes  = self::QUOTES . $xsdfile . self::QUOTES;
                $import_element             = "      <xs:import schemaLocation=$schema_location_in_quotes namespace=$namespace_uri_in_quotes/>\n";
                $schema_types              .= $import_element;
            }

            /* Make up the wsdl elements                                            */
            $types_element              .= $xmlns_types;
            $target_namespace_in_quotes  = self::QUOTES . $wsdl->targetNamespace . self::QUOTES;
            $types_element              .= "      targetNamespace=$target_namespace_in_quotes\n";
            $types_element              .= "      elementFormDefault=" . self::QUOTES . "qualified" . self::QUOTES . ">\n";
            $types_element              .= $schema_types;
        } else {
            /* No namespaces                                                        */
            $target_namespace_in_quotes  = self::QUOTES . $wsdl->targetNamespace . self::QUOTES;
            $types_element              .= "      targetNamespace=$target_namespace_in_quotes\n";
            $types_element              .= "      elementFormDefault=" . self::QUOTES . "qualified" . self::QUOTES . ">\n";
        }

        $nillable_attribute         = 'nillable=' . self::QUOTES . 'true' . self::QUOTES;
        foreach ($service_desc->operations as $op_name => $settings) {
            $op_name_in_quotes          = self::QUOTES . $op_name . self::QUOTES;
            $wrapped_operation_element  = '      <xs:element name="'. $op_name . '">' . "\n";
            $wrapped_operation_element .= '        <xs:complexType>'                     . "\n";
            $wrapped_operation_element .= '          <xs:sequence>'                     . "\n";

            foreach ($settings['parameters'] as $parameter) {
                $parameter_name_in_quotes = self::QUOTES . $parameter['name'] . self::QUOTES;
                $parameter_type           = ($parameter['type'] == 'object')
                ? $namespace_to_prefix_map[$parameter['namespace']] . self::COLON . $parameter['objectType']
                : self::XS . self::COLON . $parameter['type'];
                $parameter_type_in_quotes   = self::QUOTES . $parameter_type . self::QUOTES;
                if ($parameter['nillable']) {
                    $element_line               = "            <xs:element name=$parameter_name_in_quotes type=$parameter_type_in_quotes $nillable_attribute/>\n";
                } else {
                    $element_line               = "            <xs:element name=$parameter_name_in_quotes type=$parameter_type_in_quotes/>\n";
                }
                $wrapped_operation_element .= $element_line;
            }

            $wrapped_operation_element .= '          </xs:sequence>'                     . "\n";
            $wrapped_operation_element .= '        </xs:complexType>'                 . "\n";
            $wrapped_operation_element .= '      </xs:element>'                         . "\n";

            $wrapped_response_element  = '      <xs:element name="'. $op_name . 'Response">' . "\n";
            $wrapped_response_element .= '        <xs:complexType>'                     . "\n";
            $wrapped_response_element .= '          <xs:sequence>'                     . "\n";

            $return = $settings['return'];
            if ($return !== null) {
                $return_type = ($return[0]['type'] == 'object')
                ? $namespace_to_prefix_map[$return[0]['namespace']] . self::COLON . $return[0]['objectType']
                : self::XS . self::COLON . $return[0]['type'];
                $return_type_in_quotes     = self::QUOTES . $return_type . self::QUOTES;
                if ($return[0]['nillable']) {
                    $wrapped_response_element .= "            <xs:element name=\"{$op_name}Return\" type=$return_type_in_quotes $nillable_attribute/>\n";
                } else {
                    $wrapped_response_element .= "            <xs:element name=\"{$op_name}Return\" type=$return_type_in_quotes/>\n";
                }

            }

            $wrapped_response_element .= '          </xs:sequence>'                     . "\n";
            $wrapped_response_element .= '        </xs:complexType>'                 . "\n";
            $wrapped_response_element .= '      </xs:element>'                         . "\n";

            $types_element .= $wrapped_operation_element;
            $types_element .= $wrapped_response_element;
        }

        $types_element .= '    </xs:schema>'                                                 . "\n";
        $types_element .= '  </wsdl:types>'                                                         . "\n";

        /***********************************************************************
        * Find the <types/> element and replace it with the generated one
        ***********************************************************************/
        $types_pos = strpos($str, '<wsdl:types/>');
        $front     = substr($str, 0, $types_pos);     // the bit before <types/>
        $back      = substr($str, $types_pos + 13);    // the bit after <types/>
        $str       = $front . $types_element . $back;

        $our_comment = "<!-- this line identifies this file as WSDL generated by SCA for PHP. Do not remove -->";
        $str         = $str . "\n" . $our_comment;

        /***********************************************************************
        * Return the wsdl
        ***********************************************************************/
        return $str;
    }


}
