<!--
+-----------------------------------------------------------------------------+
| Copyright IBM Corporation 2007.                                             |
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
| Authors: Graham Charters, Megan Beynon                                      |
|                                                                             |
+-----------------------------------------------------------------------------+
$Id$
-->

<html>
<title>Calling a remote SCA component using a PHP script.</title>
<body>

<h3>Calling a remote SCA component using a PHP script.</h3>

<?php
require_once 'SCA/SCA.php';

echo "<p>Attempting to access MailApplicationService, to trigger the automatic generation of SMD for this component...</p>\n";
$f = file_get_contents('http://localhost/examples/SCA/JsonRpc/MailApplicationService.php?smd');

// write contents locally to get round the SCA cache
file_put_contents("MailApplicationService.smd", $f);

echo "<p>Attempting to access EmailService, to trigger the automatic generation of SMD for this component...</p>\n";
$f = file_get_contents('http://localhost/examples/SCA/JsonRpc/EmailService.php?smd');

// write contents locally to get round the SCA cache
file_put_contents("EmailService.smd", $f);

echo "<p>Attempting to access WebService, to trigger the automatic generation of WSDL for this component...</p>\n";
$f = file_get_contents('http://localhost/examples/SCA/JsonRpc/WebService.php?wsdl');

// write contents locally to get round the SCA cache
file_put_contents("WebService.wsdl", $f);

echo "<p>Calling MailApplicationService as a json rpc service...</p>\n";
$service = SCA::getService('./MailApplicationService.smd');

echo "<p>Response to sendMessage: " . $service->sendMessage("abc", "xyz") . "</p>\n";
echo "<p>Response to sendComplexMessage: " . $service->sendComplexMessage("abc", "xyz") . "</p>\n";

// need to do a bit of work on this as the infrastructure is currently
// unable to return an SDO based on an SMD. So we have to do some unatural stuff

// make the JSON proxy create a JSON DAS
$reference = new SCA_ReferenceType();
$service->addReferenceType($reference);

// Manually create and SDO
$xmldas = SDO_DAS_XML::create();
$xmldas->addTypes("email.xsd");
$email = $xmldas->createDataObject("http://www.example.org/email",
                                   "EmailType");
$email->address = "fred@somewhere.org";
$email->message = "some message";

echo "<p>Response to sendComplexMessagePassthrough: ";
print_r($service->sendComplexMessagePassthrough($email));
echo "</p>\n";

echo "<p>Response to sendComplexMessageResponseList: ";
print_r($service->sendComplexMessageResponseList($email));
echo "</p>\n";

?>

</body>
</html>