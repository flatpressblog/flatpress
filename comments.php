<?php
if (!defined('MOD_INDEX')) {
	include 'defaults.php';
	include INCLUDES_DIR . 'includes.php';

	/* backward compatibility */

	if (!@$_GET ['entry']) {
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

	/*
	 * $lang['comments']['error'] = array(
	 * 'name' => 'You must enter a name',
	 * 'email' => 'You must enter a valid email',
	 * 'www' => 'You must enter a valid URL',
	 * 'comment' => 'You must enter a comment',
	 * );
	 */

	$content = isset($_POST ['content']) ? trim(addslashes($_POST ['content'])) : null;

	$errors = array();

	$loggedin = false;

	if (user_loggedin()) {
		$user = user_get();
		$loggedin = $arr ['loggedin'] = true;
		$email = $user ['email'] ?? '';
		$url = $user ['www'] ?? '';
		$name = $user ['userid'] ?? '';
	} else {
		$name = trim(htmlspecialchars(@$_POST ['name'] ?? ''));
		$email = isset($_POST ['email']) ? trim(htmlspecialchars($_POST ['email'])) : '';
		$url = isset($_POST ['url']) ? trim(stripslashes(htmlspecialchars($_POST ['url']))) : '';

		/*
		 * check name
		 *
		 */
		if (empty($name)) {
			$errors ['name'] = $lerr ['name'];
		}

		/*
		 * check email
		 *
		 */
		if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors ['email'] = $lerr ['email'];
		}

		/*
		 * add https to url if not given and check url
		 *
		 */
		if (!empty($url) && strpos($url, 'http://') === false && strpos($url, 'https://') === false) {
			$url = 'https://' . $url;
			if (!filter_var($url, FILTER_VALIDATE_URL)) {
				$errors ['url'] = $lerr ['www'];
			}
		}
	}

	// check content
	if (empty($content)) {
		$errors ['content'] = $lerr ['comment'];
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

	if ($loggedin || apply_filters('comment_validate', true, $arr)) {
		return $arr;
	} else {
		return false;
	}
}

function commentform() {
	global $smarty, $lang, $fpdb, $fp_params;

	// Ensure session is started
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	if (empty($_SESSION ['csrf_token'])) {
		// Generate CSRF token
		$_SESSION ['csrf_token'] = RANDOM_HEX;
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

		// CSRF token verification
		if (!isset($_POST ['csrf_token']) || $_POST ['csrf_token'] !== $_SESSION ['csrf_token']) {
			return;
		}

		// Reset CSRF token after validation and generate a new one
		unset($_SESSION ['csrf_token']);
		$_SESSION ['csrf_token'] = RANDOM_HEX;
		$smarty->assign('csrf_token', $_SESSION ['csrf_token']);

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
