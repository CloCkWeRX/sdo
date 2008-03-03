<?php

require "SCA/SCA.php";

/**    
 * @service
 * @binding.atom
 */
class ComponentNoCreateMethod {

	/**
	 * Just indicate that the input got here and matched the input sent by the client. 
	 *
	 */
	function creosote($in)
	{
		//should not get this far
	}


}

?>