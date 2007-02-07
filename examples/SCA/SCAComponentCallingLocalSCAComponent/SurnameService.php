<?php
/*
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
*/

require 'SCA/SCA.php';

/**
 * A service that adds a surname to a name then calls a HelloService
 * 
 * @service
 */
class SurnameService 
{
    
    /**
     * A reference to a local HelloService
     * 
     * @reference
     * @binding.php ./HelloService.php
     */
     public $helloService;
    
    function sayHello($name) 
    {
        // Add the surname "Beetle"
        $newName = "$name Beetle";
        
        // Call the local HelloService
        return $this->helloService->sayHello($newName);
    }
}
