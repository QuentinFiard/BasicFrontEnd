<?php

namespace structures;

use database\SavableDatabaseObject;

use utilities\Miscellaneous;

require_once("classes/structures/Session.php");
require_once("classes/structures/SecurityLevel.php");

require_once 'classes/database/Database.php';
require_once 'classes/database/SavableDatabaseObject.php';

require_once 'classes/utilities/Miscellaneous.php';

use \database\Database;

use \structures\Session;
use \process\SecurityLevel;

class User extends SavableDatabaseObject {
    private static $currentUser_ = false;

	protected $userId;

	private $firstName;
	private $lastName;
	private $email;

	public function primaryKey()
    {
        return 'userId';
    }

	public function tableName()
    {
        return 'User';
    }

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
		$res = get_class_vars(get_class());
		unset($res['currentUser_']);
		return $res;
	}

	public static function currentUser()
	{
	    if(self::$currentUser_===false)
	    {
    		$userId = Session::getValueForKey('userId');
    		if(!$userId)
    		{
    			self::$currentUser_ = null;
    		}
    		self::$currentUser_ = self::userWithUserId($userId);
	    }
	    return self::$currentUser_;
	}

	public static function isCurrentUserRegistered()
	{
	    $user = self::currentUser();
	    if(!isset($user))
	    {
	        return false;
	    }
	    return $user->isRegistered();
	}

	public static function isCurrentUserAdmin()
	{
	    $user = self::currentUser();
	    if(!isset($user))
	    {
	        return false;
	    }
	    return $user->isAdmin();
	}

	public static function userWithUserId($userId)
	{
		return Database::shared()->getUserWithUserId($userId);
	}

	public function isFrankizUser()
	{
		return false;
	}

	public function isX()
	{
		return false;
	}

	function isAdherentKes()
	{
		return false;
	}

	public function isExt()
	{
		return true;
	}

	public function getDisplayName()
	{
		return $this->firstName;
	}

	public function getFullName()
	{
		return $this->firstName.' '.$this->lastName;
	}

	public function __toString()
	{
		$res = "<User>";
		$properties = $this->getProperties();
		foreach($properties as $key => $default_value)
		{
			$res .= "<br/>    <".$key.">".$this->$key.'</'.$key.">";
		}
		$res .= "<br/></User>";
		return $res;
	}

	public function isRegistered()
	{
		return $this->userId!=null;
	}

	public function isMember()
	{
		return false;
	}

	public function isAdmin()
	{
		return false;
	}

	public function is2010()
	{
		return false;
	}

	public function is2011()
	{
		return false;
	}

	public function getUserId() {
		return $this->userId;
	}

	public function setUserId($userId) {
		$this->userId = $userId;
	}

	static public function cmp($user1,$user2)
	{
		$res = strcasecmp($user1->getLastname(), $user2->getLastname());
		if($res==0)
		{
			$res = strcasecmp($user1->getFirstname(), $user2->getFirstname());
		}
		return $res;
	}

	public function isCotisant() {
		return false;
	}

	public function getFirstName()
    {
        return $this->firstName;
    }

	public function getLastName()
    {
        return $this->lastName;
    }

	public function getEmail()
    {
        return $this->email;
    }


}

?>