--TEST--
Call a remote component
--FILE--
<?php

require "SCA/SCA.php";
$component_file = str_replace('004.php', 'Component.php', __FILE__);
$wsdl = SCA::generateWSDL($component_file);
echo substr($wsdl,0,strpos($wsdl,'location'));

?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:tns2="http://Component" xmlns:tns="http://schemas.xmlsoap.org/wsdl/" xmlns:tns3="http://schemas.xmlsoap.org/wsdl/soap/" targetNamespace="http://Component">
  <types>
    <xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" 
      xmlns:ns0="http://www.test.com/info"
      targetNamespace="http://Component">
      <xs:import schemaLocation="person.xsd" namespace="http://www.test.com/info"/>
      <xs:element name="reverse">
        <xs:complexType>
          <xs:sequence>
            <xs:element name="in" type="xs:string" nillable="true"/>
          </xs:sequence>
        </xs:complexType>
      </xs:element>
      <xs:element name="reverseResponse">
        <xs:complexType>
          <xs:sequence>
            <xs:element name="reverseReturn" type="xs:string" nillable="true"/>
          </xs:sequence>
        </xs:complexType>
      </xs:element>
      <xs:element name="add">
        <xs:complexType>
          <xs:sequence>
            <xs:element name="person" type="ns0:person" nillable="true"/>
            <xs:element name="phone" type="ns0:phone" nillable="true"/>
          </xs:sequence>
        </xs:complexType>
      </xs:element>
      <xs:element name="addResponse">
        <xs:complexType>
          <xs:sequence>
            <xs:element name="addReturn" type="ns0:person" nillable="true"/>
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
    <operation name="reverse">
      <input>
        <tns3:body xsi:type="tBody" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" use="literal"/>
      </input>
      <output>
        <tns3:body xsi:type="tBody" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" use="literal"/>
      </output>
      <tns3:operation xsi:type="tOperation" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" soapAction=""/>
    </operation>
    <operation name="add">
      <input>
        <tns3:body xsi:type="tBody" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" use="literal"/>
      </input>
      <output>
        <tns3:body xsi:type="tBody" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" use="literal"/>
      </output>
      <tns3:operation xsi:type="tOperation" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" soapAction=""/>
    </operation>
    <tns3:binding xsi:type="tBinding" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" transport="http://schemas.xmlsoap.org/soap/http" style="document"/>
  </binding>
  <service name="ComponentService">
    <port name="ComponentPort" binding="tns2:ComponentBinding">
      <tns3:address xsi:type="tAddress" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"