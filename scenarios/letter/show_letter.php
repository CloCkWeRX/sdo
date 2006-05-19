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
| Author: Graham Charters                                              |
+----------------------------------------------------------------------+
$Id$
-->
<body>

<?php

$xmldas = SDO_DAS_XML::create('./letter.xsd');

$doc = $xmldas->loadFile('letter.xml');

$letter_seq = $doc->getRootDataObject()->getSequence();

for ($i = 0; $i < count($letter_seq); $i++) {
	$out = str_replace(array("\n"), array("<br/>"), $letter_seq[$i]);
	if ($letter_seq->getProperty($i) == NULL)
	    echo $out;
	else
	    echo "<b>$out</b>";
}

?>


</body>
</html>