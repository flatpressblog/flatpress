<?php
/*
 * Plugin Name: FlatPress Protect
 * Plugin URI: http://www.flatpress.org/
 * Description: Protect your blog with additional fetures in the HTTP response header. <a href="./fp-plugins/fpprotect/doc_fpprotect.txt" title="More information" target="_blank">[More information]</a><br>Protect the .htaccess file from unintentional changes.
 * Author: FlatPress
 * Version: 1.0
 * Author URI: https://www.flatpress.org
 */

if (function_exists('is_https')) {

	if (is_https()) {
		// Content Security Policy rules for Youtube, Facebook and Vimeo embedded video / BBCode [video], embedded OSM
		header('Content-Security-Policy: default-src https: data:; frame-src https: data:; base-uri \'self\'; font-src https: data:; script-src https: \'unsafe-inline\' \'unsafe-eval\' blob:; style-src https: \'unsafe-inline\'; img-src https: data: blob:; frame-ancestors \'self\'; manifest-src \'self\'; worker-src \'self\' blob:; connect-src https: blob:; media-src \'self\' blob:; child-src \'self\' blob:; form-action \'self\'; object-src \'self\'');
		header('X-Content-Security-Policy: default-src https: data:; frame-src https: data:; base-uri \'self\'; font-src https: data:; script-src https: \'unsafe-inline\' \'unsafe-eval\' blob:; style-src https: \'unsafe-inline\'; img-src https: data: blob:; frame-ancestors \'self\'; manifest-src \'self\'; worker-src \'self\' blob:; connect-src https: blob:; media-src \'self\' blob:; child-src \'self\' blob:; form-action \'self\'; object-src \'self\'');
		header('X-WebKit-CSP: default-src https: data:; frame-src https: data:; base-uri \'self\'; font-src https: data:; script-src https: \'unsafe-inline\' \'unsafe-eval\' blob:; style-src https: \'unsafe-inline\'; img-src https: data: blob:; frame-ancestors \'self\'; manifest-src \'self\'; worker-src \'self\' blob:; connect-src https: blob:; media-src \'self\' blob:; child-src \'self\' blob:; form-action \'self\'; object-src \'self\'');

		// End of Content Security Policy rules
		header('Permissions-Policy: interest-cohort=(), autoplay=(self), camera=(self), fullscreen=*, geolocation=(self), microphone=(self), payment=()');
		header('Referrer-Policy: strict-origin-when-cross-origin');
		header('Strict-Transport-Security: max-age=15552000; includeSubDomains');
		header('X-Permitted-Cross-Domain-Policies: none');
		header('X-Download-Options: noopen');
	}

}

function hidde_input_field() {
	// If active, the input field and the save button of the PrettyURLs plugin are hidden
}
?>
