<?php
/*
 * Plugin Name: CookieBanner
 * Plugin URI: https://flatpress.org
 * Description: Displays a discreet banner that informs the visitor about the use of cookies and provides a link to the <a href="./admin.php?p=static&action=write&page=privacy-policy" title="Edit me!" >privacy policy</a>. Part of the standard distribution. <a href="#" id="DeleteCookie" title="Reset CookieBanner">[Reset]</a>
 * Author: FlatPress
 * Version: 1.0.1
 * Author URI: http://flatpress.org
 */ 
function plugin_cookiebanner_head() {
	$pdir = plugin_geturl('cookiebanner');
	echo '
		<!-- BOF CookieBanner CSS -->
		<link rel="stylesheet" type="text/css" href="' . $pdir . 'res/cookiebanner.css">
		<!-- EOF Cookiebanner CSS -->
';
}

add_action('wp_head', 'plugin_cookiebanner_head', 0);

function plugin_cookiebanner_footer() {

	global $lang;
	$random_hex = RANDOM_HEX;
	lang_load('plugin:cookiebanner');

	$bannertext = $lang ['plugin'] ['cookiebanner'] ['bannertext'];
	$ok = $lang ['plugin'] ['cookiebanner'] ['ok'];

	echo '
		<!-- BOF Cookie-Banner HTML -->
		<div id="cookie_banner">
			<div class="buttonbar">
				' . $bannertext . '
				<input type="submit" value="' . $ok . '" class="btn btn-primary btn-sm " onclick="cookie_ok()"></input>
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
		</script>
		<!-- EOF Cookie-Banner JS -->
';
}

add_action('wp_footer', 'plugin_cookiebanner_footer', 0);

  
function plugin_cookiebanner_privacypolicy() {
	global $lang;
	$lang = lang_load('plugin:cookiebanner');

	$notice_text = $lang ['plugin'] ['cookiebanner'] ['notice_text'];

	echo '<p><em>' . $notice_text . '</em></p>';
}

add_action('comment_form', 'plugin_cookiebanner_privacypolicy', 0);
?>

