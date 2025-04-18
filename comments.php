<?php
if (!defined('MOD_INDEX')) {
	include 'defaults.php';
	include INCLUDES_DIR . 'includes.php';

	/* backward compatibility */

	if (empty($_GET ['entry'])) {
		@utils_redirect();
	} else {
		@utils_status_header(301);
		@utils_redirect(str_replace('&amp;', '&', get_comments_link($_GET ['entry'])), true);
	}
}

$module = comment_main($module);

function comment_main($module) {
	global $fpdb, $fp_params, $smarty, $fp_config, $current_entry;

	// register Smarty modifier function
	$smarty->registerPlugin('modifier', 'is_numeric', 'is_numeric');
	$smarty->registerPlugin('modifier', 'theme_comments_feed_link', 'theme_comments_feed_link');
	if (!isset($smarty->registered_plugins['modifier']['fix_encoding_issues'])) {
		// This modifier converts characters such as Ã¤ to ä or &#8220; to “. See core.language.php
		$smarty->registerPlugin('modifier', 'fix_encoding_issues', 'fix_encoding_issues');
	}

	// hackish solution to get title before fullparse starts dunno, I don't like it

	$q = & $fpdb->getQuery();

	list ($id, $entry) = @$q->peekEntry();
	if (empty($entry)) {
		return $module;
	}

	$current_entry = $entry;

	if (!empty($fp_params ['feed'])) {

		switch ($fp_params ['feed']) {

			case 'atom':
				$charset = strtoupper($fp_config ['locale'] ['charset']);
				header('Content-Type: application/atom+xml; charset=' . $charset);
				$module = SHARED_TPLS . 'comment-atom.tpl';
				break;
			case 'rss2':
			default:
				$charset = strtoupper($fp_config ['locale'] ['charset']);
				header('Content-Type: application/rss+xml; charset=' . $charset);
				$module = SHARED_TPLS . 'comment-rss.tpl';
		}
	} elseif (!in_array('commslock', $entry ['categories'])) {

		commentform();
	}

	return $module;
}

function comment_feed() {
	global $fp_params, $fp_config, $lang, $current_entry;

	if (isset($current_entry ['subject']) && !empty($current_entry ['subject'])) {
		$entry_title = $current_entry ['subject'];
	} elseif (isset($fp_config ['404error'] ['subject']) && !empty($fp_config ['404error'] ['subject'])) {
		$entry_title = $fp_config ['404error'] ['subject'];
	} else {
		$entry_title = 'Undefined Title';
	}

	echo "\n" . '<link rel="alternate" type="application/rss+xml" title="' . $entry_title . ' » ' . $lang ['main'] ['comments'] . ' | RSS 2.0" href="' . theme_comments_feed_link('rss2', $fp_params ['entry']) . '">';
	echo "\n" . '<link rel="alternate" type="application/atom+xml" title="' . $entry_title . ' » ' . $lang ['main'] ['comments'] . ' | Atom 1.0" href="' . theme_comments_feed_link('atom', $fp_params ['entry']) . '">' . "\n";
}
add_action('wp_head', 'comment_feed');

function comment_pagetitle($val, $sep) {
	global $fpdb, $lang;
	$q = & $fpdb->getQuery();
	list ($id, $e) = @$q->peekEntry();
	if (!empty($e)) {
		return $e ['subject'] . ' : ' . $lang ['main'] ['comments'] . ' ' . $sep . ' ' . $val . ' ';
	} else {
		return $val;
	}
}
remove_filter('wp_title', 'index_permatitle');
add_filter('wp_title', 'comment_pagetitle', 10, 2);

