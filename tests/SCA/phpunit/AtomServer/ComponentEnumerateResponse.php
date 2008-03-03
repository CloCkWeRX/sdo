<?php

require "SCA/SCA.php";

/**
 * @service
 * @binding.atom
 */
class ComponentEnumerateResponse {

    /**
	 * Just indicate that the input got here and matched the input sent by the client. 
	 *
	 */
    function enumerate()
    {
        $xmlFormatFeed = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<feed>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:tns="http://www.w3.org/2005/Atom">
  <id>TEST_ID</id>
  <title type="text">TEST_TITLE</title>
  <updated>TEST_DATE</updated>
  <author>
    <name>TEST_AUTHOR</name>
  </author>
  <link rel="edit">TEST_LINK</link>
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
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:tns="http://www.w3.org/2005/Atom">
  <id>TEST_ID2</id>
  <title type="text">TEST_TITLE2</title>
  <updated>TEST_DATE2</updated>
  <author>
    <name>TEST_AUTHOR2</name>
  </author>
  <link rel="edit">TEST_LINK2</link>
  <content type="xhtml">
    <div>
      <ul class="xoxo contact" title="contact" >
        <li class="shortname" title="shortname">Gra2</li>
        <li class="fullname" title="fullname">Graham Charters2</li>
        <li class="email" title="email">gcharters@googlemail.com2</li>
      </ul>
    </div>
  </content>
</entry>
</feed>


EOF;
        return $xmlFormatFeed;
    }


    function retrieve($in){
       
        //nothing in here at the moment - just here so that enumerate() can be reached
    }
}

?>