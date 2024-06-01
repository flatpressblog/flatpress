<?php
/*
 * Plugin Name: FlatPress Protect
 * Plugin URI: http://www.flatpress.org/
 * Description: Hardens your blog with additional features in the HTTP response header. <a href="./fp-plugins/fpprotect/doc_fpprotect.txt" title="More information" target="_blank">[More information]</a><br>Removes the htaccess editor from the PrettyURLs plugin.
 * Author: FlatPress
 * Version: 1.0
 * Author URI: https://www.flatpress.org
 */

if (function_exists('is_https')) {

	global $fp_config;
	$random_hex = $fp_config ['plugins'] ['fpprotect'] ['random_hex'];

	if (is_https()) {
		/**
		 * Content Security Policy rules for Youtube, Facebook and Vimeo embedded video / BBCode [video], embedded OSM '
		 * https://scotthelme.co.uk/content-security-policy-an-introduction/
		 */
		header('Content-Security-Policy: upgrade-insecure-requests; ' . // Is migrating from HTTP to HTTPS, will ensure that all requests will be sent over HTTPS with no fallback to HTTP
			'default-src \'self\'; ' . // The default-src directive is the default setting for all directives that load additional content such as JavaScript, images, CSS, fonts, AJAX requests, frames and HTML5 media.
			'frame-src \'self\' https: data:; ' . // Allows iframes from other sources - only via https
			'base-uri \'self\'; ' . //
			'font-src \'self\' https: data:; ' . // Allows fonts from other sources (e.g. font awesome) - only via https

			/**
			 * To make XSS attacks more difficult, remove all inline code in your plugins and templates and remove the “script-src inline-unsave” directive.
			 * https://content-security-policy.com/nonce/
			 */
			'script-src \'self\' \'unsafe-inline\' https:; ' . // Allows the use of inline code such as style attributes, event handler attributes such as onclick and JavaScript code noted in <script> elements
			//'script-src \'self\' \'nonce-' . $random_hex . '\' https:; ' . // No inline code without nonce-123xyz, no onclick e.t.c, other sources only https

			'style-src \'self\' \'unsafe-inline\' https:; ' . // Defines permitted sources for stylesheets e.g Youtube - only via https
			'img-src \'self\' https: data:; ' . // Defines permitted sources for images - only via https (e.g. base64-encoded images)
			'frame-ancestors \'self\' https:; ' . // Defines permitted sources that may have embedded content, such as <frame>, <iframe>, <object>, <embed> and <applet>. 
			'manifest-src \'self\'; ' . // Specifies the URLs from which video, audio and text track resources can be loaded from
			'worker-src \'self\' blob:; '. //
			'connect-src \'self\' https:; ' . // Applies to XMLHttpRequests (AJAX), WebSockets or EventSource - only via https. Otherwise emulate 400 HTTP status code
			'media-src \'self\' https:; ' . // Defines permitted sources for audio and video, e.g. HTML5 <audio>, <video> elements.
			'child-src \'self\'; ' . // Defines permitted sources for web workers and nested browsing contexts for elements such as <frame> and <iframe> - only via https
			'form-action \'self\'; ' . // Defines permitted targets for HTML forms
			'object-src \'self\' https:'); // Defines permitted sources for plugins, e.g. <object>, <embed> or <applet> - only via https

		// End of Content Security Policy rules
		header('Permissions-Policy: interest-cohort=(), autoplay=(self), camera=(self), fullscreen=*, geolocation=(self), microphone=(self), payment=()');
		header('Referrer-Policy: strict-origin-when-cross-origin');
		header('Strict-Transport-Security: max-age=15552000; includeSubDomains');

		header('Cross-Origin-Embedder-Policy: same-origin');
		header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
		header('Cross-Origin-Resource-Policy: same-site');

		header('X-Permitted-Cross-Domain-Policies: none');
		header('X-Download-Options: noopen');
	}
}

function fpprotect_harden_prettyurls_plugin() {
	// If active, the input field and the save button of the PrettyURLs plugin are hidden
	return true;
}
?>
