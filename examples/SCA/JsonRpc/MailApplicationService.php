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
class MailApplicationService {

    // @binding.jsonrpc ./EmailService.smd
    // @binding.jsonrpc http://localhost/json-rpc/EmailService.php?smd

    /**
     * @reference
     * @binding.jsonrpc EmailService.smd
     * @types http://www.example.org/email email.xsd
     * @types http://www.example.org/email emailresponse.xsd
     */
	public $email_service;

	/**
     * @reference
     * @binding.jsonrpc ./EmailService.smd
     */
	public $another_email_service;

	/**
     * @reference
     * @binding.jsonrpc http://localhost/examples/SCA/JsonRpc/HelloService.php?smd
     */
	public $hello_service;


    /**
     * @reference
     * @binding.soap ./WebService.wsdl
     */
	public $web_service;

	/**
     * @reference
     * @binding.php LocalService.php
     */
	public $local_service;

    /**
     * A method that sends an email message
     *
     * @param string $address (The email address to send the message to)
     * @param string $message The message to send
     * @return boolean The status of the send operation
     */
    function sendMessage($address, $message) {

        $result = $this->email_service->sendMessage($address, $message);

        $response = "message not sent";

        if ($result == true ) {
            $response = $result;
        }

        return $response;
    }

    /**
     * A method that sends an email message
     *
     * @param string $address The email address to send the message to
     * @param string $message The message to send
     * @return boolean The status of the send operation
     */
    function sendComplexMessage($address, $message) {

        $email = $this->email_service->createDataObject("http://www.example.org/email","EmailType");

        $email->address = $address;
        $email->message = $message;

        $result = $this->email_service->sendComplexMessage($email);

        $response = "message not sent";

        if ($result == true ) {
            $response = $result->reply;
        }

        return $response;

    }

    /**
     * A method that sends an email message
     *
	 * @param EmailType $email http://www.example.org/email
     * @return EmailResponseType http://www.example.org/email
     */
    function sendComplexMessagePassthrough(SDO_DataObject $email) {
        return $this->email_service->sendComplexMessage($email);
    }

    /**
     * A method that sends an email message
     *
	 * @param EmailType $email http://www.example.org/email
     * @return EmailResponseListType http://www.example.org/email
     */
    function sendComplexMessageResponseList(SDO_DataObject $email) {

        $email_response_list = SCA::createDataObject("http://www.example.org/email","EmailResponseListType");


        $json_response = $this->email_service->sendComplexMessage($email);
        $email_response_list->jsonemail[] = clone $json_response;

        // the response types don't match here because we have not
        // specified @types in the another_maile_service reference
        $json_response = $this->another_email_service->sendComplexMessage($email);
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

        $webservice_response = $this->web_service->sendComplexMessage($email);
        $email_response_list->wsemail = clone $webservice_response;

        $local_response = $this->local_service->sendComplexMessage($email);
        $email_response_list->localemail = $local_response;


        return $email_response_list;
    }
}

?>