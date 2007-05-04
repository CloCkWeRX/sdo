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
 * @binding.jsonrpc
 * @types http://www.example.org/email email.xsd  
 * @types http://www.example.org/email emailresponse.xsd 
 * @types http://www.example.org/email emailresponselist.xsd 
 */
class TestService {

    /**
     * @reference
     * @binding.jsonrpc ./EmailService.smd
     * @types http://www.example.org/email email.xsd  
     * @types http://www.example.org/email emailresponse.xsd     
     */
	public $email_service;
	
	/**
     * @reference
     * @binding.jsonrpc ./HelloService.smd
     */
	public $hello_service;

    
    /**
     * A method that provides a complex interface and uses a complex interface
     * 
	 * @param EmailType $email http://www.example.org/email
     * @return EmailResponseListType http://www.example.org/email
     */
    function test(SDO_DataObject $email) {
        
        $json_response = $this->email_service->sendComplexMessage($email);
        
        $email_response_list = SCA::createDataObject("http://www.example.org/email","EmailResponseListType");
             
        $email_response = SCA::createDataObject("http://www.example.org/email","EmailResponseType");
        $email_response->address = "Address 1";
        $email_response->message = "Message 1";     
        $email_response->reply   = "Reply 1";
        $email_response_list->jsonemail[] = $email_response;
        
        $email_response = SCA::createDataObject("http://www.example.org/email","EmailResponseType");
        $email_response->address = "Address 2";
        $email_response->message = "Message 2";     
        $email_response->reply   = "Reply 2";
        $email_response_list->jsonemail[] = $email_response;        
                          
        return $email_response_list;
    }       
}

/*
        $json_response = $this->email_service->sendComplexMessage($email);  
        $email_response = SCA::createDataObject("http://www.example.org/email","EmailResponseType");
        $email_response->address = $json_response->address;
        $email_response->message = $json_response->message;     
        $email_response->reply   = $json_response->reply . " SMD with no @types";
        $email_response_list->jsonemail[] = $email_response;
        
        $json_response = $this->hello_service->sayHello("Fred");  
        $email_response = SCA::createDataObject("http://www.example.org/email","EmailResponseType");
        $email_response->address = "some address";
        $email_response->message = $json_response;     
        $email_response->reply   = " SMD with no types and with no @types";
        $email_response_list->jsonemail[] = $email_response;        
*/

?>