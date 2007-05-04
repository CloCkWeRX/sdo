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
 * Service for sending emails
 * 
 * @service
 *
 */
class ContactEmailService {

    /**
     * @reference
     * @binding.jsonrpc ./ContactService.smd
     */
    public $contact_service;

    /**
     * @reference
     * @binding.soap ./EmailService.wsdl
     */
    public $email_service;


    /**
     * Send a simple text email with the options of using short names for addresses.
     *
     * @param string $to The "to" email address or shortname
     * @param string $from The "from" email address or shortname
     * @param string $subject The subject of the email
     * @param string $message The email message
     * @return boolean
     */
    public function send($to, $from, $subject, $message) {

        $contact = $this->contact_service->retrieve($to);
        if ($contact && isset($contact->fullname) && isset($contact->email)) {
            $to = $contact->email;
        }

        $contact = $this->contact_service->retrieve($from);
        if ($contact && isset($contact->fullname) && isset($contact->email)) {
            $from = $contact->email;
        }

        return $this->email_service->send($to, $from, $subject, $message);
    }


}

?>