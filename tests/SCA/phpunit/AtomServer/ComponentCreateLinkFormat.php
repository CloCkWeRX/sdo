<?php

require "SCA/SCA.php";

/**
 * @service
 * @binding.atom
 */
class ComponentCreateLinkFormat {

    /**
	 * Just indicate that the input got here and matched the input sent by the client. 
	 *
	 */
    function create($in)
    {
        $xmlFormatEntry = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:tns="http://www.w3.org/2005/Atom">      
      <id>http://localhost:1112/MegTest.php/1</id>
      <title>This is the one that should remain</title>
      <updated>2006-12-19 12:06:50</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://localhost:1112/MegTest2.php/1" />
      <link href="http://localhost:1112/MegTest3.php/1" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Fred</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>


EOF;
        return $xmlFormatEntry;
    }


}

?>