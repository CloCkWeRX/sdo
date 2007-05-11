<?php

include 'SCA/SCA.php';


/**
 * Consumes eBay
 * @service
 *
 */
class eBayConsumer {

    /**
     * eBay service reference
     * 
     * @reference
     * @binding.ebaysoap eBaySvc.wsdl
     *
     * @config ./config/ebay.ini
     */
    public $ebay;

    public function GetSearchResults($query) {
        // Create the body
        $request = $this->ebay->createDataObject('urn:ebay:apis:eBLBaseComponents', 
                                                 'GetSearchResultsRequestType');
        $request->Version = 495;
        $request->Query = $query;
        $request->createDataObject('Pagination');
        $request->Pagination->EntriesPerPage = 10;
        
        return $this->ebay->GetSearchResults($request);
    }
}

?>