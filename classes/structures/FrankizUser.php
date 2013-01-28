<?php

namespace structures;

use database\SavableDatabaseObject;

use process\SecurityLevel;

require_once ('classes/structures/User.php');
require_once 'classes/database/Database.php';

use \database\Database;

use structures\User;

class FrankizUser extends SavableDatabaseObject /* implements User */ {

    private $userId;
    private $user;

    private $uid;
	private $nickname;
	private $hruid;
	private $class = null;
	private $isX_ = null;

	protected $securityLevel;

	public function primaryKey()
    {
        return 'userId';
    }

	public function tableName()
    {
        return 'FrankizUser';
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
        unset($res['user']);
        return $res;
	}

	public function __construct($data) {
		parent::__construct($data);
	    $this->user = new User($data);
	}

	public function updateWithData($data,$constructor=false)
	{
	    if(!$constructor)
	    {
    		$this->user->updateWithData($data,$constructor);
	    }
		$properties = get_class_vars(get_class());
		foreach($properties as $key => $default_value)
		{
			if(array_key_exists($key, $data))
			{
				if($key=='securityLevel' && !is_object($data[$key]))
				{
					$this->$key = SecurityLevel::levelWithLevel($data[$key]);
				}
				else if($key=='isX_')
				{
					$this->$key = ($data[$key]=='1');
				}
				else
				{
					$this->$key = $data[$key];
				}
			}
			else if($constructor)
			{
				$this->$key = null;
			}
		}
		if(array_key_exists('promos', $data))
		{
			$this->isX_ = false;
			foreach($data['promos'] as $promo)
			{
				$matches = array();
				if (preg_match('/^([a-z_]+)([1-9][0-9]{3})$/', $promo, $matches)) {
					$year = (integer)$matches[2];
					if (!$this->class || $year > $this->class)
					{
						$this->class = $promo;
						$this->isX_ = ($matches[1]=="x");
					}
				}
			}
		}
	}

	public function isFrankizUser()
	{
		return true;
	}

	public function isRegistered()
	{
		return $this->securityLevel->isRegistered();
	}

	public function isMember()
	{
		return $this->securityLevel->isMember();
	}

	public function isAdmin()
	{
		return $this->securityLevel->isAdmin();
	}

	public function isX()
	{
		return $this->isX_;
	}

	public function isAdherentKes() {
		return $this->isCotisant() || ($this->isX() && (in_array(strtolower($this->class), array('x2010','x2011','x1829'))) && !$this->isNonCotisant());
	}

	public function isExt() {
		return false;
	}

	public function getDisplayName()
	{
		if(isset($this->nickname) && $this->nickname != "")
		{
			return $this->nickname;
		}
		return $this->user->getDisplayName();
	}

	public function save()
	{
	    $this->user->save();
	    $this->userId = $this->user->getUserId();
		parent::save();
	}

	public function getNickname() {
		return $this->nickname;
	}

	public function getClass() {
		return $this->class;
	}

	public function getSport() {
		return $this->sport;
	}

	public function is2010()
	{
		return strtolower($this->class) == 'x2010';
	}

	public function is2011()
	{
		return strtolower($this->class) == 'x2011';
	}

	public function isNonCotisant() {
		return Database::shared()->isFrankizUserNonCotisant($this);
	}

	public function isCotisant() {
		return Database::shared()->isFrankizUserCotisant($this);
	}

	public function getUID() {
		return $this->uid;
	}

	public function setUserId($userId)
    {
        $this->user->setUserId($userId);
        $this->userId = $userId;
    }

    public function __call($method,$args)
    {
        if(method_exists($this, $method))
        {
            return call_user_func_array(array($this, $method), $args);
        }
        return call_user_func_array(array($this->user, $method), $args);
    }

}

?>