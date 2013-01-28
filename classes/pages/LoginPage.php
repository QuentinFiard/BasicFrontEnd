<?php

namespace pages;

use utilities\Server;

use process\Frankiz;

use nav\UnregisteredOnlyPage;

require_once 'classes/nav/UnregisteredOnlyPage.php';
require_once 'classes/process/Frankiz.php';
require_once 'classes/utilities/Server.php';

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

	public function includeContent()
    {
        if(Frankiz::hasFrankizResponse())
        {
            Frankiz::checkResponseValidity();

            if(isset($_GET['location']) && !empty($_GET['location']) && $_GET['location']!=Server::getServerFullURL())
            {
                header("Location: ".$_GET['location'].'/login?'.http_build_query($_GET));
                exit();
            }

            // Processing response
            Frankiz::processResponse();

            header("Location: ".Server::getServerFullURL());
            exit();
        }
        else
        {
            Frankiz::startFrankizAuth();
        }
    }
}

?>