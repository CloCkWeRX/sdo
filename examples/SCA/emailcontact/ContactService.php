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
include 'db_config.inc.php';

include 'SCA/SCA.php';

/**
 * Service for managing email contacts
 *
 * @service
 * @binding.jsonrpc
 * @types http://example.org/contacts contacts.xsd
 *
 */
class ContactService {

    /**
     * Retrieve contact details
     *
     * @param string $shortname The short name of the contact
     * @return contact http://example.org/contacts The full contact details
     */
    public function retrieve($shortname) {
        try {
            SCA::$logger->log("Retrieve details for shortname $shortname");
            $dbh = new PDO(PDO_DSN, DATABASE_USER, DATABASE_PASSWORD);
            $stmt = $dbh->prepare('SELECT * FROM contact WHERE shortname = ?');
            $stmt->execute(array($shortname));
            $row = $stmt->fetch();
            $contact = SCA::createDataObject('http://example.org/contacts', 'contact');
            $contact->shortname = $shortname;
            if ($row) {
                SCA::$logger->log("Contact found $shortname = " .
                                  $row['FULLNAME'] .
                                  " " .
                                  $row['EMAIL']);
                //trigger_error("Contact found: " . $shortname);
                $contact->fullname = $row['FULLNAME'];
                $contact->email = $row['EMAIL'];
            }
            $dbh = 0;
            return $contact;
        } catch  (Exception $ex) {
           SCA::$logger->log("Exception on database read = ". $ex);
        }
    }


}

?>