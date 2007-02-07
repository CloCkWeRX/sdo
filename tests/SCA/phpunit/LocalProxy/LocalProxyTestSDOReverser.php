<?php
include_once "SCA/SCA.php";
/**
 * 
 * NOTE: uses a reference to ensure pass-by-reference
 * 
 * @service
 * @types PersonNamespace person.xsd
 */
class LocalProxyTestSDOReverser
{
	public function reverseSDOArgument(&$sdo)
	{
	    foreach($sdo as $property=>$value) {
	        $sdo[$property] = strrev($value);
	    }
	}
}
?>