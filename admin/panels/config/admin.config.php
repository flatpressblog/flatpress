<?php

class admin_config extends AdminPanel {
	var $panelname = 'config';
}

class admin_config_default extends AdminPanelActionValidated {

	var $validators = array(
		// not needed anymore !
		// array('blog_root', 'blog_root', 'notEmpty', false, false, 'trim'),
		array(
			'www',
			'www',
			'notEmpty',
			false,
			false,
			'trim'
		),
		// ...
		array(
			'title',
			'title',
			'notEmpty',
			false,
			false,
			'trim'
		),
		// array('subtitle', 'subtitle', 'notEmpty', false, false, 'trim'),
		// array('blogfooter', 'blogfooter', 'notEmpty', false, false, 'trim'),
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
			'notEmpty',
			false,
			false,
			'trim'
		),
		array(
			'dateformat',
			'dateformat',
			'notEmpty',
			false,
			false,
			'trim'
		),
		array(
			'dateformatshort',
			'dateformatshort',
			'notEmpty',
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
		)
	);

	var $events = array(
		'save'
	);

	function setup() {
		global $fp_config;

		$this->smarty->assign('themes', theme_list());
		$this->smarty->assign('lang_list', lang_list());

		// Charset options depending on the selected language
		$charset_list = $this->getCharsetList($fp_config ['locale'] ['lang']);
		$this->smarty->assign('charset_list', $charset_list);

		$static_list = array();

		foreach (static_getlist() as $id) {
			$static_list [$id] = static_parse($id);
		}

		$this->smarty->assign('static_list', $static_list);
	}

	// Function for returning the charset list based on the selected language
	private function getCharsetList($lang) {
		$langConfFile = LANG_DIR . $lang . '/lang.conf.php';
		if (file_exists($langConfFile)) {
			include $langConfFile;
			return isset($langconf ['charsets']) ? $langconf ['charsets'] : array();
		}
		// Fallback to utf-8 if no charsets are defined
		return array('utf-8');
	}

	function onsave() {
		global $fp_config;

		// Load list of valid charsets for the current language
		$validCharsets = $this->getCharsetList($_POST ['lang']);

		// Check whether the selected charset is valid
		if (!in_array($_POST ['charset'], $validCharsets)) {
			// Error case - invalid charset
			$this->smarty->assign('error', array('charset' => 'Invalid charset selected'));
			return $this->onerror();
		}

		// Save configuration
		$fp_config ['general'] = array(
			// 'BLOG_ROOT' => $_POST['blog_root'],
			'www' => $_POST ['www'],
			'title' => wp_specialchars(stripslashes($_POST ['title'])),
			'subtitle' => wp_specialchars(stripslashes($_POST ['subtitle'])),
			'footer' => wp_specialchars(stripslashes($_POST ['blogfooter'])),
			'author' => wp_specialchars($_POST ['author']),
			'email' => wp_specialchars($_POST ['email']),
			'startpage' => ($_POST ['startpage'] == ':NULL:') ? null : $_POST ['startpage'],
			'maxentries' => $_POST ['maxentries'],
			// 'voting' => $_POST['voting'],
			'notify' => isset($_POST ['notify']),
			// preserve the following
			'theme' => $fp_config ['general'] ['theme'],
			'style' => @$fp_config ['general'] ['style'],
			'blogid' => $fp_config ['general'] ['blogid'],
			'charset' => $_POST ['charset'],
			'noremoteip' => isset($_POST ['noremoteip'])
		);

		$fp_config ['locale'] = array(
			'timeoffset' => $_POST ['timeoffset'],
			'timeformat' => $_POST ['timeformat'],
			'dateformat' => $_POST ['dateformat'],
			'dateformatshort' => $_POST ['dateformatshort'],
			'charset' => $_POST ['charset'],
			'lang' => $_POST ['lang']
		);

		$success = config_save() ? 1 : -1;

		$this->smarty->assign('success', $success);

		return 1;
	}

	function onerror() {
		$this->main();
		return 0;
	}

	function cleartplcache() {
		// if theme was switched, clear tpl cache
		$tpl = new tpl_deleter();

		$tpl->getList();
	}

}
?>
