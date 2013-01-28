<?php

namespace nav;

use pages\LogoutPage;

use pages\LoginPage;

require_once 'classes/pages/LoginPage.php';
require_once 'classes/pages/LogoutPage.php';

require_once 'classes/utilities/Server.php';

use \utilities\Server;

class Root extends Page {
	static private $page = null;

	static public function getPage()
	{
		if(self::$page==null)
		{
			self::$page = new Root();

		}
		return self::$page;
	}

	public function __construct()
	{
		parent::__construct('/');

		// Add root children here

		$this->addChild(LoginPage::getPage());
		$this->addChild(LogoutPage::getPage());
	}

}

?>