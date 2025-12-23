<?php
/**
 * Plugin Name: CookieBanner
 * Plugin URI: https://flatpress.org
 * Description: Displays a discreet banner that informs the visitor about the use of cookies and provides a link to the <a href="./admin.php?p=static&action=write&page=privacy-policy" title="Edit me!" >privacy policy</a>. Part of the standard distribution. <a href="#" id="DeleteCookie" title="Reset CookieBanner">[Reset]</a>
 * Author: FlatPress
 * Version: 1.0.3
 * Author URI: http://flatpress.org
 */

/**
 * Returns the CookieBanner language array, cached per request.
 * Uses lang_load() but avoids repeated merges that can turn strings into arrays
 * when array_merge_recursive() meets already-loaded translations.
 *
 * @return array
 */
function plugin_cookiebanner_getlang() {
	static $cache = null;
	if (is_array($cache)) {
		return $cache;
	}
	$cache = lang_load('plugin:cookiebanner');
	return is_array($cache) ? $cache : array();
}

/**
 * Normalize a language value to a scalar string (guards against recursive merges
 * that may turn strings into arrays).
 *
 * @param mixed  $value
 * @param string $default
 * @return string
 */
function plugin_cookiebanner_langval($value, $default = '') {
	// array_merge_recursive can turn scalar values into arrays; pick the first scalar value
	while (is_array($value)) {
		if (!$value) {
			return (string)$default;
		}
		$value = reset($value);
	}
	if (is_object($value) && method_exists($value, '__toString')) {
		return (string)$value;
	}
	return is_scalar($value) ? (string)$value : (string)$default;
}

function plugin_cookiebanner_head() {
	$pdir = plugin_geturl('cookiebanner');
	$random_hex = RANDOM_HEX;
	$css = utils_asset_ver($pdir . 'res/cookiebanner.css', SYSTEM_VER);
	$js = utils_asset_ver($pdir . 'res/cookiebanner.js', SYSTEM_VER);
	echo '
		<!-- BOF CookieBanner -->
		<link rel="stylesheet" type="text/css" href="' . $css . '">
		<script nonce="' . $random_hex . '" src="' . $js . '" defer></script>
		<!-- EOF Cookiebanner -->
';
}

add_action('wp_head', 'plugin_cookiebanner_head', 0);

function plugin_cookiebanner_footer() {

	$random_hex = RANDOM_HEX;

	$lang = plugin_cookiebanner_getlang();

	$bannertext = plugin_cookiebanner_langval($lang ['plugin'] ['cookiebanner'] ['bannertext'] ?? '');
	$ok = plugin_cookiebanner_langval($lang ['plugin'] ['cookiebanner'] ['ok'] ?? 'OK', 'OK');
	$ok_attr = htmlspecialchars($ok, ENT_QUOTES, 'UTF-8');

	echo '
		<!-- BOF Cookie-Banner HTML -->
		<div id="cookie_banner">
			<div class="buttonbar">
				' . $bannertext . '
				<input type="submit" value="' . $ok_attr . '" class="btn btn-primary btn-sm" id="btn-primary">
			</div>
		</div>
		<!-- EOF Cookie-Banner HTML -->

		<!-- BOF Cookie-Banner JS -->
		<script nonce="' . $random_hex . '">
			/**
			 * Initializes the CookieBanner plugin.
			 */
			if( document.cookie.indexOf(\'cookiebanner=1\') != -1 ){ // if cookie exists
				jQuery(\'#cookie_banner\').hide(); // then hide banner
			} else {
				jQuery(\'#cookie_banner\').prependTo(\'body\'); // to the body and display
			}

			// OK button - sets cookie
			function cookie_ok() {
				document.cookie = \'cookiebanner=1;path=/\';
				jQuery(\'#cookie_banner\').slideUp();
			}
 
			// Reset button - deletes the cookie and displays the banner again
			$(\'#DeleteCookie\').click(()=>{
				document.cookie = \'cookiebanner\' + \'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;\'; // delete CookieBanner -Cookie
				jQuery(\'#cookie_banner\').show(); // shows banner
			})

			// Replacement for OK button onclick HTML method
			function onClick_btn_primary() {
				onclick = cookie_ok(); return false;
			}
		</script>
		<!-- EOF Cookie-Banner JS -->
';
}

add_action('wp_footer', 'plugin_cookiebanner_footer', 0);


function plugin_cookiebanner_privacypolicy() {

	$lang = plugin_cookiebanner_getlang();

	$notice_text = plugin_cookiebanner_langval($lang ['plugin'] ['cookiebanner'] ['notice_text'] ?? '');

	echo '<p><em>' . $notice_text . '</em></p>';
}

add_action('comment_form', 'plugin_cookiebanner_privacypolicy', 0);
?>
