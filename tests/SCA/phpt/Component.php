<?php

require "SCA/SCA.php";

SCA::initComponent(__FILE__);

/**
 * @service
 * @binding.soap
 * @types http://www.test.com/info person.xsd (comment)
 */

class Component {

	/**
	 * Reverse a string
	 *
	 * @param string $in (comment)
	 * @return string (comment)
	 */
	function reverse($in)
	{
		return strrev ($in);
	}


	/**
	 * clone a phone object in under a person object
	 *
	 * @param person $person http://www.test.com/info (comment)
	 * @param phone $phone http://www.test.com/info (comment)
	 * @return person http://www.test.com/info (comment)
	 */
	function add($person,$phone)
	{
		$new_phone = $person->createDataObject('phone');
		foreach ($phone as $prop => $val) {
			$new_phone[$prop] = $val;
		}
		return $person;


	}
}

?>