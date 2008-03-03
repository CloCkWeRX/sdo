<?php

require "SCA/SCA.php";

/**    
 * @service
 * @binding.atom
 */
class ComponentDeleteReturnTrueResponse {

	/**
	 * Just indicate that the input got here and matched the input sent by the client. 
	 *
	 */
	function delete($in)
	{
		return true;
	}


}

?>