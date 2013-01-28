<?php

namespace database;

abstract class DatabaseObject {

	abstract public function getProperties();
	abstract public function getProperty($key);
	abstract public function setProperty($key,$value);

	/*
	public function getProperty($key)
	{
	    $properties = $this->getProperties();
		if(!array_key_exists($key, $properties))
		{
			return null;
		}
		return $this->$key;
	}

	public function setProperty($key,$value)
	{
	    $this->$key = $value;
	}

	public function getProperties()
	{
		return get_class_vars(get_class());
	}*/

	public function __construct($data) {
		$this->updateWithData($data,true);
	}

	public function updateWithData($data,$constructor=false)
	{
		$properties = $this->getProperties();
		foreach($properties as $key => $default_value)
		{
			if(array_key_exists($key, $data))
			{
			    $this->setProperty($key, $data[$key]);
			}
			else if($constructor)
			{
			    $this->setProperty($key, null);
			}
		}
	}
}

?>