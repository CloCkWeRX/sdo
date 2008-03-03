<?php

include 'db_config.inc.php';

include_once "SCA/SCA.php";

/**
 * A service for managing contact details held in a database.
 * 
 * @service
 *
 * @types http://example.org/contacts contacts.xsd
 */
class Contact {

    /**
     * Create a contact in the database.
     */
    function create($contact) {
        try {
            $dbh = new PDO(PDO_DSN, DATABASE_USER, DATABASE_PASSWORD,
            array(PDO::ERRMODE_EXCEPTION => true));
            $stmt = $dbh->prepare('INSERT INTO contact (title, author, updated, shortname, fullname, email) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute(array($contact->title, $contact->author,
            $contact->updated, $contact->shortname,
            $contact->fullname, $contact->email));
            $id = $dbh->lastInsertId();
            $dbh = 0;
            return $id;
        } catch (PDOException $e) {
            // TODO: logging
            // We should just log the info in the PDOException.  To flow
            // database info back to the client would break encapsulation.
            throw new SCA_InternalServerErrorException();
        }
    }

    /**
     * Retrieve a contact from the database
     */
    function retrieve($id){
        try {
            $dbh = new PDO(PDO_DSN, DATABASE_USER, DATABASE_PASSWORD,
            array(PDO::ERRMODE_EXCEPTION => true));
            $stmt = $dbh->prepare('SELECT * FROM contact WHERE id = ?');
            $stmt->execute(array($id));
            $row = $stmt->fetch();
            $contact = null;
            if ($row) {
                $contact = SCA::createDataObject('http://example.org/contacts', 'contact');
                $contact->id = $row['id'];
                $contact->title = $row['title'];
                $contact->author = $row['author'];
                $contact->updated = $row['updated'];
                $contact->shortname = $row['shortname'];
                $contact->fullname = $row['fullname'];
                $contact->email = $row['email'];
            }
            $dbh = 0;
            return $contact;
        } catch (PDOException $e) {
            // there is a problem so get the SQL error code and report it
            // TODO: logging
            // We should just log the info in the PDOException.  To flow
            // database info back to the client would break encapsulation.
            throw new SCA_NotFoundException($e->getMessage());
        }
    }

    /**
     * Update a contact in the database
     */
    function update($id, $contact){
        try {
            $dbh = new PDO(PDO_DSN, DATABASE_USER, DATABASE_PASSWORD,
            array(PDO::ERRMODE_EXCEPTION => true));
            $stmt = $dbh->prepare('UPDATE contact SET title = ?, author = ?, updated = ?, shortname = ?, fullname = ?, email = ? WHERE id = ?;');
            $success = $stmt->execute(array($contact->title, $contact->author,
            $contact->updated, $contact->shortname,
            $contact->fullname, $contact->email,
            $id));
            if ($success <= 0) {
                $msg = "\nEncountered an error when attempting to execute update statement";
                $pdo_error_info = $stmt->errorInfo();
                $msg .= "\nThe error information returned from PDO::errorInfo() was:";
                $msg .= "\n  SQLSTATE: " . $pdo_error_info[0];
                $msg .= "\n  Driver-specific error code: " . $pdo_error_info[1];
                $msg .= "\n  Driver-specific error message: " . $pdo_error_info[2];

                // TODO: logging
                // We should just log any error info.  To flow
                // database info back to the client would break encapsulation.
                // We should also detect concurrency problems and in that case
                // can throw an SCA_ConflictException.
                throw new SCA_NotFoundException($msg);
            }
            $dbh = 0;
            return true;
        } catch (PDOException $e) {
            // TODO: logging
            // We should just log the info in the PDOException.  To flow
            // database info back to the client would break encapsulation.
            throw new SCA_NotFoundException();
        }

    }

    /**
     * Delete a contact from the database.
     */
    function delete($id){
        try {
            $dbh = new PDO(PDO_DSN, DATABASE_USER, DATABASE_PASSWORD,
            array(PDO::ERRMODE_EXCEPTION => true));
            $stmt = $dbh->prepare('DELETE FROM contact WHERE id = ?');
            $stmt->execute(array($id));
            $dbh = 0;
            return true;
        } catch (PDOException $e) {
            // TODO: logging
            // We should just log the info in the PDOException.  To flow
            // database info back to the client would break encapsulation.
            throw new SCA_NotFoundException();
        }
    }

    /**
     * List all the items in the collection
     */
    function enumerate() {
        try {
            $dbh = new PDO(PDO_DSN, DATABASE_USER, DATABASE_PASSWORD,
            array(PDO::ERRMODE_EXCEPTION => true));
            // TODO: paging
            $stmt = $dbh->prepare('SELECT * FROM contact ORDER BY updated DESC');
            $stmt->execute();
            $contacts = null;
            while ($row = $stmt->fetch()) {
                if ($contacts == null) {
                    $contacts = SCA::createDataObject(
                    'http://example.org/contacts', 'contacts');
                }
                $contact = $contacts->createDataObject('contact');
                $contact->id = $row['id'];
                $contact->title = $row['title'];
                $contact->author = $row['author'];
                $contact->updated = $row['updated'];
                $contact->shortname = $row['shortname'];
                $contact->fullname = $row['fullname'];
                $contact->email = $row['email'];
            }
            $dbh = 0;
            return $contacts;
        } catch (PDOException $e) {
            // TODO: logging
            // We should just log the info in the PDOException.  To flow
            // database info back to the client would break encapsulation.
            throw new SCA_NotFoundException();
        }
    }

}
?>