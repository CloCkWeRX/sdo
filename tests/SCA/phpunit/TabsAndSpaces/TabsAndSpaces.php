<?php

include_once "SCA/SCA.php";

/**
* The following script contains different string formats to attempt to ensure
* that white space differences are filtered out in the 'wsdl generation' process.
* 
* NOTE NOTE NOTE !!!!!
* If you save this file in an editor that converts tabs to spaces then you
* will need to put the tabs back again!!!
*
*/

/**
 * @service
 * @binding.ws
 *
 */
class TabsAndSpaces
{
	/**
	 * binding annotation has spaces
	 *
     * @reference
     * @binding.php                                   spaces.php
     */
	public $spaces;

	/**
	 * binding annotation  has tabs
	 *
     * @reference
     * @binding.ws									tabs.wsdl
     */
	public $tabs;

	/**
     * @param                       string               $ticker (the ticker symbol)
     */
	function spaces( $ticker )
	{
	}

	/**
     * @param				string				$ticker			(the ticker symbol)	
     */
	function tabs( $ticker )
	{
	}

}

?>