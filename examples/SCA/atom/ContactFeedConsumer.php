<?php

include_once "SCA/SCA.php";

/**
 * A component which consumes a contact atom feed service.  This is currently
 * required because of the lack of support for SCA::getService() for atom
 * services.
 * 
 * @service
 * 
 */
class ContactFeedConsumer {
    
    // Un-comment this block if you want $contact_feed to be a reference to 
    // the local ContactFeed.php 
//
//    /**
//     * A reference to the contact feed service.  The Atom xsd is currently
//     * required to be specified, but my abe optional in the future.
//     *
//     * @reference
//     * @binding.php ./ContactFeed.php
//     * @types http://www.w3.org/2005/Atom Atom1.0.xsd
//     */

    /**
     * A reference to the contact feed service.  The Atom xsd is currently
     * required to be specified, but my abe optional in the future.
     *
     * @reference
     * @binding.atom http://localhost/examples/SCA/Atom/ContactFeed.php
     * @types http://www.w3.org/2005/Atom Atom1.0.xsd
     */
    public $contact_feed;

    /**
     * We shouldn't need any additional annotations (it should always be an entry)
     */
    function create($entry) {       
        return $this->contact_feed->create($entry);
    }

    /**
     * We shouldn't need any additional annotations (it should always be an entry)
     */
    function retrieve($id){
        return $this->contact_feed->retrieve($id);
    }

    /**
     * We shouldn't need any additional annotations (it should always be an entry)
     */
    function update($id, $entry){
        return $this->contact_feed->update($id, $entry);
    }

    /**
     * We shouldn't need any additional annotations (it should always be an entry)
     */ 
    function delete($id){
        return $this->contact_feed->delete($id);
    }

    /**
     * We shouldn't need any additional annotations (it should always be an entry)
     */
    function enumerate(){
        return $this->contact_feed->enumerate();
    }

}
?>
