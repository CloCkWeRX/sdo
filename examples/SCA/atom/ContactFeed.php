<?php

include_once "SCA/SCA.php";

/**
 * @service
 * @binding.atom
 * 
 * @types http://www.w3.org/2005/Atom Atom1.0.xsd
 * @types http://example.org/contacts contacts.xsd
 */
class ContactFeed {
    
    /**
     * The contact database service
     *
     * @reference
     * @binding.php ContactFile.php
     */
    public $contact_service;

    /**
     * Converts an Atom Entry SDO to a Contact SDO.
     */
    private function entryToContact($entry) {      
        $contact = SCA::createDataObject('http://example.org/contacts', 'contact');
        
        // Set the title (this is assumed to be the first piece of unstructured text)
        // We are considering ways to make this simpler in SDO.
        // Something like $seq->values[0], maybe.
        // Also, note how title and author, etc are many-valued.  This is due
        // to limitations in xml schema's ability to model the Atom XML.
        // We are working on ways to improve the SDO APIs in these 
        // circumstances.  One way would be a shortcut to the first entry, so
        // $entry->title[0]->getSequence() and $entry->title->getSequence()
        // would be equivalent.
        $seq = $entry->title[0]->getSequence();
        for ($i=0; $i<count($seq); $i++) {
            if ($seq->getProperty($i) == null) {
                $contact->title = $seq[$i];
            }
        }

        // The last part of the entry id (in uri form) is the contact id
        $segments = explode('/', $entry->id[0]->value);
        $contact->id = $segments[count($segments)-1];
        
        // Set the author and updated properties
        $contact->author = $entry->author[0]->name[0];
        $contact->updated = $entry->updated[0]->value;

        /*****************************************************/
        /* Should be able to use XPath but encountered a bug */
        /* which mean xpath only searches down 1 level on    */
        /* open types  (e.g. $div['ul/li[title="email"]'];)   */
        /*****************************************************/
        // Get the div and then loop through the <li/>s inside the <ul/>.  The 
        // contents of each is the first peice of unstructured text.  This code 
        // assumes the span title attribute matches the contact property name.
        $div = $entry->content[0]->div;
        
        /**************** The xhtml should be of this form *************************/
        /* <ul class="xoxo contact" title="contact" >                              */
        /*   <li class="shortname" title="shortname">shifty</li>                   */
        /*   <li class="fullname" title="fullname">Rt. Hon. Elias Shifty Esq.</li> */
        /*   <li class="email" title="email">shifty@uk.ibm.com</li>                */
        /* </ul>                                                                   */
        /***************************************************************************/    
        
        $ul = $div[0]->ul;
        $lis = $ul[0]['li'];
        foreach ($lis as $li) {
            $seq = $li->getSequence();
            for ($i=0; $i<count($seq); $i++) {
                if ($seq->getProperty($i) == null) {
                    $contact->{$li->class} = $seq[$i];
                }
            }
        }

        return $contact;    
    }
    
    /**
     * Converts an Contact SDO to an Atom Entry (raw XML)
     */
    private function contactToEntry($contact) {
        
        /*************************************/
        /* Pupulate an entry using a heredoc */
        /*************************************/
        
        $link = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '/' . $contact->id;

        $entryXML = <<< ENTRY
<?xml version="1.0" encoding="UTF-8"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:tns="http://www.w3.org/2005/Atom">
  <id>$link</id>
  <title>$contact->title</title>
  <updated>$contact->updated</updated>
  <author>
    <name>$contact->author</name>
  </author>
  <link rel="edit">$link</link>
  <content type="xhtml">
    <div>
      <ul class="xoxo contact" title="contact" >
        <li class="shortname" title="shortname">$contact->shortname</li>
        <li class="fullname" title="fullname">$contact->fullname</li>
        <li class="email" title="email">$contact->email</li>
      </ul>
    </div>
  </content>
</entry>
ENTRY;
        
        return $entryXML;
    }

