<html>
<body>
<?php

include 'SCA/SCA.php';
date_default_timezone_set('Europe/London');

/***********************************************/
/* Get the component which calls the Atom feed */
/***********************************************/
$contact_service = SCA::getService('ContactFeedConsumer.php');

/***********************************************/
/* Create an XML entry and send it to the      */
/* Atom component via the ContactFeedConsumer. */
/***********************************************/
echo '<p>';
echo '<b>Testing Create<br/></b>';
echo '<p>This is the XML we are going to send';
$entryXML = create_entry();
echo '<pre>';
write_entry_xml($entryXML);
echo '</pre>';
$entry = $contact_service->create($entryXML);

// Write out the response (the id should have been
// changed to that of the database.
echo '<b><br/>Response from create (the id should be different)<br/></b>';
echo '<pre>';
write_entry($entry);
echo '</pre>';
echo '</p>';
echo '<p>';

// Remember the id because we will delete it later
// I would like to just get the id value, but it is the uri
$segments = explode('/', $entry->id[0]->value);
$id = $segments[count($segments)-1];

/***********************************************/
/* Retrieve the feed.                          */
/***********************************************/
echo '<p>';
echo '<b>Testing Enumeration<br/></b>';
$feed = $contact_service->enumerate();

echo '<pre>';
write_feed($feed);
echo '</pre>';
echo '</p>';
echo '<p>';

/***********************************************/
/* Retrieve entry with id = 1 (Note: this must */
/* have been previously created in the         */
/* database.                                   */
/***********************************************/
echo '<p>';
echo '<b>Testing Retrieve<br/></b>';
echo 'Retrieving the entry with id = 1.<br/>' ;
$entry = $contact_service->retrieve(1);

echo '<pre>';
write_entry($entry);
echo '</pre>';
echo '</p>';
echo '<p>';


/***********************************************/
/* Update the updated time for the entry with  */
/* id = 1.                                     */
/***********************************************/
echo '<p>';
echo '<b>Testing Update<br/></b>';
echo 'Updating the <i>updated</i> field in entry with id = 1<br/>' ;
$entry->updated[0]->value = date(DATE_W3C) ; //date('Y-m-j G-i-s');
if ($contact_service->update(1, $entry)) echo 'Update worked';
echo '</p>';
echo '<p>';

/***********************************************/
/* Retrieve entry with id = 1 (Note: this must */
/* have been previously created in the         */
/* database.                                   */
/***********************************************/
echo '<p>';
echo '<b>Testing Retrieve<br/></b>';
echo 'Retrieving the entry with id = 1. Note the changed field.<br/>' ;
$entry = $contact_service->retrieve(1);

echo '<pre>';
write_entry($entry);
echo '</pre>';
echo '</p>';
echo '<p>';


/***********************************************/
/* Delete the entry we created earlier.        */
/***********************************************/
echo '<p>';
echo '<b>Testing Delete<br/></b>';
echo 'Deleting entry with id = ' . $id . '<br/>';
if ($contact_service->delete($id)) echo 'Delete worked';
echo '</p>';
echo '<p>';



function write_entry($entry) {
    $xmldas = SDO_DAS_XML::create('Atom1.0.xsd');
    $doc = $xmldas->createDocument('http://www.w3.org/2005/Atom', 'entry', $entry);
    $str = $xmldas->saveString($doc,2);
    // following line taken from http://programming-oneliners.blogspot.com/2006/03/remove-blank-empty-lines-php-29.html
    $str = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $str);

    if (php_sapi_name() == "cgi-fcgi")  // running under Zend Studio
    echo $str;
    else
    echo htmlspecialchars($str);
}

function write_feed($feed) {
    $xmldas = SDO_DAS_XML::create('Atom1.0.xsd');
    $doc = $xmldas->createDocument('http://www.w3.org/2005/Atom', 'feed', $feed);
    if (php_sapi_name() == "cgi-fcgi")
    echo nl2br($xmldas->saveString($doc));
    else
    echo htmlspecialchars($xmldas->saveString($doc));
}

function write_entry_xml($entryXML) {
    if (php_sapi_name() == "cgi-fcgi")
    echo nl2br($entryXML);
    else
    echo htmlspecialchars($entryXML);
}

function create_entry() {
    // Set up some content
    $id = 'urn:uuid:1225c695-cfb8-4ebb-aaaa-80da344efa6a';
    $title = 'An Atom Entry from ContactService';
    $updated = date(DATE_W3C);
    $author = 'Graham Charters';
    if (isset($_SERVER['HTTP_HOST'])) {
        $server_name = $_SERVER['HTTP_HOST'];
    } else {
        $server_name = 'localhost';
    }
    $server_name =
    $link = 'http://' . $server_name . $_SERVER['SCRIPT_NAME'] . '/' . $id;

    $entryXML = <<< ENTRY
<?xml version="1.0" encoding="UTF-8"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:tns="http://www.w3.org/2005/Atom">
  <id>$link</id>
  <title type="text">$title</title>
  <updated>$updated</updated>
  <author>
    <name>$author</name>
  </author>
  <link rel="edit">$link</link>
  <content type="xhtml">
    <div>
      <ul class="xoxo contact" title="contact" >
        <li class="shortname" title="shortname">Gra</li>
        <li class="fullname" title="fullname">Graham Charters</li>
        <li class="email" title="email">gcharters@googlemail.com</li>
      </ul>
    </div>
  </content>
</entry>
ENTRY;

    return $entryXML;
}
?>
</body>
</html>