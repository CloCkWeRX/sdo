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
<title>SCA Component calling remote SCA Component using xmlrpc binding</title>
<body>
<h3>SCA Component calling remote SCA Component using xmlrpc</h3>

<?php
require_once 'SCA/SCA.php';

echo '<p>Requesting service description from HelloService Component</p>';

// Cause the wsdl to be generated for the target remote SCA Component
$f = file_get_contents('http://localhost/examples/SCA/XmlRpc/HelloService.php/system.describeMethods');
file_put_contents('HelloService.describe',$f);

echo '<p>Calling SurnameService locally, which should call the HelloService as a Web service.</p>';

// Get a proxy to the local SurnameService
$service = SCA::getService('./SurnameService.php');

// Call the SurnameService and write out the response
echo '<p>Response: ' . $service->sayHello('Freddie') . '</p>';

?>
</body>
</html>
