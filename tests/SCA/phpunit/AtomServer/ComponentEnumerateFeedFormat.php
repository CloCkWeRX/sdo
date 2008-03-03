<?php

require "SCA/SCA.php";

/**
 * @service
 * @binding.atom
 */
class ComponentEnumerateFeedFormat {

    /**
	 * Just indicate that the input got here and matched the input sent by the client. 
	 *
	 */
    function enumerate()
    {
        $xmlFormatFeed = <<<EOF
<?xml version="1.0" encoding="utf-8"?>
  <feed xmlns="http://www.w3.org/2005/Atom" xmlns:tns="http://www.w3.org/2005/Atom">
    <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php</id>
    <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php" rel="self"/>
    <title type="text">Contact Details</title>
    <subtitle>Atom feed of contact details</subtitle>    
    <updated>2006-12-19 12:06:50</updated>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/1</id>
      <title>This is the one that should remain</title>
      <updated>2006-12-19 12:06:50</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/1" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/1" />
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
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/42</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-12 11:28:06</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/42" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/42" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/40</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-12 11:26:01</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/40" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/40" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/39</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-12 11:23:33</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/39" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/39" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/38</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-12 11:22:49</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/38" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/38" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/37</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-12 11:21:35</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/37" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/37" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/36</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-12 11:19:32</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/36" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/36" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/35</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-12 10:40:19</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/35" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/35" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/30</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-07 10:46:54</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/30" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/30" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/29</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-07 10:42:02</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/29" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/29" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/28</id>
      <title>Hello Cheese Lovers</title>
      <updated>2006-12-06 21:28:22</updated>
      <author>
        <name>Mr Cheddar</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/28" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/28" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Cheesy</li>
            <li class="fullname" title="fullname">Mr Cheddar</li>
            <li class="email" title="email">cheese@cheesemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/27</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-06 14:54:54</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/27" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/27" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/26</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-06 14:51:58</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/26" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/26" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/25</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-06 14:22:56</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/25" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/25" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/24</id>
      <title>something bogus made up because of a bug</title>
      <updated>2006-12-06 11:58:08</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/24" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/24" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/23</id>
      <title>An Atom Entry from ContactService</title>
      <updated>2006-12-06 11:54:57</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/23" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/23" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">Gra</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/2</id>
      <title>My first Contact Entry</title>
      <updated>2006-11-29 09:38:54</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/2" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/2" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">charters</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
          </ul>
        </div>
      </content>
    </entry>
    <entry>
      <id>http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/3</id>
      <title>My first Contact Entry</title>
      <updated>2006-11-29 09:38:54</updated>
      <author>
        <name>Graham Charters</name>
      </author>
      <link rel="edit" href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/3" />
      <link href="http://paddy.hursley.ibm.com/Demos/PHPQuebec2007/contactsyndication/ContactFeed.php/3" />
      <content type="xhtml">
        <div xmlns="http://www.w3.org/1999/xhtml">
          <ul class="xoxo contact" title="contact" >
            <li class="shortname" title="shortname">charters</li>
            <li class="fullname" title="fullname">Graham Charters</li>
            <li class="email" title="email">gcharters@googlemail.com</li>
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