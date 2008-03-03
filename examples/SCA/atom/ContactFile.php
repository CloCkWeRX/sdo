<?php

include 'db_config.inc.php';

include_once "SCA/SCA.php";

/**
 * A service for managing contact details held as files.
 * 
 * @service
 * 
 *
 * @types http://example.org/contacts contacts.xsd
 */
class ContactFile {

    function enumerate() {

        //build a contacts SDO out of the contents of a number of the files.
        SCA::$logger->log("Entering");

        //Create an array of SDOs
        $contactsArray = array();


        try{

            //NOTE: need directory called Contact with entries 2.xml and 3.xml in it
            $first = 1;
            $last = 1;
            while ( file_exists("Contact/$last.xml") ){ $last++; }
            $last--;

            SCA::$logger->log("Feed will contain entries $first to $last");

            $xmldas = SDO_DAS_XML::create('contacts.xsd');

            for($i=$first; $i<=$last ; $i++){

                if (file_exists("Contact/$i.xml")){
                    $doc = $xmldas->loadFile("Contact/$i.xml");
                    SCA::$logger->log("read in " . $xmldas->saveString($doc));
                    $root_data_object = $doc->getRootDataObject();

                    SCA::$logger->log("about to return the sdo");

                    //NOTE: return $resource;    //this returns the xml (escaped...)
                    array_push($contactsArray, $root_data_object);

                } else {

                    throw new SCA_NotFoundException();

                }
            }

            SCA::$logger->log("Built an array of SDOs: ".print_r($contactsArray,true));


            $contactsSDO = $xmldas->createDataObject('http://example.org/contacts','contacts');

            foreach($contactsArray as $contact){
                $contactsSDO->contact[] = $contact;
            }

            SCA::$logger->log("Built a contactsSDO: ".print_r($contactsSDO,true));

            return $contactsSDO;


        }
        catch(Exception $e){
            SCA::$logger->log("caught exception: ".$e->getMessage());
            throw new SCA_NotFoundException();

        }

    }
    /**
     * Create a file representing a contact from an SDO passed in. 
     */
    function create($contact) {

        SCA::$logger->log("Entering");

        //should work if receives no parameter so make $sdo optional for the moment.
        if($contact !== null){

            //TODO: Check whether what we have received IS an SDO

            $xmldas = SDO_DAS_XML::Create('contacts.xsd');

            //NOTE: Does not mind if a non-existent type is specified for the second parameter.
            $doc = $xmldas->createDocument('http://example.org/contacts','contact', $contact);


            $id = 1;

            while ( file_exists("Contact/$id.xml") ){ $id++; }

            $contact->id = $id;
            $xmldas->saveFile($doc, "Contact/$id.xml", 2);

        }
        else {

            //NOTE: Idea of this is to be able to handle situations where a create method takes no parameter, is just expected to create an instance of a resource, perhaps with default settings.

            SCA::$logger->log("No setting for the resource were provided: creating an instance of the resource using default settings.");

            $id = 1;

            while ( file_exists("Contact/$id.xml") ){
                $id++;
            }

            fopen("Contact/$id.xml", 'x');
        }

        if(file_exists("Contact/$id.xml")){
            SCA::$logger->log("Created a file called ".$id.".xml");
            return $id;
        }
        else{
            SCA::$logger->log("Failed to create a file called ".$id.".xml");
            throw new SCA_InternalServerErrorException();
        }
    }

    /**
     * Retrieve a contact 
     */
    function retrieve($id){

        SCA::$logger->log("Entering");

        try{
            $xmldas = SDO_DAS_XML::Create('contacts.xsd');
            if (file_exists("Contact/$id.xml")){
                $doc = $xmldas->loadFile("Contact/$id.xml");
                //$resource = $xmldas->saveString($doc, 2);
                $root_data_object = $doc->getRootDataObject();

                SCA::$logger->log("about to return the sdo");

                //return $resource;    //this returns the xml (escaped...)
                return $root_data_object;

            } else {
                return null;

            }
        }
        catch(Exception $e){
            SCA::$logger->log("caught exception: ".$e->getMessage());
            throw new SCA_NotFoundException();
        }




    }

    /**
         * Update a contact in the database
         */
    //    function update($id, $contact){
    //        SCA::$logger->log("Got into update()");
    //        //NOTE: the next part of the code is for some reason resulting in a 200 going back to the client before any of the log statements are reached.
    //        try {
    //            $dbh = new PDO(PDO_DSN, DATABASE_USER, DATABASE_PASSWORD,
    //            array(PDO::ERRMODE_EXCEPTION => true));
    //            $stmt = $dbh->prepare('UPDATE contact SET title = ?, author = ?, updated = ?, shortname = ?, fullname = ?, email = ? WHERE id = ?;');
    //            $success = $stmt->execute(array($contact->title, $contact->author,
    //            $contact->updated, $contact->shortname,
    //            $contact->fullname, $contact->email,
    //            $id));
    //            if ($success <= 0) {
    //                // TODO: logging
    //                // We should just log any error info.  To flow
    //                // database info back to the client would break encapsulation.
    //                // We should also detect concurrency problems and in that case
    //                // can throw an SCA_ConflictException.
    //                throw new SCA_NotFoundException();
    //            }
    //            $dbh = 0;
    //            SCA::$logger->log("Successfully got through the code in update()");
    //            return true;
    //        } catch (PDOException $e) {
    //            // TODO: logging
    //            // We should just log the info in the PDOException.  To flow
    //            // database info back to the client would break encapsulation.
    //            SCA::$logger->log("Problem in ContactFile with update()");
    //            throw new SCA_NotFoundException();
    //        }
    //
    //    }

    function update($id, $sdo){
        SCA::$logger->log("Entering with params ID: ".$id." SDO: ".$sdo);

        //TODO: need to make sure these are properly checked and errors are passed around as appropriate.
        try{
            $xmldas = SDO_DAS_XML::Create('Atom1.0.xsd');
            //Does not mind if a non-existent type is specified for the second parameter.
            $doc = $xmldas->createDocument('http://www.w3.org/2005/Atom','entryType', $sdo);
            $xmldas->saveFile($doc, "Contact/$id.xml");
            return true;
        }
        catch(Exception $e){
            SCA::$logger->log("caught exception: ".$e->getMessage());
            throw new SCA_NotFoundException();
        }

    }

    /**
     * Delete a contact from the database.
     */
    //    function delete($id){
    //        //triggers returning a 200
    //        try {
    //            $dbh = new PDO(PDO_DSN, DATABASE_USER, DATABASE_PASSWORD,
    //            array(PDO::ERRMODE_EXCEPTION => true));
    //            $stmt = $dbh->prepare('DELETE FROM contact WHERE id = ?');
    //            $stmt->execute(array($id));
    //            $dbh = 0;
    //            return true;
    //        } catch (PDOException $e) {
    //            // TODO: logging
    //            // We should just log the info in the PDOException.  To flow
    //            // database info back to the client would break encapsulation.
    //            throw new SCA_NotFoundException();
    //        }
    //    }


    function delete($id){
        //TODO: find out what an adequate check is. This is failing with a warning if given a file that doesnt exist, but returns true. Should deal with sucess, problems deleting existing file, files that are not there. Some stuff to investigate...

        SCA::$logger->log("dealing with file: "."Contact/$id.xml");

        if (file_exists("Contact/$id.xml")){

            unlink("Contact/$id.xml");

        }
        else{
            throw new SCA_NotFoundException();
        }
        if (file_exists("Contact/$id.xml")){
            throw new SCA_InternalServerErrorException();
        }
        return true;
    }


}
?>
