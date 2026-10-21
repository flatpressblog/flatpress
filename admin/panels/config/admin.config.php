<?php

class admin_config extends AdminPanel {
	var $panelname = 'config';
}

class admin_config_default extends AdminPanelActionValidated {

	var $validators = array(
		array(
			'www',
			'www',
			'notEmpty',
			false,
			false,
			'trim'
		),
		array(
			'title',
			'title',
			'notEmpty',
			false,
			false,
			'trim'
		),
		array(
			'email',
			'email',
			'isEmail',
			false,
			false,
			'trim'
		),
		array(
			'maxentries',
			'maxentries',
			'isInt',
			false,
			false,
			'trim'
		),
		array(
			'timeoffset',
			'timeoffset',
			'isNumber',
			false,
			false,
			'trim'
		),
		array(
			'timeformat',
			'timeformat',
			'isValidDateOrTimeFormat',
			false,
			false,
			'trim'
		),
		array(
			'dateformat',
			'dateformat',
			'isValidDateOrTimeFormat',
			false,
			false,
			'trim'
		),
		array(
			'dateformatshort',
			'dateformatshort',
			'isValidDateOrTimeFormat',
			false,
			false,
			'trim'
		),
		array(
			'lang',
			'lang',
			'notEmpty',
			false,
			false,
			'trim'
		),
		array(
			'charset',
			'charset',
			'notEmpty',
			false,
			false,
			'trim'
		),
		array(
			'admin',
			'admin',
			'isValidAdminName',
			false,
			false,
			'trim'
		)
	);

	var $events = array('save');

	function setup() {
		global $fp_config;

		$this->smarty->assign('themes', theme_list());
		$this->smarty->assign('lang_list', lang_list());

		// Load charset options based on the selected language
		$charset_list = $this->getCharsetList($fp_config ['locale'] ['lang']);
		$this->smarty->assign('charset_list', $charset_list);

		$static_list = [];
		foreach (static_getlist() as $id) {
			$static_list [$id] = static_parse($id);
		}
		$this->smarty->assign('static_list', $static_list);

		// Determining the logged-in user
		$user = isset($_SESSION ['userid']) ? $_SESSION ['userid'] : null;

		// Set default value for $flatpress.admin
		if (empty($fp_config ['general'] ['admin']) && $user) {
			$fp_config ['general'] ['admin'] = $user;
		}

		// Transfer logged-in user to the template
		$this->smarty->assign('user', $user);

		// Dynamically add password validators only if necessary
		if (!empty($_POST ['password']) || !empty($_POST ['confirm_password'])) {
			$this->validators[] = array(
				'password',
				'password',
				'isValidAdminPassword',
				false,
				false,
				'strval'
			);
			$this->validators[] = array(
				'confirm_password',
				'confirm_password',
				'isValidAdminPassword',
				false,
				false,
				'strval'
			);
		}
	}

	// Function for returning the charset list based on the selected language
	private function getCharsetList($lang) {
		$langConfFile = LANG_DIR . $lang . '/lang.conf.php';
		if (file_exists($langConfFile)) {
			@include $langConfFile;
			return isset($langconf ['charsets']) ? $langconf ['charsets'] : ['utf-8'];
		}
		// Fallback to utf-8 if no charsets are defined
		return ['utf-8'];
	}

	function onsave() {
		global $fp_config, $lang;

		$success = null;
		$originalConfig = $fp_config;

		// Sanitize all input data
		$localeCharset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');
		$postData = array_map(function ($value) use ($localeCharset) {
			return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, $localeCharset) : '';
		}, $_POST);
		// Passwords are opaque bytes; output escaping must never change the hash input.
		$password = isset($_POST ['password']) && is_string($_POST ['password']) ? $_POST ['password'] : '';
		$confirmation = isset($_POST ['confirm_password']) && is_string($_POST ['confirm_password']) ? $_POST ['confirm_password'] : '';
		$passwordChanged = $password !== '' || $confirmation !== '';
		if ($passwordChanged && ($password !== $confirmation || strlen($password) < 6 || strpos($password, "\0") !== false || preg_match('/\s/', $password) !== 0)) {
			$key = $password !== $confirmation ? 'confirm_password' : 'password';
			$this->smarty->assign('error', [$key => $lang ['admin'] ['config'] ['default'] ['error'] [$key]]);
			return $this->onerror();
		}
		$current_user = $_SESSION ['userid'] ?? null;
		$admin = is_string($current_user) ? user_get($current_user) : null;
		if (!is_array($admin) || !isset($postData ['admin']) || !is_valid_userid($postData ['admin'])) {
			$this->smarty->assign('error', ['admin' => $lang ['admin'] ['config'] ['default'] ['error'] ['account_save']]);
			return $this->onerror();
		}

