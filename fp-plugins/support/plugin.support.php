<?php
/*
 * Plugin Name: Support
 * Description: Shows support data in the Maintain menu. Part of the standard distribution.
 * Version: 1.1.1
 * Plugin URI: https://flatpress.org
 * Author: FlatPress
 * Author URI: https://flatpress.org
 */
require_once ABS_PATH . 'defaults.php';
require_once INCLUDES_DIR . 'includes.php';

if (class_exists('AdminPanelAction')) {

	function owner_has_write_permission($path) {
		if (!file_exists($path)) {
			return false;
		}
		$is_writable = is_writable($path);
		$perms = fileperms($path);
		if ($perms === false) {
			return false;
		}
		// -3, 1 owner, -1 others, -2 group/ others
		$octal_perms = substr(sprintf('%o', $perms), -3, 1);
		return $is_writable && (
			// 2: Write permission for the group/others.
			// 6: Read and write permission for the group/ others.
			// 7: Full access (read, write, execute) for the group/others.
			strpos($octal_perms, '2') !== false || strpos($octal_perms, '6') !== false || strpos($octal_perms, '7') !== false
		);
	}

	function other_has_write_permission($path) {
		if (!file_exists($path)) {
			return false;
		}
		$is_writable = is_writable($path);
		$perms = fileperms($path);
		if ($perms === false) {
			return false;
		}
		// -3, 1 owner, -1 others, -2 group/ others
		$octal_perms = substr(sprintf('%o', $perms), -1);
		return $is_writable && (
			// 2: Write permission for the group/others.
			// 6: Read and write permission for the group/ others.
			// 7: Full access (read, write, execute) for the group/others.
			strpos($octal_perms, '2') !== false || strpos($octal_perms, '6') !== false || strpos($octal_perms, '7') !== false
		);
	}

	// Browser recognition does not always work correctly.
	// This also depends on whether a current browscap has been set in php.ini or not.
	function browser_rec() {
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

	function plugin_support_head() {
		$plugindir = plugin_geturl('support');
		$random_hex = RANDOM_HEX;
		$css = utils_asset_ver($plugindir . 'res/support.css', SYSTEM_VER);
		$js = utils_asset_ver($plugindir . 'res/support.js', SYSTEM_VER);

		echo '
		<!-- BOF support files -->
		<link rel="stylesheet" type="text/css" href="' . $css . '">
		<script nonce="' . $random_hex . '" src="' . $js . '" defer></script>
		<!-- EOF support files -->
		';
	}

	class admin_maintain_support extends AdminPanelAction {

		function setup() {
			$this->smarty->assign('admin_resource', 'plugin:support/admin.plugin.support');
		}

		function main() {
			$pluginConfigPath = CONFIG_DIR . 'plugins.conf.php';
			if (file_exists($pluginConfigPath)) {
				/** @phpstan-ignore-next-line */
				require $pluginConfigPath;
			}
			global $fp_config;

			$BASE_DIR = defined('BASE_DIR') ? BASE_DIR : null;
			$setupfile = $BASE_DIR !== null ? $BASE_DIR . '/setup.php' : null;
			$LANG_DEFAULT = defined('LANG_DEFAULT') ? LANG_DEFAULT : null;
			$langId = $fp_config ['locale'] ['lang'] ?? null;
			$charset = $fp_config ['locale'] ['charset'] ?? null;
			$theme = $fp_config ['general'] ['theme'] ?? null;
			$style = $fp_config ['general'] ['style'] ?? null;
			$BLOG_BASEURL = $fp_config ['general'] ['www'] ?? null;
			$lang = lang_load('plugin:support');

			$support = [];
			$support ['output_LANG_DEFAULT'] = '';
			$support ['output_lang'] = '';
			$support ['output_charset'] = '';
			$support ['output_theme'] = '';
			$support ['output_style'] = '';
			$support ['output_plugins'] = '';

			/**
			 * prepare output "Setup"
			 */
			$support ['h2_general'] = $lang ['admin'] ['maintain'] ['support'] ['h2_general'];

			$support ['h3_setup'] = $lang ['admin'] ['maintain'] ['support'] ['h3_setup'];

			$support ['output_SYSTEM_VER'] = SYSTEM_VER . '</p>';

			$support ['output_BASE_DIR'] = BASE_DIR . '</p>';

			$support ['output_www'] = $BLOG_BASEURL . '</p>';

			if ($theme) {
				$support ['theme'] = $lang ['admin'] ['maintain'] ['support'] ['pos_theme'];
				$support ['output_theme'] = $fp_config ['general'] ['theme'] . '</p>';
			} else {
				$support ['theme'] = $lang ['admin'] ['maintain'] ['support'] ['neg_theme'];
			}

			if ($style) {
				$support ['style'] = $lang ['admin'] ['maintain'] ['support'] ['pos_style'];
				$support ['output_style'] = $fp_config ['general'] ['style'] . '</p>';
			} else {
				$support ['style'] = $lang ['admin'] ['maintain'] ['support'] ['neg_style'];
			}

			if ($fp_plugins) {
				$support ['plugins'] = $lang ['admin'] ['maintain'] ['support'] ['pos_plugins'];
				$support ['output_plugins'] = implode(', ', $fp_plugins) . '</p>';
			} else {
				$support ['plugins'] = $lang ['admin'] ['maintain'] ['support'] ['neg_plugins'];
			}

			/**
			 * prepare output "International"
			 */
			$support ['h3_international'] = $lang ['admin'] ['maintain'] ['support'] ['h3_international'];

			if ($LANG_DEFAULT) {
				$support ['LANG_DEFAULT'] = $lang ['admin'] ['maintain'] ['support'] ['pos_LANG_DEFAULT'];
				$support ['output_LANG_DEFAULT'] = $LANG_DEFAULT . '</p>';
			} else {
				$support ['LANG_DEFAULT'] = $lang ['admin'] ['maintain'] ['support'] ['neg_LANG_DEFAULT'];
			}

			if ($langId) {
				$support ['lang'] = $lang ['admin'] ['maintain'] ['support'] ['pos_lang'];
				$support ['output_lang'] = $fp_config ['locale'] ['lang'] . '</p>';
			} else {
				$support ['lang'] = $lang ['admin'] ['maintain'] ['support'] ['neg_lang'];
			}

			if ($charset) {
				$support ['charset'] = $lang ['admin'] ['maintain'] ['support'] ['pos_charset'];
				$support ['output_charset'] = $fp_config ['locale'] ['charset'] . '</p>';
			} else {
				$support ['charset'] = $lang ['admin'] ['maintain'] ['support'] ['neg_charset'];
			}

			$support ['global_date_time'] = $lang ['admin'] ['maintain'] ['support'] ['global_date_time'];
			if (function_exists('gmdate')) {
				$support ['output_global_date_time'] = gmdate('Y-m-d H:i:s') . '</p>';
			} else {
				$support ['output_global_date_time'] = $lang ['admin'] ['maintain'] ['support'] ['neg_global_date_time'];
			}

			$support ['local_date_time'] = $lang ['admin'] ['maintain'] ['support'] ['local_date_time'];
			$dateTime = getLocalDateTime();
			if ($dateTime !== false) {
				$support ['output_local_date_time'] = $dateTime . '</p>';
			} else {
				$support ['output_local_date_time'] = $lang ['admin'] ['maintain'] ['support'] ['neg_local_date_time'];
			}

			$support ['time_offset'] = $lang ['admin'] ['maintain'] ['support'] ['time_offset'];
			$support ['timeoffset'] = $fp_config ['locale'] ['timeoffset'] . ' hours</p>';

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
			if (file_exists($BASE_DIR . '/defaults.php')) {
				if (is_readable($BASE_DIR . '/defaults.php') && other_has_write_permission($BASE_DIR . '/defaults.php')) {
					$support ['defaultsfile'] = $lang ['admin'] ['maintain'] ['support'] ['attention_defaultsfile'];
				} else {
					$support ['defaultsfile'] = $lang ['admin'] ['maintain'] ['support'] ['success_defaultsfile'];
				}
			} else {
				$support ['defaultsfile'] = $lang ['admin'] ['maintain'] ['support'] ['neg_local_date_time'];
			}

			$support ['desc_configdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_configdir'];
			$config_dir = $BASE_DIR . '/fp-content/config';
			if (file_exists($config_dir) && is_readable($config_dir)) {
				if (other_has_write_permission($config_dir)) {
					$test_file = @fopen($config_dir . '/chmod-test-file', 'a+');
					if ($test_file) {
						$support ['configdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_configdir'];
						@fclose($test_file);
						@unlink($config_dir . '/chmod-test-file');
					} else {
						$support ['configdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_configdir'];
					}
				} else {
					$support ['configdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_configdir'];
				}
			} else {
				$support ['configdir'] = $lang ['admin'] ['maintain'] ['support'] ['neg_local_date_time'];
			}

			$support ['desc_admindir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_admindir'];
			$admin_dir = $BASE_DIR . '/admin';
			if (file_exists($admin_dir) && is_readable($admin_dir)) {
				if (other_has_write_permission($admin_dir)) {
					$test_file = @fopen($admin_dir . '/chmod-test-file', 'a+');
					if ($test_file) {
						$support ['admindir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_admindir'];
						@fclose($test_file);
						@unlink($admin_dir . '/chmod-test-file');
					} else {
						$support ['admindir'] = $lang ['admin'] ['maintain'] ['support'] ['success_admindir'];
					}
				} else {
					$support ['admindir'] = $lang ['admin'] ['maintain'] ['support'] ['success_admindir'];
				}
			} else {
				$support ['admindir'] = $lang ['admin'] ['maintain'] ['support'] ['neg_local_date_time'];
			}

			$support ['desc_includesdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_includesdir'];
			$includes_dir = $BASE_DIR . '/fp-includes';
			if (file_exists($includes_dir) && is_readable($includes_dir)) {
				if (other_has_write_permission($includes_dir)) {
					$test_file = @fopen($includes_dir . '/chmod-test-file', 'a+');
					if ($test_file) {
						$support ['includesdir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_includesdir'];
						@fclose($test_file);
						@unlink($includes_dir . '/chmod-test-file');
					} else {
						$support ['includesdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_includesdir'];
					}
				} else {
					$support ['includesdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_includesdir'];
				}
			} else {
				$support ['admindir'] = $lang ['admin'] ['maintain'] ['support'] ['neg_local_date_time'];
			}

			/**
			 * prepare output "Configuration file for the webserver"
			 */
			$support ['h3_configwebserver'] = $lang ['admin'] ['maintain'] ['support'] ['h3_configwebserver'];
			$support ['note_configwebserver'] = $lang ['admin'] ['maintain'] ['support'] ['note_configwebserver'];
			$support ['serversoftware'] = $lang ['admin'] ['maintain'] ['support'] ['serversoftware'];

			$test_file = @fopen($BASE_DIR . '/chmod-test-file', 'a+');
			if ($test_file) {
				$support ['maindir'] = $lang ['admin'] ['maintain'] ['support'] ['success_maindir'];
				@fclose($test_file);
				@unlink($BASE_DIR . '/chmod-test-file');
			} else {
				$support ['maindir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_maindir'];
			}

			// Do not create a .hthaccess file, otherwise the PrettyURLs plugin cannot create its own file. Better is .htaccess.txt
			$test_file = @fopen($BASE_DIR . '/.htaccess.txt', 'a+');
			if ($test_file) {
				$support ['htaccessw'] = $lang ['admin'] ['maintain'] ['support'] ['success_htaccessw'];
				@fclose($test_file);
				@unlink($BASE_DIR . '/.htaccess.txt');
			} else {
				$support ['htaccessw'] = $lang ['admin'] ['maintain'] ['support'] ['attention_htaccessw'];
			}

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
			$interface_dir = $BASE_DIR . '/fp-interface';
			if (file_exists($interface_dir) && is_readable($interface_dir)) {
				if (other_has_write_permission($interface_dir)) {
					$test_file = @fopen($interface_dir . '/chmod-test-file', 'a+');
					if ($test_file) {
						$support ['interfacedir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_interfacedir'];
						@fclose($test_file);
						@unlink($interface_dir . '/chmod-test-file');
					} else {
						$support ['interfacedir'] = $lang ['admin'] ['maintain'] ['support'] ['success_interfacedir'];
					}
				} else {
					$support ['interfacedir'] = $lang ['admin'] ['maintain'] ['support'] ['success_interfacedir'];
				}
			} else {
				$support ['admindir'] = $lang ['admin'] ['maintain'] ['support'] ['neg_local_date_time'];
			}

			$support ['desc_themesdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_themesdir'];
			$themes_dir = $BASE_DIR . '/fp-interface/themes';
			if (file_exists($themes_dir) && is_readable($themes_dir)) {
				if (other_has_write_permission($themes_dir)) {
					$test_file = @fopen($themes_dir . '/chmod-test-file', 'a+');
					if ($test_file) {
						$support ['themesdir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_themesdir'];
						@fclose($test_file);
						@unlink($themes_dir . '/chmod-test-file');
					} else {
						$support ['themesdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_themesdir'];
					}
				} else {
					$support ['themesdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_themesdir'];
				}
			} else {
				$support ['admindir'] = $lang ['admin'] ['maintain'] ['support'] ['neg_local_date_time'];
			}

			$support ['desc_plugindir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_plugindir'];
			$plugin_dir = $BASE_DIR . '/fp-plugins';
			if (file_exists($plugin_dir) && is_readable($plugin_dir)) {
				if (other_has_write_permission($plugin_dir)) {
					$test_file = @fopen($plugin_dir . '/chmod-test-file', 'a+');
					if ($test_file) {
						$support ['plugindir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_plugindir'];
						@fclose($test_file);
						@unlink($plugin_dir . '/chmod-test-file');
					} else {
						$support ['plugindir'] = $lang ['admin'] ['maintain'] ['support'] ['success_plugindir'];
					}
				} else {
					$support ['plugindir'] = $lang ['admin'] ['maintain'] ['support'] ['success_plugindir'];
				}
			} else {
				$support ['admindir'] = $lang ['admin'] ['maintain'] ['support'] ['neg_local_date_time'];
			}

			$support ['h3_contentdir'] = $lang ['admin'] ['maintain'] ['support'] ['h3_contentdir'];

			$support ['desc_contentdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_contentdir'];
			$content_dir = $BASE_DIR . '/fp-content';
			if (file_exists($content_dir) && is_readable($content_dir)) {
				if (owner_has_write_permission($content_dir)) {
					$test_file = @fopen($content_dir . '/chmod-test-file', 'a+');
					if ($test_file) {
						$support ['contentdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_contentdir'];
						@fclose($test_file);
						@unlink($content_dir . '/chmod-test-file');
					} else {
						$support ['contentdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_contentdir'];
					}
				} else {
					$support ['contentdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_contentdir'];
				}
			} else {
				$support ['admindir'] = $lang ['admin'] ['maintain'] ['support'] ['neg_local_date_time'];
			}

			$support ['desc_imagesdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_imagesdir'];
			$images_dir = $BASE_DIR . '/fp-content/images';
			if (file_exists($images_dir) && is_readable($images_dir)) {
				if (owner_has_write_permission($images_dir)) {
					$test_file = @fopen($images_dir . '/chmod-test-file', 'a+');
					if ($test_file) {
						$support ['imagesdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_imagesdir'];
						@fclose($test_file);
						@unlink($images_dir . '/chmod-test-file');
					} else {
						$support ['imagesdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_imagesdir'];
					}
				} else {
					$support ['imagesdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_imagesdir'];;
				}
			} else {
				$support ['imagesdir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_imagesdir'];
			}

			$support ['desc_thumbsdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_thumbsdir'];
			$thumbs_dir = $BASE_DIR . '/fp-content/images/.thumbs';
			if (file_exists($thumbs_dir) && is_readable($thumbs_dir)) {
				if (owner_has_write_permission($thumbs_dir)) {
					$test_file = @fopen($thumbs_dir . '/chmod-test-file', 'a+');
					if ($test_file) {
						$support ['thumbsdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_thumbsdir'];
						@fclose($test_file);
						@unlink($thumbs_dir . '/chmod-test-file');
					} else {
						$support ['thumbsdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_thumbsdir'];
					}
				} else {
					$support ['thumbsdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_thumbsdir'];
				}
			} else {
				$support ['thumbsdir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_thumbsdir'];
			}

			$support ['desc_attachsdir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_attachsdir'];
			$attachs_dir = $BASE_DIR . '/fp-content/attachs';
			if (file_exists($attachs_dir) && is_readable($attachs_dir)) {
				if (owner_has_write_permission($attachs_dir)) {
					$test_file = @fopen($attachs_dir . '/chmod-test-file', 'a+');
					if ($test_file) {
						$support ['attachsdir'] = $lang ['admin'] ['maintain'] ['support'] ['success_attachsdir'];
						@fclose($test_file);
						@unlink($attachs_dir . '/chmod-test-file');
					} else {
						$support ['attachsdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_attachsdir'];
					}
				} else {
					$support ['attachsdir'] = $lang ['admin'] ['maintain'] ['support'] ['error_attachsdir'];
				}
			} else {
				$support ['attachsdir'] = $lang ['admin'] ['maintain'] ['support'] ['attention_attachsdir'];
			}

			$support ['desc_cachedir'] = $lang ['admin'] ['maintain'] ['support'] ['desc_cachedir'];
			$cache_dir = $BASE_DIR . '/fp-content/cache';
			if (file_exists($cache_dir) && is_readable($cache_dir)) {
				if (owner_has_write_permission($cache_dir)) {
					$test_file = @fopen($cache_dir . '/chmod-test-file', 'a+');
					if ($test_file) {
						$support ['cachedir'] = $lang ['admin'] ['maintain'] ['support'] ['success_cachedir'];
						@fclose($test_file);
						@unlink($cache_dir . '/chmod-test-file');
					} else {
						$support ['cachedir'] = $lang ['admin'] ['maintain'] ['support'] ['error1_cachedir'];
					}
				} else {
					$support ['cachedir'] = $lang ['admin'] ['maintain'] ['support'] ['error1_cachedir'];
				}
			} else {
				$support ['cachedir'] = $lang ['admin'] ['maintain'] ['support'] ['error2_cachedir'];
			}

			/**
			 * prepare output "PHP"
			 */
			$support ['h2_php'] = $lang ['admin'] ['maintain'] ['support'] ['h2_php'];
			$support ['php_ver'] = $lang ['admin'] ['maintain'] ['support'] ['php_ver'];
			$support ['output_php_ver'] = phpversion();

			$support ['php_timezone'] = $lang ['admin'] ['maintain'] ['support'] ['php_timezone'];
			if (ini_get('date.timezone')) {
				$timezone = ini_get('date.timezone');
				$support ['output_timezone'] = $timezone;
			} else {
				$support ['output_timezone'] = $lang ['admin'] ['maintain'] ['support'] ['php_timezone_neg'];
			}

			$support ['h3_extensions'] = $lang ['admin'] ['maintain'] ['support'] ['h3_extensions'];

			$support ['desc_php_intl'] = $lang ['admin'] ['maintain'] ['support'] ['desc_php_intl'];
			if (function_exists('intl_error_name')) {
				$support ['php_intl'] = $lang ['admin'] ['maintain'] ['support'] ['success_php_intl'];
			} else {
				$support ['php_intl'] = $lang ['admin'] ['maintain'] ['support'] ['error_php_intl'];
			}

			$support ['desc_php_gdlib'] = $lang ['admin'] ['maintain'] ['support'] ['desc_php_gdlib'];
			if (function_exists('gd_info')) {
				$support ['php_gdlib'] = $lang ['admin'] ['maintain'] ['support'] ['success_php_gdlib'];
			} else {
				$support ['php_gdlib'] = $lang ['admin'] ['maintain'] ['support'] ['error_php_gdlib'];
			}

			$support ['desc_php_mbstring'] = $lang ['admin'] ['maintain'] ['support'] ['desc_php_mbstring'];
			if (function_exists('mb_get_info')) {
				$support ['php_mbstring'] = $lang ['admin'] ['maintain'] ['support'] ['success_php_mbstring'];
			} else {
				$support ['php_mbstring'] = $lang ['admin'] ['maintain'] ['support'] ['attention_php_mbstring'];
			}

			/**
			 * prepare output "Other"
			 */
			$support ['h2_other'] = $lang ['admin'] ['maintain'] ['support'] ['h2_other'];
			$support ['desc_browser'] = $lang ['admin'] ['maintain'] ['support'] ['desc_browser'];

			$support ['detect_browser'] = $lang ['admin'] ['maintain'] ['support'] ['detect_browser'];
			$support ['function_browser'] = browser_rec();

			$support ['desc_cookie'] = $lang ['admin'] ['maintain'] ['support'] ['desc_cookie'];
			$support ['session_cookie'] = $lang ['admin'] ['maintain'] ['support'] ['session_cookie'];
			if (function_exists('cookie_setup')) {
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

	// Register stylesheet
	add_action('admin_head', 'plugin_support_head');

	// Register to 'maintain' menu
	admin_addpanelaction('maintain', 'support', true);
}

function getLocalDateTime() {
	if (function_exists('date_time') && function_exists('gmdate')) {
		$timestamp = date_time();
		return gmdate('Y-m-d H:i:s', $timestamp);
	}
	return false;
}
?>
