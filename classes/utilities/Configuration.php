<?php

namespace utilities;

use database\Database;

require_once 'classes/database/Database.php';

class Configuration {

	static public function getConfigurationForField($field)
	{
		return Database::shared()->getConfigurationForField($field);
	}

	static public function hasConfigurationForField($field)
	{
		return Database::shared()->hasConfigurationForField($field);
	}

	static public function setConfigurationForField($field,$value)
	{
		Database::shared()->setConfigurationForField($field,$value);
	}
}

?>