<?php
/*
 * Plugin Name: Support
 * Description: Show support data. Part of the standard distribution.
 * Version: 1.1.0
 * Plugin URI: https://flatpress.org
 * Author: FlatPress
 * Author URI: https://flatpress.org
 */
require_once ABS_PATH . 'defaults.php';
require_once INCLUDES_DIR . 'includes.php';
require_once CONFIG_DIR . 'plugins.conf.php';

if (class_exists('AdminPanelAction')) {

	function plugin_support_head() {
		$plugindir = plugin_geturl('support');
		echo '
			<!-- BOF support files -->
			<link rel="stylesheet" type="text/css" href="' . $plugindir . 'res/support.css">
			<!-- EOF support files -->';
	}

	class admin_maintain_support extends AdminPanelAction {

		function setup() {
			$this->smarty->assign('admin_resource', 'plugin:support/admin.plugin.support');
		}

		function main() {
 			require CONFIG_DIR . 'plugins.conf.php';
			global $fp_config;

			$BASE_DIR = BASE_DIR;
			$setupfile = BASE_DIR . '/setup.php';

			$LANG_DEFAULT = null;
			$LANG_DEFAULT = LANG_DEFAULT;

			$lang = null;
			$fp_config ['locale'] ['lang'];

			$charset = null;
			$charset = $fp_config ['locale'] ['charset'];

			$theme = null;
			$theme = $fp_config ['general'] ['theme'];

			$style = null;
			$style = $fp_config ['general'] ['style'];

			$BLOG_BASEURL = null;
			$BLOG_BASEURL = $fp_config ['general'] ['www'];

			$lang = lang_load('plugin:support');

			/**
			 * prepare output "Setup"
			 */
			$support ['h2_general'] = $lang ['admin'] ['maintain'] ['support'] ['h2_general'];

			$support ['h3_setup'] = $lang ['admin'] ['maintain'] ['support'] ['h3_setup'];

			if ($LANG_DEFAULT) {
				$support ['LANG_DEFAULT'] = $lang ['admin'] ['maintain'] ['support'] ['pos_LANG_DEFAULT'];
			} else {
				$support ['LANG_DEFAULT'] = $lang ['admin'] ['maintain'] ['support'] ['neg_LANG_DEFAULT'];
			}

			if ($lang) {
				$support ['lang'] = $lang ['admin'] ['maintain'] ['support'] ['pos_lang'];
			} else {
				$support ['lang'] = $lang ['admin'] ['maintain'] ['support'] ['neg_lang'];
			}

			if ($charset) {
				$support ['charset'] = $lang ['admin'] ['maintain'] ['support'] ['pos_charset'];
			} else {
				$support ['charset'] = $lang ['admin'] ['maintain'] ['support'] ['neg_charset'];
			}

			if ($theme) {
				$support ['theme'] = $lang ['admin'] ['maintain'] ['support'] ['pos_theme'];
			} else {
				$support ['theme'] = $lang ['admin'] ['maintain'] ['support'] ['neg_theme'];
			}

			if ($style) {
				$support ['style'] = $lang ['admin'] ['maintain'] ['support'] ['pos_style'];
			} else {
				$support ['style'] = $lang ['admin'] ['maintain'] ['support'] ['neg_style'];
			}

			if ($BLOG_BASEURL) {
				$support ['plugins'] = $lang ['admin'] ['maintain'] ['support'] ['pos_plugins'];
				$support ['output_plugins'] = implode(', ', $fp_plugins);
			} else {
				$support ['plugins'] = $lang ['admin'] ['maintain'] ['support'] ['neg_plugins'];
			}

			/**
			 * prepare output "Core files"
			 */
			$support ['h2_permissions'] = $lang ['admin'] ['maintain'] ['support'] ['h2_permissions'];

			$support ['h3_core_files'] = $lang ['admin'] ['maintain'] ['support'] ['h3_core_files'];

			$support ['desc_setupfile'] = $lang ['admin'] ['maintain'] ['support'] ['desc_setupfile'];
			if (file_exists($setupfile)) {
				$support ['setupfile'] = $lang ['admin'] ['maintain'] ['support'] ['error_setupfile'];
			} else {
				$support ['setupfile'] = $lang ['admin'] ['maintain'] ['support'] ['success_setupfile'];
			}

			$support ['desc_defaultsfile'] = $lang ['admin'] ['maintain'] ['support'] ['desc_defaultsfile'];
			$test_file = @fopen("{$BASE_DIR}/defaults.php", "a+");
			if ($test_file) {
				$support ['defaultsfile'] = $lang ['admin'] ['maintain'] ['support'] ['attention_defaultsfile'];
			} else {
				$support ['defaultsfile'] = $lang ['admin'] ['maintain'] ['support'] ['success_defaultsfile'];
			}
			@fclose($test_file);

			$support ['desc_admindir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_admindir'];
			$test_file = @fopen("{$BASE_DIR}/admin/chmod-test-file", "a+");
			if ($test_file) {
				$support ['admindir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_admindir'];
			} else {
				$support ['admindir'] = $lang ['admin'] ['maintain'] ['support'] ['success_admindir'];
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/admin/chmod-test-file");

			$support ['desc_includesdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_includesdir'];
			$test_file = @fopen("{$BASE_DIR}/fp-includes/chmod-test-file", "a+");
			if ($test_file) {
				$support ['includesdir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_includesdir'];
			} else {
				$support ['includesdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_includesdir'];
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/fp-includes/chmod-test-file");

			/**
			 * prepare output "Configuration file for the webserver"
			 */
			$support ['h3_configwebserver'] = $lang ['admin'] ['maintain'] ['support'] ['h3_configwebserver'];
			$support ['note_configwebserver'] = $lang ['admin'] ['maintain'] ['support'] ['note_configwebserver'];
			$support ['serversoftware'] = $lang ['admin'] ['maintain'] ['support'] ['serversoftware'];

			$test_file = @fopen("{$BASE_DIR}/chmod-test-file", "a+");
			if ($test_file) {
				$support ['maindir'] = $lang ['admin'] ['maintain'] ['support'] ['success_maindir'];
			} else {
				$support ['maindir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_maindir'];
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/chmod-test-file");

			// Do not create a .hthaccess file, otherwise the PrettyURLs plugin cannot create its own file. Better is .htaccess.txt
			$test_file = @fopen("{$BASE_DIR}/.htaccess.txt", "a+");
			if ($test_file) {
				$support ['htaccessw'] = $lang ['admin'] ['maintain'] ['support'] ['success_htaccessw'];
			} else {
				$support ['htaccessw'] = $lang ['admin'] ['maintain'] ['support'] ['attention_htaccessw'];
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/.htaccess.txt");

			$htaccess = BASE_DIR . '/.htaccess';
			if (file_exists($htaccess)) {
				$support ['htaccessn'] = $lang ['admin'] ['maintain'] ['support'] ['attention_htaccessn'];
			} else {
				$support ['htaccessn'] = $lang ['admin'] ['maintain'] ['support'] ['success_htaccessn'];
			}

			/**
			 * prepare output "Themes and plugins"
			 */
			$support ['h3_themesplugins'] = $lang ['admin'] ['maintain'] ['support'] ['h3_themesplugins'];

			$support ['desc_interfacedir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_interfacedir'];
			$test_file = @fopen("{$BASE_DIR}/fp-interface/chmod-test-file", "a+");
			if ($test_file) {
				$support ['interfacedir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_interfacedir'];
			} else {
				$support ['interfacedir'] = $lang ['admin'] ['maintain'] ['support'] ['success_interfacedir'];
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/fp-interface/chmod-test-file");

			$support ['desc_themesdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_themesdir'];
			$test_file = @fopen("{$BASE_DIR}/fp-interface/themes/chmod-test-file", "a+");
			if ($test_file) {
				$support ['themesdir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_themesdir'];
			} else {
				$support ['themesdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_themesdir'];
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/fp-interface/themes/chmod-test-file");

			$support ['desc_plugindir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_plugindir'];
			$test_file = @fopen("{$BASE_DIR}/fp-plugins/chmod-test-file", "a+");
			if ($test_file) {
				$support ['plugindir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_plugindir'];
			} else {
				$support ['plugindir'] = $lang ['admin'] ['maintain'] ['support'] ['success_plugindir'];
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/fp-plugins/chmod-test-file");

			$support ['h3_contentdir'] = $lang ['admin'] ['maintain'] ['support'] ['h3_contentdir'];

			$support ['desc_contentdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_contentdir'];
			$test_file = @fopen("{$BASE_DIR}/fp-content/chmod-test-file", "a+");
			if ($test_file) {
				$support ['contentdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_contentdir'];
			} else {
				$support ['contentdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_contentdir'];
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/fp-content/chmod-test-file");

			$support ['desc_imagesdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_imagesdir'];
			if (file_exists("{$BASE_DIR}/fp-content/images/")) {
				$test_file = @fopen("{$BASE_DIR}/fp-content/images/chmod-test-file", "a+");
				if ($test_file) {
					$support ['imagesdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_imagesdir'];
				} else {
					$support ['imagesdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_imagesdir'];
				}
				@fclose($test_file);
				@unlink("{$BASE_DIR}/fp-content/images/chmod-test-file");
			} else {
				$support ['imagesdir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_imagesdir'];
			}

			$support ['desc_thumbsdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_thumbsdir'];
			if (file_exists("{$BASE_DIR}/fp-content/images/.thumbs")) {
				$test_file = @fopen("{$BASE_DIR}/fp-content/images/.thumbs/chmod-test-file", "a+");
				if ($test_file) {
					$support ['thumbsdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_thumbsdir'];
				} else {
					$support ['thumbsdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_thumbsdir'];
				}
				@fclose($test_file);
				@unlink("{$BASE_DIR}/fp-content/images/chmod-test-file");
			} else {
				$support ['thumbsdir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_thumbsdir'];
			}

			$support ['desc_attachsdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_attachsdir'];
			if (file_exists("{$BASE_DIR}/fp-content/attachs/")) {
				$test_file = @fopen("{$BASE_DIR}/fp-content/attachs/chmod-test-file", "a+");
				if ($test_file) {
					$support ['attachsdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_attachsdir'];
				} else {
					$support ['attachsdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_attachsdir'];
				}
				@fclose($test_file);
				@unlink("{$BASE_DIR}/fp-content/attachs/chmod-test-file");
			} else {
				$support ['attachsdir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_attachsdir'];
			}

			$support ['desc_cachedir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_cachedir'];
			if (file_exists("{$BASE_DIR}/fp-content/cache/")) {
				$test_file = @fopen("{$BASE_DIR}/fp-content/cache/chmod-test-file", "a+");
				if ($test_file) {
					$support ['cachedir'] = $lang ['admin'] ['maintain'] ['support'] ['success_cachedir'];
				} else {
					$support ['cachedir'] = $lang ['admin'] ['maintain'] ['support'] ['error1_cachedir'];
				}
				@fclose($test_file);
				@unlink("{$BASE_DIR}/fp-content/cache/chmod-test-file");
			} else {
				$support ['cachedir'] = $lang ['admin'] ['maintain'] ['support'] ['error2_cachedir'];
			}

			/**
			 * prepare output "PHP"
			 */
			$support ['h2_php'] = $lang ['admin'] ['maintain'] ['support'] ['h2_php'];
			$support ['php_ver'] = $lang ['admin'] ['maintain'] ['support'] ['php_ver'];

			$support ['h3_extensions'] = $lang ['admin'] ['maintain'] ['support'] ['h3_extensions'];

			$support ['desc_php_intl'] = $lang ['admin'] ['maintain'] ['support'] ['desc_php_intl'];
			if (function_exists("intl_error_name")) {
				$support ['php_intl'] = $lang ['admin'] ['maintain'] ['support'] ['success_php_intl'];
			} else {
				$support ['php_intl'] = $lang ['admin'] ['maintain'] ['support'] ['error_php_intl'];
			}

			$support ['desc_php_gdlib'] = $lang ['admin'] ['maintain'] ['support'] ['desc_php_gdlib'];
			if (function_exists("gd_info")) {
				$support ['php_gdlib'] = $lang ['admin'] ['maintain'] ['support'] ['success_php_gdlib'];
			} else {
				$support ['php_gdlib'] = $lang ['admin'] ['maintain'] ['support'] ['error_php_gdlib'];
			}

			/**
			 * prepare output "Other"
			 */
			$support ['h2_other'] = $lang ['admin'] ['maintain'] ['support'] ['h2_other'];
			$support ['desc_browser'] = $lang ['admin'] ['maintain'] ['support'] ['desc_browser'];

			// Browser recognition does not always work correctly.
			// This also depends on whether a current browscap has been set in php.ini or not.
			function browser() {
				$lang = lang_load('plugin:support');

				$user_agent = $_SERVER ['HTTP_USER_AGENT'];
				$browser = $lang ['admin'] ['maintain'] ['support'] ['no_browser'];

				$browsers = [
					'/msie/i' => 'Internet explorer',
					'/trident/i' => 'Internet explorer',
					'/edge/i' => 'Edge',
					'/firefox/i' => 'Firefox',
					'/safari/i' => 'Safari',
					'/chrome/i' => 'Chrome',
					'/opera/i' => 'Opera',
					'/opr/i' => 'Opera',
					'/mobile/i' => 'Mobile browser',
					'/konqueror/i' => 'Konqueror'
				];

				foreach ($browsers as $regex => $value) {
					if (preg_match($regex, $user_agent)) {
						$browser = $value;
					}
				}
				return $browser;
			}
			$support ['detect_browser'] = $lang ['admin'] ['maintain'] ['support'] ['detect_browser'];
			$support ['function_browser'] = browser();

			$support ['desc_cookie'] = $lang ['admin'] ['maintain'] ['support'] ['desc_cookie'];
			$support ['session_cookie'] = $lang ['admin'] ['maintain'] ['support'] ['session_cookie'];
			if (function_exists("cookie_setup")) {
				@cookie_setup();
				$support ['output_sess_cookie'] = SESS_COOKIE;
			} else {
				$support ['output_sess_cookie'] = $lang ['admin'] ['maintain'] ['support'] ['no_session_cookie'];
			}

			$support ['h3_completed'] = $lang ['admin'] ['maintain'] ['support'] ['h3_completed'];

			$support ['symbols'] = $lang ['admin'] ['maintain'] ['support'] ['symbols'];
			$support ['symbol_success'] = $lang ['admin'] ['maintain'] ['support'] ['symbol_success'];
			$support ['symbol_attention'] = $lang ['admin'] ['maintain'] ['support'] ['symbol_attention'];
			$support ['symbol_error'] = $lang ['admin'] ['maintain'] ['support'] ['symbol_error'];

			$support ['close_btn'] = $lang ['admin'] ['maintain'] ['support'] ['close_btn'];

			$this->smarty->assign('support', $support);
		}

	}

	// register stylesheet
	add_action('wp_head', 'plugin_support_head');

	// register to 'maintain' menu
	admin_addpanelaction('maintain', 'support', true);
}
