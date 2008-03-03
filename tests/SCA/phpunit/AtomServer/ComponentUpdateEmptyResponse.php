<?php

require "SCA/SCA.php";

/**    
 * @service
 * @binding.atom
 */
class ComponentUpdateEmptyResponse {

	/**
	 * Just indicate that the input got here and matched the input sent by the client. 
	 *
	 */
	function update($in)
	{
		//does not return anything at all
	}


}

?>