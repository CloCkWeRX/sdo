<?php
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                  |
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
require_once "SDO/DAS/json.php";

try {
    // create a json das, we'll need one later
    $jsonDas = new SDO_DAS_Json();
  	     	
    // get the rss feed as an SDO object
    $feedurl = $_POST['feedurl'];
    $xmldas  = SDO_DAS_XML::create('./rss.xsd');
    $xmldoc  = $xmldas->loadFile($feedurl);
    $rss     = $xmldoc->getRootDataObject();
    
    // get the object representing the contents of the <div/> element
    // in the browser
    $objectString = $_POST['object']; 	
    $item         = null;
  	
    // do something special if this is the first time through here
    if ( $objectString == "NoItem" ) {
        // this is the first request so read the first item
        try {
            $item = $rss["channel/item[1]"];
        } catch ( Exception $e ) {
            // there are no items so $item will remain null. 
        }
    } else {
        // and item has already been read and passed back into us
      
        // reconstitute the object sent from the browser		
        $currentItem = $jsonDas->decode($objectString);
	
        // get the title from the object
        $title = $currentItem["title"];

        // find the next sibling of the item that matches the title
        $matchfound = false;
        foreach ( $rss->channel->item as $rssitem ) {   
            if ( $matchfound == true ) {
                $item = $rssitem;
                break;
            }
            if ( $rssitem->title == $title ) {
                $matchfound = true;
            }
        }     
    }
	
    if ( $item == null ) {
        $item = "No items in feed";
    }
			
    echo $jsonDas->encode($item);
	
} catch ( Exception $e ) {
    echo "Caught exception: \n";
    print_r($e);
}
?>