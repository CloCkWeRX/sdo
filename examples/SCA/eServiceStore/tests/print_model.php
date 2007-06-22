<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006, 2007                                    |
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
| Authors: Graham Charters, Matthew Peters                                    |
|                                                                             |
+-----------------------------------------------------------------------------+
$Id$
*/

function print_model($do)
{
	echo "BEGIN \n";
	
	echo 'DO has ' . count($do) . " properties. \n";
	for ($i=0; $i<count($do); $i++)
	{
		if (is_object($do[$i]))
		{	
			echo "It's an Object \n";
			print_model($do[$i]);
		}
		else
		{
			echo "It's not an object \n";
			echo "$do[$i] \n";
			echo "Containment Name: $do[$i]->getContainmentPropertyName() \n";	
		}
	}
	echo "END \n";
}


?>