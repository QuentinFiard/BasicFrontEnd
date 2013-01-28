<?php

namespace process;

require_once 'classes/utilities/Server.php';
require_once 'classes/process/Log.php';

use utilities\Server;

use structures\FrankizUser;
use structures\Session;

use exceptions\InvalidContactInfos;

use utilities\FormValidator;

use exceptions\InvalidResponse;

require_once ('classes/exceptions/InvalidResponse.php');
require_once ('classes/exceptions/InvalidContactInfos.php');
require_once('classes/utilities/FormValidator.php');
require_once('classes/structures/FrankizUser.php');
require_once('classes/structures/Session.php');

require_once 'classes/database/Database.php';

use \database\Database;

use \exceptions;
use \utilities;

class Frankiz {
	static private $key = 'FRANKIZ_AUTH_KEY'; // Clé nécessaire à l'authentification interne de Frankiz, communiquée par le BR
	/**
	 * url de la page de login, doit correspondre *exactement* à celle entrée dans
	 * la base de données de Frankiz (définie lors de l'inscription)
	 */
	static private $site = "SITE_LOGIN_PAGE";

	static public function hasFrankizResponse()
	{
		return isset($_GET['response']);
	}

	static public function startFrankizAuth()
	{
		// Copyright BR 2010
		/**
		 * Prendre le timestamp permet d'éviter le rejeu de la requête
		 */
		$timestamp = time();
		/**
		 * Nature de la requête.
		 * Fkz renverra ici à la fois les noms de la personne mais aussi ses droits dans différents groupes.
		 * Il faut cependant que le site ait les droits sur les informations en question (à définir lors de son inscription).
		 */
		$request = json_encode(array('names', 'rights', 'email', 'promo', 'sport'));

		$hash = md5($timestamp . self::$site . self::$key . $request);

		$remote  = 'https://www.frankiz.net/remote?timestamp=' . $timestamp .
		'&site=' . self::$site .
		'&location=' . Server::getServerFullURL() .
		'&hash=' . $hash .
		'&request=' . $request;
		header("Location:" . $remote);
		exit();
	}

	static public function checkResponseValidity()
	{
	    header('Content-type: text/plain; charset:UTF-8');

		if(!isset($_GET['timestamp']) || !isset($_GET['response']) || !isset($_GET['hash']))
		{
			throw new InvalidResponse("La réponse de Frankiz est incomplète");
		}

		$timestamp = $_GET['timestamp'];
		$hash = $_GET['hash'];
		$response = urldecode($_GET['response']);

		// Frankiz security protocol
		if(abs($timestamp - time()) > 600)
		{
			throw new InvalidResponse("Frankiz n'a pas répondu dans un délai raisonnable, la requête a été annulée.");
		}
		if(md5($timestamp . self::$key . $response) != $hash)
		{
			throw new InvalidResponse("Votre compte Frankiz semble victime d'une attaque. Merci de contacter le BR pour plus d'informations.");
		}
	}

	static public function processResponse()
	{
		// Copyright BR 2010 & Quentin Fiard
		// Read request
		self::checkResponseValidity();

		$response = urldecode($_GET['response']);
		$response = json_decode($response, true);

		$response['firstName'] = $response['firstname'];
		$response['lastName'] = $response['lastname'];
		unset($response['firstname']);
		unset($response['lastname']);

		$admin = false;
		$member = false;

		if(array_key_exists('rights', $response))
		{
			$rights = $response['rights'];
			if(array_key_exists('NOM_DU_BINET', $rights))  // Droits gérés par Frankiz
			{
				$rights_binet = $rights['NOM_DU_BINET'];
				foreach($rights_binet as $value)
				{
					if($value == "admin")
					{
						$admin = true;
					}
					if($value == "member")
					{
						$member = true;
					}
				}
			}
		}

		$securityLevel = SecurityLevel::$Registered;

		if($member)
		{
			$securityLevel = SecurityLevel::$Member;
		}
		if($admin)
		{
			$securityLevel = SecurityLevel::$Admin;
		}


		$response['securityLevel'] = $securityLevel;

		$fields = array('uid' => 'number',
						'firstName' => 'name',
						'lastName' => 'name',
						'email' => 'email',
						'promo' => 'number');

		$mandatories = $fields;
		//unset($mandatories['nickname']);
		$mandatories = array_keys($mandatories);

		$validator = new FormValidator($fields,$mandatories);

		if(!$validator->validate($response))
		{
		    $details = var_export($validator,true);
		    $responseDetails = var_export($response,true);
		    Log::logError('Exception - InvalidContactInfos : '.$details);
		    Log::logError('Response : '.$responseDetails);
			//throw new InvalidContactInfos();
		}

		$user = Database::shared()->getFrankizUserWithUID($response['uid']);

		if(isset($user))
		{
			$user->updateWithData($response);
		}
		else
		{
			$user = new FrankizUser($response);
		}

		$user->save();

		Session::setValueForKey('userId', $user->getUserId());
	}
}

?>