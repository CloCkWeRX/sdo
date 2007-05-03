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
 * @binding.soap
 * @types http://www.example.org/email email.xsd  
 * @types http://www.example.org/email emailresponse.xsd
 */
class WebService {

    
    /**
     * A method that sends an email message
     * 
	 * @param EmailType $email http://www.example.org/email
     * @return EmailResponseType http://www.example.org/email
     */
    function sendComplexMessage(SDO_DataObject $email) {
        //echo "Sending message $message to address $address";
        
        $emailresponse = SCA::createDataObject("http://www.example.org/email","EmailResponseType");
        $emailresponse->address = $email->address;
        $emailresponse->message = $email->message;
        $emailresponse->reply   = "web service email reply";
        return $emailresponse;
    }    
}

?>