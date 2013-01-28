<?php

use structures\User;

use utilities\RequestInformation;

use nav\PageTree;

use utilities\Miscellaneous;

use \database\Database;

require_once 'classes/database/Database.php';
require_once 'classes/utilities/Miscellaneous.php';
require_once 'classes/utilities/RequestInformation.php';
require_once 'classes/structures/User.php';

require_once 'classes/nav/PageTree.php';

global $currentPage;

if(!isset($currentPage))
{
	ob_end_clean();
	header('HTTP/1.0 404 Not Found');
	echo "<h1>404 Not Found</h1>";
	echo "The page that you have requested could not be found.";
	exit();
}

global $user;
$user = User::currentUser();

$currentPage->checkSecurityGrant();
if(RequestInformation::isAjax())
{
	ob_end_clean();
	$response = $currentPage->handleAjaxRequest();
	echo json_encode($response);
	exit;
}
else
{
	$currentPage->includeContent();
	return;
}

?>