		// Load list of valid charsets for the current language
		$validCharsets = $this->getCharsetList($postData ['lang']);

		// Check whether the selected charset is valid
		if (!in_array($postData ['charset'], $validCharsets)) {
			$this->smarty->assign('error', ['charset' => 'Invalid charset selected']);
			return $this->onerror();
		}

		// Update and save the configuration
		$trustForwardedScheme = function_exists('fp_should_trust_forwarded_scheme_for_url') ? fp_should_trust_forwarded_scheme_for_url($postData ['www']) : !empty($fp_config ['general'] ['trust_forwarded_scheme']);

		$fp_config ['general'] = array(
			'www' => $postData ['www'],
			'trust_forwarded_scheme' => $trustForwardedScheme,
			'title' => wp_specialchars(stripslashes($postData ['title'])),
			'subtitle' => wp_specialchars(stripslashes($postData ['subtitle'])),
			'footer' => wp_specialchars(stripslashes($postData ['blogfooter'])),
			'author' => wp_specialchars($postData ['author']),
			'email' => wp_specialchars($postData ['email']),
			'startpage' => ($postData ['startpage'] == ':NULL:') ? null : $postData ['startpage'],
			'maxentries' => (int)$postData ['maxentries'],
			'notify' => isset($postData ['notify']),
			'theme' => $fp_config ['general'] ['theme'],
			'style' => @$fp_config ['general'] ['style'],
			'blogid' => $fp_config ['general'] ['blogid'],
			'charset' => $postData ['charset']
		);

		$fp_config ['locale'] = array(
			'timeoffset' => (float)$postData ['timeoffset'],
			'timeformat' => $postData ['timeformat'],
			'dateformat' => $postData ['dateformat'],
			'dateformatshort' => $postData ['dateformatshort'],
			'charset' => $postData ['charset'],
			'lang' => $postData ['lang']
		);

		$renamed = $current_user !== $postData ['admin'];
		$admin ['userid'] = $postData ['admin'];
		if ($passwordChanged) {
			$admin ['password'] = user_pwd($password);
		}
		$admin ['www'] = $postData ['www'];
		$admin ['email'] = $postData ['email'];
		$config_saved = false;
		$error = user_update($current_user, $admin, function () use (&$config_saved) {
			$config_saved = config_save();
			return $config_saved;
		});
		if ($error !== null) {
			$fp_config = $originalConfig;
			if ($config_saved) {
				config_save();
			}
			$this->smarty->assign('error', ['admin' => $lang ['admin'] ['config'] ['default'] ['error'] [$error]]);
			return $this->onerror();
		}
		$_SESSION ['userid'] = $admin ['userid'];
		$_SESSION ['auth_fingerprint'] = hash('sha256', $admin ['password']);
		$success = $renamed ? 2 : 1;

		if ($config_saved && function_exists('fp_setup_migration_mode') && fp_setup_migration_mode()) {
			if (function_exists('fp_setup_migration_write_lockfile') && !fp_setup_migration_write_lockfile()) {
				$success = -1;
			}
		}

		// Re-assign values directly to Smarty template to reflect changes
		$this->setup();
		$this->smarty->assign('success', $success);

		// If set the new Admin and delete the current Admin, delay the logout
		if ($success === 2) {
			$this->delay_logout(5);
		}

		// Call main() to render updated config without reload
		return $this->main();
	}

	function onerror() {
		$this->main();
		return 0;
	}

	function delay_logout($delay_seconds = 5) {
		user_logout();
		cookie_clear();
		header('Refresh: ' . $delay_seconds . '; URL=' . BLOG_BASEURL . 'login.php?do=logout');
		//exit;
	}

	// if theme was switched, clear tpl cache
	function cleartplcache() {
		$tpl = new tpl_deleter();
		$_ = $tpl->getList();
	}
}
?>
