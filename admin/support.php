<?php
/*
 * Name: Create support data
 * Autor: FlatPress
 * Version: 1.0.1
 * Purpose: The FlatPress admin is thus able to provide the community with all relevant data to solve a problem quickly and specifically.
 * Hint: The output is in English only, so that the project supervisor or the community do not have to translate it.
 */
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-en">
<html>
	<head>
		<meta charset="UTF-8">
		<title>FlatPress - Create support data</title>
		<style type="text/css">
			body { font-family: Arial; background-color: #ffffff }
			h1 { margin-bottom: 0 }
			.error { color: #990000; padding-left: 10px }
			.attention { color: #D35400; padding-left: 10px }
			.success { color: #0E7924; padding-left: 10px }
			.output { padding-left: 10px }
			/* hidden code block - only becomes visible when pasted - onion juice ink - magic */
			h1 { margin-bottom: 0 }
			.codeblock { background-color: #ffffff; margin: 0; font-size: 0px }
		</style>
	</head>

	<body>
		<p>For bug reports and help, visit the <a href="https://forum.flatpress.org" target="_blank">FlatPress forum</a>, report the bug on <a href="https://github.com/flatpressblog/flatpress/issues" target="_blank">GitHub</a> or <a href="mailto:hello@flatpress.org">send an email</a>.<br>Include these outputs (copy &#38; paste) in English with the following information: bug description, steps to reproduce.</p>
		<h1>FlatPress general</h1>
		<p class="codeblock">[code]</p>
		<h2>Setup</h2>
		<?php
			require_once '../defaults.php';
			require_once INCLUDES_DIR . 'includes.php';

			$BASE_DIR = BASE_DIR;
			$setupfile = BASE_DIR . '/setup.php';

			$LANG_DEFAULT = null;
			$LANG_DEFAULT = LANG_DEFAULT;

			$lang = null;

			$charset = null;

			$theme = null;

			$style = null;

			$BLOG_BASEURL = null;

			if (file_exists("{$BASE_DIR}/fp-content/config/")) {
				require_once CONFIG_DIR . 'plugins.conf.php';
				require_once CONFIG_FILE;
				$lang = $fp_config ['locale'] ['lang'];
				$charset = $fp_config ['locale'] ['charset'];
				$theme = $fp_config ['general'] ['theme'];
				$style = $fp_config ['general'] ['style'];
				$BLOG_BASEURL = $fp_config ['general'] ['www'];
			} else {
				$fp_plugins = array();
			}

			echo '<p class="output"><strong>FlatPress version:</strong> ' . SYSTEM_VER . '</p>';
			echo '<p class="output"><strong>Basis directory:</strong> ' . BASE_DIR . '</p>';

			if ($BLOG_BASEURL) {
				echo '<p class="output"><strong>Blog base URL:</strong> ' . $BLOG_BASEURL . '</p>';
			} else {
				echo '<p class="output"><strong>Blog base URL:</strong> Could not be determined. ';
				if (file_exists($setupfile)) {
					echo '<a href="../setup.php" target="_blank">Start setup</a></p>';
				} else {
					echo '</p>';
				}
			}

			if ($LANG_DEFAULT) {
				echo '<p class="output"><strong>Language (automatic):</strong> ' . $LANG_DEFAULT . '</p>';
			} else {
				echo '<p class="output"><strong>Language (automatic): &#8505;</strong> not recognized</p>';
			}

			if ($lang) {
				echo '<p class="output"><strong>Language (set):</strong> ' . $lang . '</p>';
			} else {
				echo '<p class="output"><strong>Language (set):</strong> not set</p>';
			}

			if ($charset) {
				echo '<p class="output"><strong>Character set:</strong> ' . $charset . '</p>';
			} else {
				echo '<p class="output"><strong>Character set:</strong> not set (default is utf-8)</p>';
			}

			if ($theme) {
				echo '<p class="output"><strong>Theme:</strong> ' . $theme . '</p>';
			} else {
				echo '<p class="output"><strong>Theme:</strong> not set (default is leggero) ';
				if (file_exists($setupfile)) {
					echo '<a href="../setup.php" target="_blank">Start setup</a></p>';
				} else {
					echo '</p>';
				}
			}

			if ($style) {
				echo '<p class="output"><strong>Stil:</strong> ' . $style . '</p>';
			} else {
				echo '<p class="output"><strong>Stil:</strong> default style</p>';
			}

			if ($BLOG_BASEURL) {
				echo '<p class="output"><strong>Activated plugins:</strong></p>';
				echo '<p class="output">';
					for($i = 0; $i < count($fp_plugins); $i++) {
						echo ', ' . $fp_plugins [$i];
					}
				echo '</p>';
			} else {
				echo '<p class="output"><strong>Activated plugins:</strong> Could not be determined.</p>';
			}
		?>
		</p>
		<p class="codeblock">[/code]</p>

		<h1>FlatPress file and directory permissions</h1>
		<p class="codeblock">[code]</p>
		<h2>Core files</h2>
		<p>As soon as the setup has been successfully executed, the setup.php file should be deleted before productive operation.</p>
		<?php
			if (file_exists($setupfile)) {
				echo '<p class="error"><strong>&#33;</strong> The setup file is located in the main directory!</p>';
			} else {
				echo '<p class="success"><strong>&#10003;</strong> The setup file was not found in the main directory.</p>';
			}
		?>

		<p>The defaults.php file should only be read-only for productive operation.</p>
		<?php
			$test_file = @fopen("{$BASE_DIR}/defaults.php", "a+");
			if ($test_file) {
				echo '<p class="attention"><strong>&#8505;</strong> The defaults.php file can be changed!</p>';
			} else {
				echo '<p class="success"><strong>&#10003;</strong> The defaults.php file cannot be changed.</p>';
			}
			@fclose($test_file);
		?>

		<p>The admin directory should be read-only for productive operation.</p>
		<?php
			$test_file = @fopen("{$BASE_DIR}/admin/chmod-test-file", "a+");
			if ($test_file) {
				echo '<p class="attention"><strong>&#8505;</strong> The core files in the admin directory are writable!</p>';
			} else {
				echo '<p class="success"><strong>&#10003;</strong> The core files in the admin directory are not writable.</p>';
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/admin/chmod-test-file");
		?>

		<p>The fp-includes directory should be read-only for productive operation.</p>
		<?php
			$test_file = @fopen("{$BASE_DIR}/fp-includes/chmod-test-file", "a+");
			if ($test_file) {
				echo '<p class="attention"><strong>&#8505;</strong> The core files in the fp-includes directory are writable!</p>';
			} else {
				echo '<p class="success"><strong>&#10003;</strong> The core files in the fp-includes directory are not writable.</p>';
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/fp-includes/chmod-test-file");
		?>

		<h2>Configuration file for the webserver</h2>
		<p>The main directory must be writable in order to be able to create or modify an .htaccess file with the PrettyURLs plugin.</p>
		<p><strong>Note:</strong> Only web servers that are NCSA compatible, such as Apache, are familiar with the concept of .htaccess files.</p>
		<?php
			echo '<p>The server software is <strong>' . $_SERVER["SERVER_SOFTWARE"] . '</strong>.</p>';
			$test_file = @fopen("{$BASE_DIR}/chmod-test-file", "a+");
			if ($test_file) {
				echo '<p class="success"><strong>&#10003;</strong> The FlatPress main directory is writable.</p>';
			} else {
				echo '<p class="attention"><strong>&#8505;</strong> The FlatPress main directory is not writable!</p>';
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/chmod-test-file");

			// Do not create a .hthaccess file, otherwise the PrettyURLs plugin cannot create its own file. Better is .htaccess.txt
			$test_file = @fopen("{$BASE_DIR}/.htaccess.txt", "a+");
			if ($test_file) {
				echo '<p class="success"><strong>&#10003;</strong> The .htaccess file is writable.</p>';
			} else {
				echo '<p class="attention"><strong>&#8505;</strong> The .htaccess file is not writable!</p>';
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/.htaccess.txt");

			$htaccess = BASE_DIR . '/.htaccess';
			if (file_exists($htaccess)) {
				echo '<p class="attention"><strong>&#8505;</strong> A .htaccess file already exists in the main directory!</p>';
			} else {
				echo '<p class="success"><strong>&#10003;</strong> No .htaccess file was found in the main directory.</p>';
			}
		?>

		<h2>Themes and plugins</h2>
		<p>The fp-interface directory should be read-only for productive operation.</p>
		<?php
			$test_file = @fopen("{$BASE_DIR}/fp-interface/chmod-test-file", "a+");
			if ($test_file) {
				echo '<p class="attention"><strong>&#8505;</strong> The directory fp-interface writable!</p>';
			} else {
				echo '<p class="success"><strong>&#10003;</strong> The directory fp-interface is not writable.</p>';
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/fp-interface/chmod-test-file");
		?>

		<p>The themes directory should be read-only for productive operation.</p>
		<?php
			$test_file = @fopen("{$BASE_DIR}/fp-interface/themes/chmod-test-file", "a+");
			if ($test_file) {
				echo '<p class="attention"><strong>&#8505;</strong> The theme directory is writable!</p>';
			} else {
				echo '<p class="success"><strong>&#10003;</strong> The theme directory is not writable.</p>';
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/fp-interface/themes/chmod-test-file");
		?>

		<p>The fp-plugin directory should be read-only for productive operation.</p>
		<?php
			$test_file = @fopen("{$BASE_DIR}/fp-plugins/chmod-test-file", "a+");
			if ($test_file) {
				echo '<p class="attention"><strong>&#8505;</strong> The plugin directory fp-plugins writable!</p>';
			} else {
				echo '<p class="success"><strong>&#10003;</strong> The plugin directory fp-plugins is not writable.</p>';
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/fp-plugins/chmod-test-file");
		?>

		<h2>Content directory</h2>
		<p>The fp-content directory must be writable for FlatPress to work.</p>
		<?php
			$test_file = @fopen("{$BASE_DIR}/fp-content/chmod-test-file", "a+");
			if ($test_file) {
				echo '<p class="success"><strong>&#10003;</strong> The fp-content directory is writable.</p>';
			} else {
				echo '<p class="error"><strong>&#33;</strong> The fp-content directory is not writable!</p>';
			}
			@fclose($test_file);
			@unlink("{$BASE_DIR}/fp-content/chmod-test-file");
		?>

		<p>This images directory must have write permissions so that you can upload images.</p>
		<?php
			if (file_exists("{$BASE_DIR}/fp-content/images/")) {
				$test_file = @fopen("{$BASE_DIR}/fp-content/images/chmod-test-file", "a+");
				if ($test_file) {
					echo '<p class="success"><strong>&#10003;</strong> The images directory is writable.</p>';
				} else {
					echo '<p class="error"><strong>&#33;</strong> The images directory is not writable!</p>';
				}
				@fclose($test_file);
				@unlink("{$BASE_DIR}/fp-content/images/chmod-test-file");
			} else {
				echo '<p class="attention"><strong>&#8505;</strong> The images directory does not exist.</p>';
			}
		?>

		<p>This thumbs directory must have write permissions so that scalable images can be created.</p>
		<?php
			if (file_exists("{$BASE_DIR}/fp-content/images/.thumbs")) {
				$test_file = @fopen("{$BASE_DIR}/fp-content/images/.thumbs/chmod-test-file", "a+");
				if ($test_file) {
					echo '<p class="success"><strong>&#10003;</strong> The images/.thumbs directory is writable.</p>';
				} else {
					echo '<p class="error"><strong>&#33;</strong> The images/.thumbs directory is not writable!</p>';
				}
				@fclose($test_file);
				@unlink("{$BASE_DIR}/fp-content/images/chmod-test-file");
			} else {
				echo '<p class="attention"><strong>&#8505;</strong> The .thumbs directory does not exist, but is created automatically as soon as a thumbnail has been created with the Thumbnails plugin.</p>';
			}
		?>

		<p>This upload directory must have write permissions so that you can upload something.</p>
		<?php
			if (file_exists("{$BASE_DIR}/fp-content/attachs/")) {
				$test_file = @fopen("{$BASE_DIR}/fp-content/attachs/chmod-test-file", "a+");
				if ($test_file) {
					echo '<p class="success"><strong>&#10003;</strong> The upload directory is writable.</p>';
				} else {
					echo '<p class="error"><strong>&#33;</strong> The upload directory is not writable!</p>';
				}
				@fclose($test_file);
				@unlink("{$BASE_DIR}/fp-content/attachs/chmod-test-file");
			} else {
				echo '<p class="attention"><strong>&#8505;</strong> The upload directory does not exist, but is created automatically with the first upload.</p>';
			}
		?>

		<p>This cache directory must have write permission for the cache to function correctly.</p>
		<?php
			if (file_exists("{$BASE_DIR}/fp-content/cache/")) {
				$test_file = @fopen("{$BASE_DIR}/fp-content/cache/chmod-test-file", "a+");
				if ($test_file) {
					echo '<p class="success"><strong>&#10003;</strong> The cache directory is writable.</p>';
				} else {
					echo '<p class="error"><strong>&#33;</strong> The cache directory is not writable!</p>';
				}
				@fclose($test_file);
				@unlink("{$BASE_DIR}/fp-content/cache/chmod-test-file");
			} else {
				echo '<p class="error"><strong>&#33;</strong> The directory cache does not exist!</p>';
			}
		?>
		<p class="codeblock">[/code]</p>

		<h1>PHP</h1>
		<p class="codeblock">[code]</p>
		<p>The PHP version is <strong><?php echo phpversion();?></strong></p>
		<h2>Extensions</h2>
		<p>The PHP-Intl extension must be activated.</p>
		<?php
			if (!function_exists("intl_error_name")) {
				echo '<p class="error"><strong>&#33;</strong> The intl Extension is not activated!</p>';
			} else {
				echo '<p class="success"><strong>&#10003;</strong> The intl Extension is activated.</p>';
			}
		?>

		<p>The GDlib extension must be activated to create image thumbnails.</p>
		<?php
			if (!function_exists("gd_info")) {
				echo '<p class="error"><strong>&#33;</strong> The GD Extension is not activated!</p>';
			} else {
				echo '<p class="success"><strong>&#10003;</strong> The GD Extension is activated.</p>';
			} 
		?>
		<p class="codeblock">[/code]</p>

		<h1>Other</h1>
		<p class="codeblock">[code]</p>
		<p>The browser used is of interest if there are display errors.</p>
		<?php
			// Browser recognition does not always work correctly.
			// This also depends on whether a current browscap has been set in php.ini or not.
			function browser() {
				$user_agent = $_SERVER ['HTTP_USER_AGENT'];
				$browser = "Not recognized";

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
					'/konqueror/i' => 'Konqueror',
				];

				foreach ($browsers as $regex => $value) {
						if (preg_match($regex, $user_agent)) {
							$browser = $value;
						}
				}
				return $browser;
			}
			echo '<p class="output"><strong>Browser: </strong>' . browser() . '</p>';
		?>

		<p>If visitors to the FlatPress blog are to be informed about cookies, this is the cookie.</p>
		<p><strong>Hint:</strong> The name of the cookie changes each time FlatPress is reinstalled.</p>
		<?php
		if ($BLOG_BASEURL) {
			@cookie_setup();
			echo '<p class="output"><strong>FlatPress Session cookie: </strong>' . SESS_COOKIE . '</p>';
		} else {
			echo '<p class="output"><strong>FlatPress Session cookie: </strong> Could not be determined.</p>';
		}
		?>
		<p class="codeblock">[/code]</p>
		<h2>Output completed!</h2>
		<p class="output"><strong>Symbols:</strong></p>
		<p class="success"><strong>&#10003;</strong> No action necessary</p>
		<p class="attention"><strong>&#8505;</strong> Does not restrict functionality, but requires attention</p>
		<p class="error"><strong>&#33;</strong> Action urgently needed</p>
		<p>Powered by <a href="https://flatpress.org" target="_blank">FlatPress</a>.</p>
	</body>
</html>
