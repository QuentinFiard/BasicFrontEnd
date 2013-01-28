<?php

namespace pages;

use utilities\Server;

use structures\Session;

use nav\RegisteredOnlyPage;
require_once 'classes/nav/RegisteredOnlyPage.php';
require_once 'classes/structures/Session.php';
require_once 'classes/utilities/Server.php';

use nav\LeafPage;

class LogoutPage extends RegisteredOnlyPage {
	private static $page = null;

	public static function getPage()
	{
		if(self::$page==null)
		{
			self::$page = new LogoutPage();
		}
		return self::$page;
	}

	public function __construct()
	{
		parent::__construct("logout","Logout");
	}

	public function checkSecurityGrant()
    {
        parent::checkSecurityGrant();

        Session::unsetKey('userId');
        header("Location: ".Server::getServerFullURL());
        exit;
    }
}

?>