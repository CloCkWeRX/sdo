<html>
<!--
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                  |
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
-->
<head>
    <title>The SDO AJAX Sample</title>
</head>
<body BGCOLOR="#EFEFEF">

<?php
define('AJAX_SERVER', 
       'http://' . 
       $_SERVER['SERVER_NAME'] .
       ':' .
       $_SERVER['SERVER_PORT'] .
       '/ajax-rss/sdoajax.php');
?>

<script type="text/javascript">
var request;
var object;
  
/**
 * Called when the response from XMLHttpRequest.send
 * is ready for processing
 */
function handleResponse ()
{
    if ( request.readyState == 4 ) {
        if ( request.status == 200 ) {
            var responseText = request.responseText;
            document.getElementById('jsonString').innerHTML=responseText;
        } else {
            alert("The request didn't work " + request.status);
        }
    }
}
   
/**
 * Called when the "Get Item" button is pressed. 
 * Makes a call to the server to retrieve the next
 * item from the RSS feed
 */
function getItem ()
{ 
    var inputfield = document.getElementById("feedurl").value;  
    var objectString = document.getElementById("jsonString").innerHTML;

    request = new XMLHttpRequest ();
    request.onreadystatechange = handleResponse;
    request.open("POST", "<?php echo AJAX_SERVER ?>", true);
    request.setRequestHeader("Content-Type",
                             "application/x-www-form-urlencoded; charset=UTF-8");
    request.send("feedurl=" + inputfield + "&object=" + objectString);
} 
</script>

URL of RSS feed: 
<input type="text" id="feedurl" size="80" 
       value="http://www.mail-archive.com/tuscany-dev%40ws.apache.org/maillist.xml"/>
<p/> 
<input type="button" value="Get Item" onclick="getItem()"/>
<p/>
RSS Item: 
<div id="jsonString">NoItem</div>
</body>
</html>