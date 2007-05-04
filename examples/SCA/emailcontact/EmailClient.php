<html>
<body>
<?php

/*
+----------------------------------------------------------------------+
| Copyright IBM Corporation 2007.                                      |
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+
|                                                                      |
| Licensed under the Apache License, Version 2.0 (the "License"); you  |
| may not use this file except in compliance with the License. You may |
| obtain a copy of the License at                                      |
| http://www.apache.org/licenses/LICENSE-2.0                           |
|                                                                      |
| Unless required by applicable law or agreed to in writing, software  |
| distributed under the License is distributed on an "AS IS" BASIS,    |
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
| implied. See the License for the specific language governing         |
| permissions and limitations under the License.                       |
+----------------------------------------------------------------------+
| Author: SL                                                           |
+----------------------------------------------------------------------+
$Id$
*/

// TODO: VALIDATE INPUT - should do regex check for email addresses!!!

if (empty($_POST['to'])) {
    echo "<h3>No email address passed to client.</h3></br>";
    return;
}
if (empty($_POST['from'])) {
    echo "<h3>No from email address passed to client.</h3></br>";
    return;
}
if (empty($_POST['subject'])) {
    echo "<h3>No subject passed to client.</h3></br>";
    return;
}
if (empty($_POST['message'])) {
    echo "<h3>No message passed to client.</h3></br>";
    return;
}
$to      = $_POST['to'];
$from    = $_POST['from'];
$subject = $_POST['subject'];
$message = $_POST['message'];

include 'SCA/SCA.php';

$email_service = SCA::getService('ContactEmailService.php');
$success       = $email_service->send($to, $from, $subject, $message); 

if (!$success) {
    echo '<h3>Failed to send email to: ' . $_POST['to'] . '</h3></br>';
}
else {
    echo '<h3>Email sent to: ' . $_POST['to'] . '</h3></br>';
}

?>
</body>
</html>

