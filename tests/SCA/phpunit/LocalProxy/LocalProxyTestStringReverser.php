<?php
include_once "SCA/SCA.php";
/**
 * 
 * NOTE: uses references to ask for pass-by-reference
 * 
 * @service
 */
class LocalProxyTestStringReverser
{
	public function reverseStringArgument(&$str)
	{
		$str = strrev($str);
	}
}
?>
