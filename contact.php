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
	global $smarty, $contactform_inputs, $fp_config, $lang;

	$lerr = &$lang ['contact'] ['error'];

	$r = true;

	$name = strip_tags(sanitize_contact($_POST ['name'] ?? ''));
	$email = strip_tags(sanitize_contact($_POST ['email'] ?? '', FILTER_VALIDATE_EMAIL));
	$url = strip_tags(sanitize_contact(ensure_https_url($_POST ['url'] ?? ''), FILTER_SANITIZE_URL));
	$content = isset($_POST ['content']) ? sanitize_contact($_POST ['content']) : '';

	$errors = array();

	// If the request does not contain all input fields, it might be forged
	foreach ($contactform_inputs as $input) {
		if (!array_key_exists($input, $_POST)) {
			return false;
		}
	}

	// Check name
	if (empty($name)) {
		$errors ['name'] = $lerr ['name'];
	}

	// Check email
	if (!empty($_POST ['email']) && !$email) {
		$errors ['email'] = $lerr ['email'];
	}

	// Check url
	if (!empty($_POST ['url']) && !$url) {
		$errors ['url'] = $lerr ['www'];
	}

	// Check content
	if (empty($content)) {
		$errors ['content'] = $lerr ['content'];
	}

	// Assign error messages to template
	if (!empty($errors)) {
		$smarty->assign('error', $errors);
		return false;
	}

	$arr = [
		'version' => system_ver(),
		'name' => $name,
		'email' => $email ? : null,
		'url' => $url ? : null,
		'content' => $content,
		'ip-address' => utils_ipget() ? : null,
	];

	// Check aaspam if active
	if (apply_filters('comment_validate', true, $arr)) {
		return $arr;
	} else {
		return false;
	}
}

function sanitize_contact($data, $filter = FILTER_UNSAFE_RAW) {
	global $fp_config;
	$charset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');

	$data = trim($data);
	$data = filter_var($data, $filter) ? : '';

	// Removes dangerous characters for flat file manipulation and potential code injections
	$unsafe_patterns = [
		"<?", "?>", ";", "eval", "base64_decode", "base64_encode",
		"system", "exec", "shell_exec", "proc_open", "passthru", "popen",
		"curl_exec", "curl_multi_exec", "fopen", "fwrite", "file_put_contents"
	];
	$data = str_replace($unsafe_patterns, "", $data);

	// Block potential SQL injection patterns
	$sql_patterns = [
		'/\bSELECT\b/i', '/\bINSERT\b/i', '/\bUPDATE\b/i', '/\bDELETE\b/i', '/\bDROP\b/i',
		'/\bUNION\b/i', '/\bALTER\b/i', '/\bEXEC\b/i', '/\bOR\b\s*\d+=\d+/i', '/\bAND\b\s*\d+=\d+/i',
		'/--/', '/;/', '/\bNULL\b/i', '/\bTRUE\b/i', '/\bFALSE\b/i', '/\bINFORMATION_SCHEMA\b/i',
		'/\bTABLE_SCHEMA\b/i', '/\bCONCAT\b/i', '/\bSLEEP\b/i', '/\bBENCHMARK\b/i'
	];
	$data = preg_replace($sql_patterns, '', $data) ? : '';

	// Check for potentially dangerous flat file characters
	if (preg_match('/\|{2,}|::{2,}|;;/', $data)) {
		return '';
	}

	return htmlspecialchars($data, ENT_NOQUOTES, $charset);
}

// Add https to url if not given
function ensure_https_url($url) {
	$url = trim(stripslashes($url));
	if (!empty($url) && !preg_match('/^https?:\/\//i', $url)) {
		$url = 'https://' . $url;
	}
	return (filter_var($url, FILTER_VALIDATE_URL) && !preg_match('/^(file|php|data|ftp):/i', $url)) ? $url : '';
}

function contactform() {
	global $smarty, $lang, $fp_config, $contactform_inputs;

	// Ensure session is started
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	if (empty($_SESSION ['csrf_token'])) {
		// Generate CSRF token
		$_SESSION ['csrf_token'] = bin2hex(random_bytes(32));
	}

	// Check CSRF protection
	$request_method = $_SERVER ['REQUEST_METHOD'] ?? '';
	$is_post_request = ($request_method === 'POST');

	if (!$is_post_request && isset($_SERVER ['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
		$override_method = strtoupper(trim($_SERVER ['HTTP_X_HTTP_METHOD_OVERRIDE']));
		if ($override_method === 'POST') {
			$is_post_request = true;
		}
	}

	if ($is_post_request) {
		if (!isset($_POST ['csrf_token']) || $_POST ['csrf_token'] !== ($_SESSION ['csrf_token'] ?? '')) {
			// Renew CSRF token to prevent replay attacks
			unset($_SESSION ['csrf_token']);
			$_SESSION ['csrf_token'] = bin2hex(random_bytes(32));
			utils_redirect('contact.php');
			exit();
		}

		unset($_SESSION ['csrf_token']);
		$_SESSION ['csrf_token'] = bin2hex(random_bytes(32));
	}

	// Transfer token to Smarty
	$smarty->assign('csrf_token', $_SESSION ['csrf_token']);

	// Initial call of the contact form
	if (empty($_POST)) {
		$smarty->assign('success', system_geterr('contact'));
		$smarty->assign('panelstrings', $lang ['contact']);
		return;
	}

	// New form, we (re)set the session data
	utils_nocache_headers();

	$validationResult = contact_validate();

	// If validation failed
	if ($validationResult === false) {
		// assign given input values to the template, so they're prefilled again
		$smarty->assign('values', $_POST);
		return;
	}

	// Okay, validation returned validated values
	// now build the mail content
	$msg = $lang ['contact'] ['notification'] ['name'] . " \n" . $validationResult ['name'] . "\n\n";

	if (isset($validationResult ['email'])) {
		$msg .= $lang ['contact'] ['notification'] ['email'] . " \n" . $validationResult ['email'] . "\n\n";
	}
	if (isset($validationResult ['url'])) {
		$msg .= $lang ['contact'] ['notification'] ['www'] . " \n" . $validationResult ['url'] . "\n\n";
	}
	$msg .= $lang ['contact'] ['notification'] ['content'] . " \n" . $validationResult ['content'] . "\n";

	// Send notification mail to site admin
	// For non-ASCII characters in the e-mail header use RFC 1342 — Encodes $subject with MIME base64 via core.utils.php
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
