<?php

namespace process;

class SecurityLevel {

	const LevelAnyone = 0;
	const LevelRegistered = 1;
	const LevelMember = 2;
	const LevelAdmin = 3;

	public static $Anyone;
	public static $Registered;
	public static $Member;
	public static $Admin;
	
	private static $map = array();
	
	private $securityLevel;
	
	public function __construct($securityLevel)
	{
		$this->securityLevel = $securityLevel;
		self::$map[$securityLevel] = $this;
	}
	
	public static function levelWithLevel($level)
	{
		return self::$map[$level];
	}
	
	public function isRegistered()
	{
		return $this->securityLevel >= self::LevelRegistered;
	}
	
	public function isMember()
	{
		return $this->securityLevel >= self::LevelMember;
	}
	
	public function isAdmin()
	{
		return $this->securityLevel >= self::LevelAdmin;
	}
	
	public function __toString()
	{
		return (string)$this->securityLevel;
	}
	
	public function getSecurityLevel() {
		return $this->securityLevel;
	}

}

SecurityLevel::$Anyone = new SecurityLevel(0);
SecurityLevel::$Registered = new SecurityLevel(1);
SecurityLevel::$Member = new SecurityLevel(2);
SecurityLevel::$Admin = new SecurityLevel(3);

?>