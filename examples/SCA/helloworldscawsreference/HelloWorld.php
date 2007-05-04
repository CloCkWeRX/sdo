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
include 'SCA/SCA.php';

/**
 * @service
 * @binding.restrpc
 */
class HelloWorld
{
    /**
     * @reference
     * @binding.soap ./wsdl/Greeting.wsdl
     */
	public $greeting_service;
	
	/**
     * @reference
     * @binding.soap ./wsdl/Reversing.wsdl   
     */
	public $reversing_service;
	
    /**
      * @param string $name
      * @return string
      */    
    public function hello($name) 
    {
        $greeting          = $this->greeting_service->greet($name);
        $reversed_greeting = $this->reversing_service->reverse($greeting);
         
        $response = "Name: " . $name . "<br/>";
        $response = $response . "Greeting: " . $greeting . "<br/>";
        $response = $response . "Reversed: " . $reversed_greeting. "<br/>"; 
        
        return $response;
    }
}
?>
