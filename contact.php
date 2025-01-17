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
function contact_validate() {
	global $smarty, $contactform_inputs, $lang;

	$lerr = &$lang ['contact'] ['error'];

	$r = true;

	$name = trim(htmlspecialchars(@$_POST ['name']));
	$email = isset($_POST ['email']) ? trim(htmlspecialchars($_POST ['email'])) : null;
	$url = isset($_POST ['url']) ? trim(stripslashes(htmlspecialchars($_POST ['url']))) : null;
	$content = isset($_POST ['content']) ? trim(addslashes($_POST ['content'])) : null;

	$errors = array();

	// if the request does not contain all input fields, it might be forged
	foreach ($contactform_inputs as $input) {
		if (!array_key_exists($input, $_POST)) {
			return false;
		}
	}

	// check name
	if (empty($name)) {
		$errors ['name'] = $lerr ['name'];
	}

	// check email
	if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors ['email'] = $lerr ['email'];
	}

	// add https to url if not given and check url
	if (!empty($url) && strpos($url, 'http://') === false && strpos($url, 'https://') === false) {
		$url = 'https://' . $url;
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			$errors ['url'] = $lerr ['www'];
		}
	}

	// check content
	if (empty($content)) {
		$errors ['content'] = $lerr ['content'];
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

	// check aaspam if active
	if (apply_filters('comment_validate', true, $arr)) {
		return $arr;
	} else {
		return false;
	}
}

function contactform() {
	global $smarty, $lang, $fp_config, $contactform_inputs;

	// initial call of the contact form
	if (empty($_POST)) {
		$smarty->assign('success', system_geterr('contact'));
		$smarty->assignByRef('panelstrings', $lang ['contact']);
		return;
	}

	// new form, we (re)set the session data
	utils_nocache_headers();

	$validationResult = contact_validate();

	// if validation failed
	if ($validationResult === false) {
		// assign given input values to the template, so they're prefilled again
		$smarty->assign('values', $_POST);
		return;
	}

	// okay, validation returned validated values
	// now build the mail content
	$msg = $lang ['contact'] ['notification'] ['name'] . " \n" . $validationResult ['name'] . "\n\n";

	if (isset($validationResult ['email'])) {
		$msg .= $lang ['contact'] ['notification'] ['email'] . " \n" . $validationResult ['email'] . "\n\n";
	}
	if (isset($validationResult ['url'])) {
		$msg .= $lang ['contact'] ['notification'] ['www'] . " \n" . $validationResult ['url'] . "\n\n";
	}
	$msg .= $lang ['contact'] ['notification'] ['content'] . " \n" . $validationResult ['content'] . "\n";

	// send notification mail to site admin
	// for non-ASCII characters in the e-mail header use RFC 1342 — Encodes $subject with MIME base64 via core.utils.php
	$success = @utils_mail((isset($validationResult ['email']) ? $validationResult ['email'] : $fp_config ['general'] ['email']), $lang ['contact'] ['notification'] ['subject'] . ' ' . $fp_config ['general'] ['title'], $msg);

	// Assign success or error message directly
	$smarty->assign('success', $success ? 1 : -1);

	// Store the result in the session for further use after the redirect
	system_seterr('contact', $success ? 1 : -1);

	// Redirect to the same page to prevent double submission
	utils_redirect(basename(__FILE__), true);
	exit();
}

function contact_main() {
	global $smarty;

	// register all Smarty modifier functions used by the templates
	register_smartyplugins();

	$lang = lang_load('contact');

	$smarty->assign('subject', $lang ['contact'] ['head']);
	$smarty->assign('content', 'shared:contact.tpl');
	contactform();
}

function register_smartyplugins() {
	global $smarty;
	$functionsToRegister = array(
		// FlatPress functions
		'stripslashes',
		'wp_specialchars',
		// PHP functions
		'function_exists',
		'is_numeric'
	);
	foreach ($functionsToRegister as $functionToRegister) {
		$smarty->registerPlugin('modifier', $functionToRegister, $functionToRegister);
	}
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
?>
