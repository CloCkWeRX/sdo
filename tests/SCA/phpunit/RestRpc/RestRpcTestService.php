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
$Id: RestRpcTestService.php 234945 2007-05-04 15:05:53Z mfp $
*/
include 'SCA/SCA.php';

/**
 * @service
 * @binding.restrpc
 * @types http://www.example.org/Hello Hello.xsd
 */
class RestRpcTestService
{
	
    /**
      * @param string $name
      * @return HelloType http://www.example.org/Hello
      */    
    public function hello($name) 
    {
       
        $response = SCA::createDataObject('http://www.example.org/Hello', 'HelloType');
        $response->name = $name;
        $response->greeting = "Hello Fred";      
        $response->reversed = "derF olleH";
        
        return $response;
    }
}
?>
