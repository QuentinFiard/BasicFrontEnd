<?php

namespace nav;

use structures\User;

require_once ('classes/nav/RegisteredOnlyPage.php');
require_once ('classes/utilities/Server.php');
require_once ('classes/structures/User.php');

use \nav\Page;
use \utilities\Server;

class AdminOnlyPage extends RegisteredOnlyPage {

	public function checkSecurityGrant() {
		parent::checkSecurityGrant();

		global $user;
		if(!User::isCurrentUserAdmin())
		{
			header('Location: '.Server::getServerFullURL());
			exit();
		}
	}

}

?>