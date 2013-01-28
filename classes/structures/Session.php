<?php

namespace structures;

require_once 'classes/structures/FrankizUser.php';
require_once 'classes/structures/ExternalUser.php';

class Session {
	
	static private $shared_ = null;
	
	private function __construct() {
		session_start();
	}
	
	public static function init()
	{
		if(!isset(self::$shared_))
		{
			self::$shared_ = new Session();
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
	
	private function getValueForKeyPrivate($key)
	{
		if(!array_key_exists($key,$_SESSION))
		{
			return null;
		}
		return unserialize($_SESSION[$key]);
	}
	
	public static function getValueForKey($key)
	{
		return self::shared()->getValueForKeyPrivate($key);
	}
	
	private function setValueForKeyPrivate($key,$value)
	{
		$_SESSION[$key] = serialize($value);
	}
	
	public static function setValueForKey($key,$value)
	{
		return self::shared()->setValueForKeyPrivate($key,$value);
	}
	
	private function unsetKeyPrivate($key)
	{
		if(array_key_exists($key,$_SESSION))
		{
			unset($_SESSION[$key]);
		}
	}
	
	public static function unsetKey($key)
	{
		return self::shared()->unsetKeyPrivate($key);
	}
}

Session::init();

?>