function comment_validate() {
	global $smarty, $lang;

	$lerr = & $lang ['comments'] ['error'];

	$r = true;

	/**
	 * $lang['comments']['error'] = array(
	 * 'name' => 'You must enter a name',
	 * 'email' => 'You must enter a valid email',
	 * 'www' => 'You must enter a valid URL',
	 * 'comment' => 'You must enter a comment',
	 * );
	 */

	$content = isset($_POST ['content']) ? trim(sanitize_comment($_POST ['content'], FILTER_UNSAFE_RAW)) : '';

	$errors = array();

	$loggedin = false;

	if (user_loggedin()) {
		$user = user_get();
		$loggedin = $arr ['loggedin'] = true;
		$email = $user ['email'] ?? '';
		$url = $user ['www'] ?? '';
		$name = $user ['userid'] ?? '';
	} else {
		$name = strip_tags(sanitize_comment($_POST ['name'] ?? ''));
		$email = strip_tags(sanitize_comment($_POST ['email'] ?? '', FILTER_VALIDATE_EMAIL));
		$url = strip_tags(sanitize_comment(ensure_https_url($_POST ['url'] ?? ''), FILTER_SANITIZE_URL));

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
	}

	// Check content
	if (empty($content)) {
		$errors ['content'] = $lerr ['comment'];
	}

	// Assign error messages to template
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

	if ($loggedin || apply_filters('comment_validate', true, $arr)) {
		return $arr;
	} else {
		return false;
	}
}

function sanitize_comment($data, $filter = FILTER_UNSAFE_RAW) {
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

function commentform() {
	global $smarty, $lang, $fpdb, $fp_params;

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
			utils_redirect('comments.php');
			exit();
		}

		unset($_SESSION ['csrf_token']);
		$_SESSION ['csrf_token'] = bin2hex(random_bytes(32));
	}

	$comment_formid = 'fp-comments';
	$smarty->assign('comment_formid', $comment_formid);

	// Transfer token to Smarty
	$smarty->assign('csrf_token', $_SESSION ['csrf_token']);

	// Define panel strings for success or error message
	$panelstrings = array(
		'msgs' => array(
			1 => $lang ['comments'] ['success'],
			-1 => $lang ['comments'] ['error'],
		),
	);
	$smarty->assign('panelstrings', $panelstrings);

	if (!empty($_POST)) {

		// New form, we (re)set the session data
		utils_nocache_headers();

		// Custom hook here!!
		if ($arr = comment_validate()) {
			global $fp_config;

			$id = comment_save($fp_params ['entry'], $arr);
			do_action('comment_post', $fp_params ['entry'], array(
				$id,
				$arr
			));

			$q = new FPDB_Query(array(
				'id' => $fp_params ['entry'],
				'fullparse' => false
			), null);
			list ($entryid, $e) = $q->getEntry();

			if ($fp_config ['general'] ['notify'] && !user_loggedin()) {
				global $post;
				$comm_mail = isset($arr ['email']) ? '<' . $arr ['email'] . '>' : '';
				$from_mail = $fp_config ['general'] ['email'];

				// Plugin such as prettyurls might need this...
				$post = $e;

				$lang = lang_load('comments');

				$mail = str_replace(array(
					'%toname%',
					'%fromname%',
					'%frommail%',
					'%entrytitle%',
					'%commentlink%',
					'%content%',
					'%blogtitle%'
				), array(
					$fp_config ['general'] ['author'],
					$arr ['name'],
					$comm_mail,
					$e ['subject'],
					get_comments_link($entryid) . '#' . $id,
					$arr ['content'],
					$fp_config ['general'] ['title']
				), $lang ['comments'] ['mail']);

				// For non-ASCII characters in the e-mail header use RFC 1342 — Encodes $subject with MIME base64 via core.utils.php
				@utils_mail($from_mail, $lang ['comments'] ['newcomment'] . ' ' . $fp_config ['general'] ['title'], $mail);
			}

			// Set success message for the current request
			$smarty->assign('success', 1);

			// Save success status in session for use after redirect
			system_seterr('comment', 1);

			// If comment is valid, this redirect will clean the postdata
			$location = str_replace('&amp;', '&', get_comments_link($entryid)) . '#' . $id;
			utils_redirect($location, true);
			exit();

		} else {
			$smarty->assign('values', $_POST);
		}

	} else {
		// Check for any previous success or error state
		$success = system_geterr('comment');
		if ($success === 1) {
			$smarty->assign('success', 1);
		}
	}
}

?>
