<?php

require "SCA/SCA.php";

/**
 * @service
 * @binding.atom
 */
class ComponentRetrieveResponse {

    /**
	 * Just indicate that the input got here and matched the input sent by the client. 
	 *
	 */
    function retrieve($in)
    {
        $xmlFormatEntry = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:tns="http://www.w3.org/2005/Atom">
  <id>meglet_response</id>
  <title>meglet</title>
  <updated>meglet</updated>
  <author>
    <name>meglet</name>
  </author>
  <link rel="edit">meglet</link>
  <content type="text">Component's retrieve() method reached
  </content>
</entry>


EOF;
        return $xmlFormatEntry;
    }


}

?>