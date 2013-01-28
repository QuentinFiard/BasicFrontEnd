<?php
header('Content-type:application/javascript');

use structures\User;
chdir('..');

require_once 'classes/utilities/Server.php';
require_once 'classes/structures/User.php';
use \utilities\Server;

global $user;
$user = User::currentUser();

?>
/*
 * Utilities
 */

var serverUrl = "<?php echo Server::getServerRoot(); ?>";

var serverFullUrl = "<?php echo 'http://'.$_SERVER['HTTP_HOST'];?>"+serverUrl;

function redirectToRoot()
{
	window.location.href=serverFullUrl;
}

function formToData(form)
{
	var data_raw = form.serializeArray();

	var data = new Object();

	for(var i=0 ; i<data_raw.length ; i++)
	{
		data[data_raw[i]['name']] = data_raw[i]['value'];
	}

	if (typeof CryptoJS != 'undefined')
	{
		form.find('.sha').val('true');
		data['digest'] = ""+CryptoJS.SHA256(data['password']);
		data['sha'] = 'true';
		delete data['password'];
	}

	return data;
}

function createAccountFormToData(form)
{
	var data = formToData(form);

	if(data['sha']=='true')
	{
		delete data['passwordConfirm'];
	}

	return data;
}


var wasCanceled = false;

function handleFailure(data)
{
	if(wasCanceled)
	{
		wasCanceled = false;
		return;
	}
	alert('Impossible de communiquer avec le serveur. Vérifiez votre connection internet ou réessayez plus tard.');
}


/*
 * Placeholders
 */

//This adds 'placeholder' to the items listed in the jQuery .support object.
jQuery(function() {
   jQuery.support.placeholder = false;
   test = document.createElement('input');
   if('placeholder' in test) jQuery.support.placeholder = true;
});
// This adds placeholder support to browsers that wouldn't otherwise support it.
jQuery(function() {
   if(!jQuery.support.placeholder) {
      var active = document.activeElement;
      jQuery(':text').focus(function () {
         if (jQuery(this).attr('placeholder') != '' && jQuery(this).val() == jQuery(this).attr('placeholder')) {
            jQuery(this).val('').removeClass('hasPlaceholder');
         }
      }).blur(function () {
         if (jQuery(this).attr('placeholder') != '' && (jQuery(this).val() == '' || jQuery(this).val() == jQuery(this).attr('placeholder'))) {
            jQuery(this).val(jQuery(this).attr('placeholder')).addClass('hasPlaceholder');
         }
      });
      jQuery(':text').blur();
      jQuery(active).focus();
      jQuery('form:eq(0)').submit(function () {
         jQuery(':text.hasPlaceholder').val('');
      });
   }
});

function goToPage(path)
{
	window.location.href = serverFullUrl + path;
}