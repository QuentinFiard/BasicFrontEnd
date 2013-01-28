<?php

namespace utilities;

class Server {

	static public $serverPath = null;

	static public function getServerRoot(){
		return '';
	}

	static public function getServerPath()
	{
		return self::$serverPath;
	}

	static public function getServerFullURL(){
		return 'http://'.$_SERVER['HTTP_HOST'].Server::getServerRoot();
	}

}

Server::$serverPath = dirname(dirname(dirname(__FILE__)));

?>