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
	}

	// Function for escaping HTML output
	private function escapeHTML($value) {
		return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
	}

	// Function for returning the charset list based on the selected language
	private function getCharsetList($lang) {
		$langConfFile = LANG_DIR . $lang . '/lang.conf.php';
		if (file_exists($langConfFile)) {
			include $langConfFile;
			return isset($langconf ['charsets']) ? $langconf ['charsets'] : ['utf-8'];
		}
		// Fallback to utf-8 if no charsets are defined
		return ['utf-8'];
	}

	function onsave() {
		global $fp_config;

		// Load list of valid charsets for the current language
		$validCharsets = $this->getCharsetList($_POST ['lang']);

		// Check whether the selected charset is valid
		if (!in_array($_POST ['charset'], $validCharsets)) {
			// Error case - invalid charset
			$this->smarty->assign('error', ['charset' => 'Invalid charset selected']);
			return $this->onerror();
		}

		// Update and save the configuration
		$fp_config ['general'] = array(
			'www' => $this->escapeHTML($_POST ['www']),
			'title' => $this->escapeHTML(wp_specialchars(stripslashes($_POST ['title']))),
			'subtitle' => $this->escapeHTML(wp_specialchars(stripslashes($_POST ['subtitle']))),
			'footer' => $this->escapeHTML(wp_specialchars(stripslashes($_POST ['blogfooter']))),
			'author' => $this->escapeHTML(wp_specialchars($_POST ['author'])),
			'email' => $this->escapeHTML(wp_specialchars($_POST ['email'])),
			'startpage' => ($_POST ['startpage'] == ':NULL:') ? null : $this->escapeHTML($_POST ['startpage']),
			'maxentries' => (int)$_POST ['maxentries'],
			'notify' => isset($_POST ['notify']),
			'theme' => $fp_config ['general'] ['theme'],
			'style' => @$fp_config ['general'] ['style'],
			'blogid' => $fp_config ['general'] ['blogid'],
			'charset' => $_POST ['charset'],
			'noremoteip' => isset($_POST ['noremoteip'])
		);

		$fp_config ['locale'] = array(
			'timeoffset' => (float)$_POST ['timeoffset'],
			'timeformat' => $this->escapeHTML($_POST ['timeformat']),
			'dateformat' => $this->escapeHTML($_POST ['dateformat']),
			'dateformatshort' => $this->escapeHTML($_POST ['dateformatshort']),
			'charset' => $_POST ['charset'],
			'lang' => $_POST ['lang']
		);

		$success = config_save() ? 1 : -1;

		// Re-assign values directly to Smarty template to reflect changes
		// Ensure the latest config is loaded into the template
		$this->setup();
		$this->smarty->assign('success', $success);

		// Call main() to render updated config without reload
		return $this->main();
	}

	// if theme was switched, clear tpl cache
	function onerror() {
		$this->main();
		return 0;
	}
}
?>
