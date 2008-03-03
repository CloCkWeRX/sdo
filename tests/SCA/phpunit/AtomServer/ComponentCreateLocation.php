<?php

require "SCA/SCA.php";

/**
 * @service
 * @binding.atom
 */
class ComponentCreateLocation {

    /**
	 * Just indicate that the input got here and matched the input sent by the client. 
	 *
	 */
    function create($in)
    {
        $xmlFormatEntry = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:tns="http://www.w3.org/2005/Atom">
  <id>http://meglet_response</id>
  <title>meglet</title>
  <updated>meglet</updated>
  <author>
    <name>meglet</name>
  </author>
  <link rel="edit">h</link>
  <content type="text">Component's create() method reached
  </content>
</entry>


EOF;
        return $xmlFormatEntry;
    }


}

?>