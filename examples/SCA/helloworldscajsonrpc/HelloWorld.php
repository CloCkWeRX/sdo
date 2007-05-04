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
$Id: HelloWorld.php,v 1.1.2.1 2007/04/27 11:17:46 slaws Exp $
*/
include 'SCA/SCA.php';

/**
 * @service
 * @binding.restrpc
 * @binding.jsonrpc
 * @types http://www.example.org/Hello Hello.xsd
 */
class HelloWorld
{

    /**
     * @reference
     * @binding.restrpc http://localhost/examples/SCA/helloworldscajsonrpc/Greeting.php      
     * @types http://www.example.org/Greeting ./Greeting.xsd
     */
	public $greeting_service;
	
	/**
     * @reference
     * @binding.soap ./wsdl/Reversing.wsdl   
     */
	public $reversing_service;
	
    /**
      * @param string $name
      * @return HelloType http://www.example.org/Hello
      */    
    public function hello($name) 
    {
        $greeting          = $this->greeting_service->greet($name);
        $reversed_greeting = $this->reversing_service->reverse($greeting->greeting);
        
        $response = SCA::createDataObject('http://www.example.org/Hello', 'HelloType');
        $response->name = $name;
        $response->greeting = $greeting->greeting;      
        $response->reversed = $reversed_greeting;
        
        return $response;
    }
}
?>
