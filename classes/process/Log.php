<?php

namespace process;

use utilities\Server;

require_once 'classes/utilities/Server.php';

class Log {

	static private function logHeader()
	{
		return date("[Y-m-d H:i:s] ",time());
	}

	static public function logError($message)
	{
		$logFile = fopen(Server::getServerPath().'/log/error.log', 'a');
		fwrite($logFile, self::logHeader());
		fwrite($logFile, $message);
		fwrite($logFile, "\n");
		fclose($logFile);
	}

}

?>