--TEST--
Generate a service description (WSDL)
--FILE--
<?php

require "SCA/SCA.php";
require "SCA/Bindings/soap/ServiceDescriptionGenerator.php";
$component_file = str_replace('004.php', 'Component.php', __FILE__);
$service_description = SCA::constructServiceDescription($component_file);
$wsdl = SCA_Bindings_soap_ServiceDescriptionGenerator::generateDocumentLiteralWrappedWsdl($service_description);
echo substr($wsdl,0,strpos($wsdl,'location'));

?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:tns2="http://Component" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" targetNamespace="http://Component">
  <types>
    <xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" 
      xmlns:ns0="http://www.test.com/info"
      targetNamespace="http://Component"
      elementFormDefault="qualified">
      <xs:import schemaLocation="person.xsd" namespace="http://www.test.com/info"/>
      <xs:element name="reverse">
        <xs:complexType>
          <xs:sequence>
            <xs:element name="in" type="xs:string"/>
          </xs:sequence>
        </xs:complexType>
      </xs:element>
      <xs:element name="reverseResponse">
        <xs:complexType>
          <xs:sequence>
            <xs:element name="reverseReturn" type="xs:string"/>
          </xs:sequence>
        </xs:complexType>
      </xs:element>
      <xs:element name="add">
        <xs:complexType>
          <xs:sequence>
            <xs:element name="person" type="ns0:person"/>
            <xs:element name="phone" type="ns0:phone"/>
          </xs:sequence>
        </xs:complexType>
      </xs:element>
      <xs:element name="addResponse">
        <xs:complexType>
          <xs:sequence>
            <xs:element name="addReturn" type="ns0:person"/>
          </xs:sequence>
        </xs:complexType>
      </xs:element>
    </xs:schema>
  </types>

  <message name="reverseRequest">
    <part name="reverseRequest" element="tns2:reverse"/>
  </message>
  <message name="reverseResponse">
    <part name="return" element="tns2:reverseResponse"/>
  </message>
  <message name="addRequest">
    <part name="addRequest" element="tns2:add"/>
  </message>
  <message name="addResponse">
    <part name="return" element="tns2:addResponse"/>
  </message>
  <portType name="ComponentPortType">
    <operation name="reverse">
      <input message="tns2:reverseRequest"/>
      <output message="tns2:reverseResponse"/>
    </operation>
    <operation name="add">
      <input message="tns2:addRequest"/>
      <output message="tns2:addResponse"/>
    </operation>
  </portType>
  <binding name="ComponentBinding" type="tns2:ComponentPortType">
    <soap:binding transport="http://schemas.xmlsoap.org/soap/http" style="document"/>
    <operation name="reverse">
      <soap:operation soapAction=""/>
      <input>
        <soap:body use="literal"/>
      </input>
      <output>
        <soap:body use="literal"/>
      </output>
    </operation>
    <operation name="add">
      <soap:operation soapAction=""/>
      <input>
        <soap:body use="literal"/>
      </input>
      <output>
        <soap:body use="literal"/>
      </output>
    </operation>
  </binding>
  <service name="ComponentService">
    <port name="ComponentPort" binding="tns2:ComponentBinding">
      <soap:address