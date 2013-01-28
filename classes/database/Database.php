<?php

namespace database {

use utilities\Server;

use process\Log;

require_once 'classes/utilities/Miscellaneous.php';
require_once 'classes/utilities/Server.php';
require_once 'classes/process/Log.php';

class Database {
    static private $shared_ = null;

    private $conn;

    private function __construct()
    {
        try {
            $this->conn = new \PDO('mysql:host=localhost;dbname=DBNAME', 'DBUSER', 'DBPASS');
            $this->conn->exec("SET NAMES utf8");

            $schemaIsValid = $this->checkDatabaseSchema();
            if(!$schemaIsValid)
            {
                $this->updateDatabaseSchema();
            }
        } catch (\PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    public static function init()
    {
        if(!isset(self::$shared_))
        {
            self::$shared_ = new Database();
        }
    }

    public static function shared()
    {
        if(!isset(self::$shared_))
        {
            self::init();
        }
        return self::$shared_;
    }

    public function logQuery($query,$bindings,$output=null)
    {
        $res = "";
        $chars = str_split($query);
        $i=0;
        $bindings_details = var_export($bindings,true);
        foreach($chars as $char)
        {
            if($char=="?")
            {
                if($i>=count($bindings))
                {
                    Log::logError("Mismatch between query and number of bindings");
                    Log::logError("Query : ".$query);
                    Log::logError("Bindings : ".$bindings_details);
                    return;
                }
                $res .= $bindings[$i];
                $i++;
            }
            else
            {
                $res .= $char;
            }
        }
        Log::logError("Query : ".$query);
        Log::logError("Bindings : ".$bindings_details);
        Log::logError("Result : ".$res);
        if(isset($output))
        {
            $output_details = var_export($output,true);
            Log::logError("Output : ".$output_details);
        }
    }

    public function getArrayForClassQueryAndBindings($class,$query,$bindings)
    {
        try {
            $stmt = $this->conn->prepare($query);
            for($i=0; $i<count($bindings) ; $i++)
            {
                if(is_int($bindings[$i]))
                {
                    $stmt->bindParam($i+1, $bindings[$i], \PDO::PARAM_INT);
                }
                else
                {
                    $stmt->bindParam($i+1, $bindings[$i], \PDO::PARAM_STR);
                }
            }
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $res = array();
            while($row)
            {
                $res[] = new $class($row);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
            $stmt->closeCursor();
            //$this->logQuery($query, $bindings,$res);
            return $res;
        }
        catch(\Exception $e)
        {
            Log::logError("Query error");
            Log::logError("Error code : ".$e->getCode());
            Log::logError("Error description : ".$e->getMessage());
            $this->logQuery($query, $bindings);
        }
    }

    public function getSingleValueForQueryAndBindings($key,$query,$bindings)
    {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($bindings);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if($row)
        {
            return $row[$key];
        }
        return null;
    }

    public function getExistsRowForQueryAndBindings($query,$bindings)
    {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($bindings);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if($row)
        {
            return true;
        }
        return false;
    }

    private function getAlterScript($oldSchemaHash,$schemaHash)
    {
        $path = Server::getServerPath().'/database/alter_'.substr($oldSchemaHash,0,10).'_to_'.substr($schemaHash,0,10).'.sql';
        if(file_exists($path))
        {
            return file_get_contents($path);
        }
        return null;
    }

    private function getCreateScript()
    {
        return file_get_contents(Server::getServerPath().'/database/create.sql');
    }

    private function logCreateScript()
    {
        $createScript = $this->getCreateScript();
        $schemaHash = hash('sha256', $createScript);
        $this->setConfigurationForField("schemaHash", $schemaHash);
        file_put_contents(Server::getServerPath().'/database/create_'.substr($schemaHash,0,10).'.sql', $createScript);
    }

    private function checkDatabaseSchema()
    {
        if($this->needsCreation())
        {
            return false;
        }
        if(!$this->hasConfigurationForField("schemaHash"))
        {
            return false;
        }
        $oldSchemaHash = $this->getConfigurationForField("schemaHash");
        $schemaHash = hash('sha256', $this->getCreateScript());
        if($schemaHash!=$oldSchemaHash)
        {
            return false;
        }
        return true;
    }

    private function needsCreation()
    {
        $stmt = $this->conn->prepare("SHOW TABLES LIKE 'Configuration';");
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt->closeCursor();
        if(!$res)
        {
            return true;
        }
        return false;
    }

    private function updateDatabaseSchema()
    {
        $schemaHash = hash('sha256', $this->getCreateScript());
        if($this->needsCreation())
        {
            $this->conn->beginTransaction();
            $this->conn->exec($this->getCreateScript());
            $this->conn->commit();
        }
        else
        {
            $oldSchemaHash = $this->getConfigurationForField("schemaHash");
            $alterScript = $this->getAlterScript($oldSchemaHash, $schemaHash);
            if(!$alterScript)
            {
                Log::logError("Missing alter script from ".substr($oldSchemaHash, 0, 10).' to '.substr($schemaHash, 0, 10). ' : '.Server::getServerPath().'/database/alter_'.substr($oldSchemaHash, 0, 10).'_to_'.substr($schemaHash, 0, 10).'.sql');
                die();
            }
            $this->conn->beginTransaction();
            $this->conn->exec($alterScript);
            $this->conn->commit();
        }
        $this->logCreateScript();
    }

    /**
     * Configuration
     */

    public function getConfigurationForField($field)
    {
        $cmd = 'SELECT value FROM Configuration WHERE field=?';
        $bindings = array($field);
        $stmt = $this->conn->prepare($cmd);
        $stmt->execute($bindings);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if(!$res)
        {
            return null;
        }
        $res = unserialize($res['value']);
        return $res;
    }

    public function hasConfigurationForField($field)
    {
        $stmt = $this->conn->prepare('SELECT value FROM Configuration WHERE field=?');
        $stmt->execute(array($field));
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if(!$res)
        {
            return false;
        }
        return true;
    }

    public function setConfigurationForField($field,$value)
    {
        $value = serialize($value);

        $cmd = 'SELECT * FROM Configuration WHERE field=?';
        $bindings = array($field);

        $stmt = $this->conn->prepare($cmd);
        $stmt->execute($bindings);
        $res = $stmt->fetch();

        $stmt->closeCursor();

        if($res)
        {
            $cmd = 'UPDATE Configuration SET value=? WHERE field=?';
            $bindings = array($value,$field);

            $stmt = $this->conn->prepare($cmd);
            $stmt->execute($bindings);
            $stmt->closeCursor();
        }
        else
        {
            $cmd = 'INSERT INTO Configuration (`field`,`value`) VALUES (?,?)';
            $bindings = array($field,$value);
            $stmt = $this->conn->prepare($cmd);

            $stmt->execute($bindings);
            $stmt->closeCursor();
        }
    }

    /*
     * SavableObjects
     */

    public function saveObject($object)
    {
        $shouldInsert = true;
        if($object->primaryKey()!=null && $object->getPrimaryKeyValue()!=null)
        {
            $cmd = 'SELECT * FROM '.$object->tableName().' WHERE '.$object->primaryKey().'=?';
            $stmt = $this->conn->prepare($cmd);
            $stmt->execute(array($object->getPrimaryKeyValue()));
            $row = $stmt->fetch();
            $stmt->closeCursor();
            if($row)
            {
                $shouldInsert = false;
            }
        }

        $first = true;
        $bindings = array();
        $keys = $object->getProperties();
        $cmd="";

        if(!$shouldInsert)
        {
            $cmd = "UPDATE ".$object->tableName()." SET ";
            foreach($keys as $key => $defaultValue)
            {
                if($key!=$object->primaryKey())
                {
                    if(!$first)
                    {
                        $cmd .= ', ';
                    }
                    $cmd .= $key.'=?';
                    $bindings[] = $object->getProperty($key);
                    $first = false;
                }
            }
            $cmd .= ' WHERE '.$object->primaryKey().'=?';
            $bindings[] = $object->getPrimaryKeyValue();
        }
        else
        {
            $cmd = "INSERT INTO ".$object->tableName()." (";
            $values = '';
            foreach($keys as $key => $defaultValue)
            {
                if(!$first)
                {
                    $cmd .= ',';
                    $values .= ',';
                }
                $cmd .= $key;
                $values .= '?';
                $bindings[] = $object->getProperty($key);
                $first = false;
            }

            $cmd .= ') VALUES ('.$values.')';
        }
        $stmt = $this->conn->prepare($cmd);
        $stmt->execute($bindings);
        if($object->primaryKey()!=null && $object->getPrimaryKeyValue()==null)
        {
            $object->setPrimaryKeyValue($this->conn->lastInsertId());
        }
        $stmt->closeCursor();
    }

    public function removeObject($object)
    {
        $stmt = $this->conn->prepare('DELETE FROM '.$object->tableName().' WHERE '.$object->primaryKey().'=?');
        $stmt->execute(array($object->getPrimaryKeyValue()));
    }
}

Database::init();

}

?>
