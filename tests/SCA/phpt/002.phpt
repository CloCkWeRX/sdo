--TEST--
Call a remote component with a simple scalar type (string)
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
<ns1:reverse xmlns="http://Component" xmlns:tns="http://Component" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="reverse">
  <in>IBM</in>
</ns1:reverse>
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
require_once "$component_file";
echo preg_replace("/>\s*</", ">\n<", ob_get_clean());
?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
<SOAP-ENV:Body>
<tns2:reverseResponse xmlns:tns2="http://Component" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<reverseReturn>MBI</reverseReturn>
</tns2:reverseResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>