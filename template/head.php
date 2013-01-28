<?php
use pages\LoginPage;

use utilities\Server;

global $currentPage;

require_once('classes/utilities/Server.php');
require_once('classes/structures/User.php');

global $user;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#" xml:lang="fr">
    <head>
    	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
    	<link rel="icon" type="image/png" href="favicon.png" />

        <title><?php echo htmlentities($this->getTitle()) ?></title>

        <meta name="description" content="Mon super site" />
    	<meta name="keywords" content="my,super,keywords" />
        <meta name="author" content="Quentin Fiard" />

        <link rel="stylesheet" href="<?php echo Server::getServerRoot(); ?>/css/reset.css" type="text/css"  media="screen" />
        <link rel="stylesheet" href="<?php echo Server::getServerRoot(); ?>/css/shared.css" type="text/css"  media="screen" />

    	<script type="text/javascript" src="<?php echo Server::getServerRoot(); ?>/js/jquery.js"></script>
    	<script type="text/javascript" src="<?php echo Server::getServerRoot(); ?>/js/shared.js"></script>

    	<?php if(file_exists(Server::getServerPath().$currentPage->getPageScriptPath())) { ?>
        	<script type="text/javascript" src="<?php echo Server::getServerRoot().$currentPage->getPageScriptPath(); ?>"></script>
    	<?php } ?>
    	<?php if(file_exists(Server::getServerPath().$currentPage->getPageStylePath())) { ?>
        	<link href="<?php echo Server::getServerRoot().$currentPage->getPageStylePath(); ?>" rel="stylesheet" type="text/css" />
    	<?php } ?>

    </head>

    <body>
        <header>Mon header</header>
	    <div id="contentWrapper">