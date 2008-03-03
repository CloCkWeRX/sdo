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
<wsdl:definitions xmlns:tns2="http://Component" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" targetNamespace="http://Component">
  <wsdl:types>
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
  </wsdl:types>

  <wsdl:message name="reverseRequest">
    <wsdl:part name="reverseRequest" element="tns2:reverse"/>
  </wsdl:message>
  <wsdl:message name="reverseResponse">
    <wsdl:part name="return" element="tns2:reverseResponse"/>
  </wsdl:message>
  <wsdl:message name="addRequest">
    <wsdl:part name="addRequest" element="tns2:add"/>
  </wsdl:message>
  <wsdl:message name="addResponse">
    <wsdl:part name="return" element="tns2:addResponse"/>
  </wsdl:message>
  <wsdl:portType name="ComponentPortType">
    <wsdl:operation name="reverse">
      <wsdl:input message="tns2:reverseRequest"/>
      <wsdl:output message="tns2:reverseResponse"/>
    </wsdl:operation>
    <wsdl:operation name="add">
      <wsdl:input message="tns2:addRequest"/>
      <wsdl:output message="tns2:addResponse"/>
    </wsdl:operation>
  </wsdl:portType>
  <wsdl:binding name="ComponentBinding" type="tns2:ComponentPortType">
    <soap:binding transport="http://schemas.xmlsoap.org/soap/http" style="document"/>
    <wsdl:operation name="reverse">
      <soap:operation soapAction=""/>
      <wsdl:input>
        <soap:body use="literal"/>
      </wsdl:input>
      <wsdl:output>
        <soap:body use="literal"/>
      </wsdl:output>
    </wsdl:operation>
    <wsdl:operation name="add">
      <soap:operation soapAction=""/>
      <wsdl:input>
        <soap:body use="literal"/>
      </wsdl:input>
      <wsdl:output>
        <soap:body use="literal"/>
      </wsdl:output>
    </wsdl:operation>
  </wsdl:binding>
  <wsdl:service name="ComponentService">
    <wsdl:port name="ComponentPort" binding="tns2:ComponentBinding">
      <soap:address