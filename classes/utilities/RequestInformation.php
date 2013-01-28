<?php

namespace utilities;

class RequestInformation {
	
	public static function isAjax()
	{
		if(array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] === 'XMLHttpRequest')
		{
			return true;
		}
		return false;
	}
	
}

?>