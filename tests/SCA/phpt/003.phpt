--TEST--
Call a remote component with a structured type
--SKIPIF--
<?php 
if (!extension_loaded("sdo")) 
    echo "skip sdo not loaded"; 
?>
--FILE--
<?php

$HTTP_RAW_POST_DATA = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://Component">
<SOAP-ENV:Body>
<ns1:add xmlns="http://Component" xmlns:tns="http://Component" xmlns:tns2="http://www.test.com/info" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="add">
  <tns2:person>
    <tns2:name>
      <first>William</first>
      <last>Shakespeare</last>
    </tns2:name>
    <tns2:address>
      <street>456 Evergreen</street>
      <city>Austin</city>
      <state>TX</state>
    </tns2:address>
  </tns2:person>
  <tns2:phone>
    <type>home</type>
    <number>123-456</number>
  </tns2:phone>
</ns1:add>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOF;

require "SCA/SCA.php";
$component_file = dirname(__FILE__).'/Component.php';

// make it look to the component as if it is on the receiving end of a SOAP request
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['SCRIPT_FILENAME'] = $component_file;
$_SERVER['CONTENT_TYPE'] = 'application/soap+xml';

ob_start();
include "$component_file";
echo preg_replace("/>\s*</", ">\n<", ob_get_clean());

?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
<SOAP-ENV:Body>
<tns2:addResponse xmlns:tns2="http://Component" xmlns:tns3="http://www.test.com/info" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<addReturn>
<name>
<first>William</first>
<last>Shakespeare</last>
</name>
<phone>
<type>home</type>
<number>123-456</number>
</phone>
<address>
<street>456 Evergreen</street>
<city>Austin</city>
<state>TX</state>
</address>
</addReturn>
</tns2:addResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>