    private function contactsToFeed($contacts) {
        $collectionURI = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        // Assumes the contacts have been sorted with most recent first
        if (count($contacts->contact) > 0)
            $last_updated = $contacts->contact[0]->updated;
        
        $feedXML = <<< FEEDSTART
<?xml version="1.0" encoding="utf-8"?>
  <feed xmlns="http://www.w3.org/2005/Atom" xmlns:tns="http://www.w3.org/2005/Atom">
    <id>$collectionURI</id>
    <link href="$collectionURI" rel="self"/>
    <title type="text">Contact Details</title>
    <updated>$last_updated</updated>
FEEDSTART;

        foreach ($contacts->contact as $contact) {
            $link = $collectionURI . '/' . $contact->id;
            $feedXML .= <<< ENTRY

    <entry>
      <id>$link</id>
      <title>$contact->title</title>
      <updated>$contact->updated</updated>
      <author>
        <name>$contact->author</name>
      </author>
      <link rel="edit">$link</link>
      <link href="$link"/>
      <content type="xhtml">
        <div>
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">$contact->shortname</li>
            <li class="fullname" title="fullname">$contact->fullname</li>
            <li class="email" title="email">$contact->email</li>
          </ul>
        </div>
      </content>
    </entry>
ENTRY;
        }

        $feedXML .= <<< FEEDEND

  </feed>
FEEDEND;

        return $feedXML;
    }
    
    
    /**
     * We shouldn't need any additional annotations (it should always be an entry)
     */
    function create($entry) {
        $contact = $this->entryToContact($entry);
        
        /*********************************************/
        /* Resource creation code - delegates to the */
        /* contact data service                      */
        /*********************************************/
        $id = $this->contact_service->create($contact);
        // Pass-by-value means we must add in the new id to the contact, the
        // service can't do this for us.
        $contact->id = $id;
        
        // Return the entry.
        // Should result in the follow:
        //  - a 201 Created.
        //  - a Location of $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '/' . $entry->id[0]->value;
        //  - the entry in the body
        return $this->contactToEntry($contact);

    }

    /**
     * We shouldn't need any additional annotations (it should always be an entry)
     */
    function retrieve($id){
        /**********************************************/
        /* Resource retrieval code - delegates to the */
        /* contact data service                       */
        /**********************************************/
        $contact = $this->contact_service->retrieve($id);
        if ($contact == null) throw new SCA_NotFoundException();

        // Should result in
        //   - 200 OK
        //   - the entry in the body
        return $this->contactToEntry($contact);
    }

    /**
     * We shouldn't need any additional annotations (it should always be an entry)
     */
    function update($id, $entry){
        $contact = $this->entryToContact($entry);
        
        /*******************************************/
        /* Resource update code - delegates to the */
        /* contact data service.                   */
        /*******************************************/
        $this->contact_service->update($id, $contact);
        
        // Return success (should this be true or just return (can we use false to mean anything?)?
        // Should result in the follow:
        //  - a 200 OK.
        return true;
    }

    /**
     * We shouldn't need any additional annotations (it should always be an entry)
     */ 
    function delete($id){
        /*********************************************/
        /* Resource deletion code - delegates to the */
        /* contact data service.                     */
        /*********************************************/
        $this->contact_service->delete($id);
        
        // Return success (should this be true or just return (can we use false to mean anything?)?
        // Should result in the follow:
        //  - a 200 OK.
        return true;
    }

    /**
     * We shouldn't need any additional annotations (it should always be an entry)
     */
    function enumerate(){
        /**********************************************/
        /* Resource retrieval code - delegates to the */
        /* contact data service                       */
        /**********************************************/
        $contacts = $this->contact_service->enumerate();
        if ($contacts == null) throw new SCA_NotFoundException();

        // Should result in
        //   - 200 OK
        //   - the entry in the body
        return $this->contactsToFeed($contacts);
    }

}
?>
