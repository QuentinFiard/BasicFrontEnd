<?php

namespace pages;

use nav\UnregisteredOnlyPage;

require_once 'classes/nav/UnregisteredOnlyPage.php';

class LoginPage extends UnregisteredOnlyPage {
	private static $page = null;

	public static function getPage()
	{
		if(self::$page==null)
		{
			self::$page = new LoginPage();
		}
		return self::$page;
	}

	public function __construct()
	{
		parent::__construct("login","Login");
	}
}

?>