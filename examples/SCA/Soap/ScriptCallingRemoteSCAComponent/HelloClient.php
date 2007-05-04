<!--
+-----------------------------------------------------------------------------+
| Copyright IBM Corporation 2006.                                             |
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
require 'SCA/SCA.php';

echo '<p>Attempting to access HelloService, to trigger the automatic generation of WSDL for this component...</p>';
$f = file_get_contents('http://localhost/examples/SCA/Soap/ScriptCallingRemoteSCAComponent/HelloService.php?wsdl');
file_put_contents('HelloService.wsdl',$f);

echo '<p>Calling HelloService as a Web service...</p>';
$service = SCA::getService('./HelloService.wsdl');
echo '<p>Response: ' . $service->sayHello('Freddie') . '</p>';

?>

</body>
</html>