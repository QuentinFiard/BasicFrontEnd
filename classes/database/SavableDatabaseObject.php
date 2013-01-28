<?php

namespace database;

require_once 'classes/database/DatabaseObject.php';

abstract class SavableDatabaseObject extends DatabaseObject {

    abstract public function primaryKey();
    abstract public function tableName();

    public function getPrimaryKeyValue()
    {
        $key = $this->primaryKey();
        return $this->getProperty($key);
    }

    public function setPrimaryKeyValue($value)
    {
        $key = $this->primaryKey();
        $this->setProperty($key, $value);
    }

	public function save()
	{
        Database::shared()->saveObject($this);
	}

	public function remove()
	{
        Database::shared()->removeObject($this);
	}
}

?>