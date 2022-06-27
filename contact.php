<?php
require_once 'defaults.php';
require_once INCLUDES_DIR . 'includes.php';

// contact form fields
$contactform_inputs = array(
	'name',
	'email',
	'url',
	'content'
);

/**
 * Validates the POST data and returns a validated array (key=>value) - or <code>false</code> if validation failed
 *
 * @return boolean|array
 */
function contact_form_validate() {
	global $smarty, $contactform_inputs, $lang;

	// if the request does not contain all input fields, it might be forged
	foreach ($contactform_inputs as $input) {
		if (!array_key_exists($input, $_POST)) {
			return false;
		}
	}

	$errors = array();

	$name = trim(htmlspecialchars($_POST ['name']));
	$email = trim(htmlspecialchars($_POST ['email']));
	$url = trim(stripslashes(htmlspecialchars($_POST ['url'])));
	$content = trim(addslashes($_POST ['content']));

	// check name
	if (empty($name)) {
		$errors ['name'] = $lang ['contact'] ['error'] ['name'];
	}

	// check email
	if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors ['email'] = $lang ['contact'] ['error'] ['email'];
	}

	// check url
	if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
		$errors ['url'] = $lang ['contact'] ['error'] ['www'];
	}

	// check content
	if (empty($content)) {
		$errors ['content'] = $lang ['contact'] ['error'] ['content'];
	}

	// assign error messages to template
	if (!empty($errors)) {
		$smarty->assign('error', $errors);
		return false;
	}

	$arr ['version'] = system_ver();
	$arr ['name'] = $name;

	if (!empty($email)) {
		($arr ['email'] = $email);
	}
	if (!empty($url)) {
		($arr ['url'] = ($url));
	}
	$arr ['content'] = $content;

	if ($v = utils_ipget()) {
		$arr ['ip-address'] = $v;
	}

	return $arr;
}

function contact_form() {
	global $smarty, $lang, $fp_config, $contactform_inputs;

	// initial call of the contact form
	if (empty($_POST)) {
		$smarty->assign('success', system_geterr('contact'));
		$smarty->assign_by_ref('panelstrings', $lang ['contact']);
		return;
	}

	// new form, we (re)set the session data
	utils_nocache_headers();

	$validationResult = contact_form_validate();

	// if validation failed
	if ($validationResult === false) {
		// assign given input values to the template, so they're prefilled again
		$smarty->assign('values', $_POST);
		return;
	}

	// okay, validation returned validated values
	// now build the mail content
	$msg = "Name: \n{$validationResult['name']} \n\n";

	if (isset($validationResult ['email'])) {
		$msg .= "Email: {$validationResult['email']}\n\n";
	}
	if (isset($validationResult ['url'])) {
		$msg .= "WWW: {$validationResult['url']}\n\n";
	}
	$msg .= "Content:\n{$validationResult['content']}\n";

	// send notification mail to site admin
	$success = @utils_mail((isset($validationResult ['email']) ? $validationResult ['email'] : $fp_config ['general'] ['email']), "Contact sent through {$fp_config['general']['title']} ", $msg);
	system_seterr('contact', $success ? 1 : -1);
	utils_redirect(basename(__FILE__));
}

function contact_main() {
	global $smarty;

	$lang = lang_load('contact');

	$smarty->assign('subject', $lang ['contact'] ['head']);
	$smarty->assign('content', 'shared:contact.tpl');
	contact_form();
}

function contact_display() {
	global $smarty;

	contact_main();

	theme_init($smarty);

	$smarty->display('default.tpl');

	unset($smarty);

	do_action('shutdown');
}

system_init();
contact_display();