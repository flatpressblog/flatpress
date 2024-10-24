<?php
/*
 * Plugin Name: FlatPress Protect
 * Plugin URI: https://www.flatpress.org/
 * Description: Offers various options for the security of your blog. <a href="./fp-plugins/fpprotect/doc_fpprotect.txt" title="More information" target="_blank">[More information]</a><br>Removes the htaccess editor from the PrettyURLs plugin.
 * Author: FlatPress
 * Version: 1.1.0
 * Author URI: https://www.flatpress.org
 */

if (function_exists('is_https')) {

	// $random_hex is only required if unsafe-inline is not set
	$random_hex = RANDOM_HEX;

	// Get the configuration from the fp_config file
	global $fp_config;

	if (isset($fp_config ['plugins'] ['fpprotect'] ['allowUnsafeInline'])) {
		$allowUnsafeInline = $fp_config ['plugins'] ['fpprotect'] ['allowUnsafeInline'];
	} else {
		// Default value, if not available
		$allowUnsafeInline = false;
	}

	if (isset($fp_config ['plugins'] ['fpprotect'] ['allowPrettyURLEdit'])) {
		$allowUnsafeInline = $fp_config ['plugins'] ['fpprotect'] ['allowPrettyURLEdit'];
	} else {
		// Default value, if not available
		$allowPrettyURLEdit = false;
	}

	if (is_https()) {
		/**
		 * Content Security Policy rules for Youtube, Facebook and Vimeo embedded video / BBCode [video], embedded OSM '
		 * https://scotthelme.co.uk/content-security-policy-an-introduction/
		 */

		// Create the script-src header based on the configuration
		$scriptSrc = 'script-src \'self\' ';
		if ($allowUnsafeInline) {
			// Allow unsafe inline JavaScript
			$scriptSrc .= '\'unsafe-inline\' https:; ';
		} else {
			// use nonce to ensure that only approved scripts are loaded
			$scriptSrc .= '\'nonce-' . $random_hex . '\' https:; ';
		}

		// Setze den gesamten CSP-Header
		header('Content-Security-Policy: upgrade-insecure-requests; ' . // Is migrating from HTTP to HTTPS, will ensure that all requests will be sent over HTTPS with no fallback to HTTP
			'default-src \'none\'; ' . // The default-src directive is the default setting for all directives that load additional content such as JavaScript, images, CSS, fonts, AJAX requests, frames and HTML5 media.
			'frame-src \'self\' https: data:; ' . // Allows iframes from other sources - only via https
			'base-uri \'self\'; ' . //
			'font-src \'self\' https: data:; ' . // Allows fonts from other sources (e.g. font awesome) - only via https
			$scriptSrc . // Use the dynamic script-src directive based on the configuration
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
		//header('Access-Control-Allow-Origin: ' . BLOG_BASEURL . ''); // Otherwise, clients could be prevented from retrieving a feed.
	}
}

function fpprotect_harden_prettyurls_plugin() {
	// If the checkbox is checked, the htaccess input field and the save button of the PrettyURLs plugin are displayed
	$config = plugin_getoptions('fpprotect');
	return isset($config['allowPrettyURLEdit']) ? !(bool)$config['allowPrettyURLEdit'] : true;
}

/**
 * Admin-Panel
 */
if (class_exists('AdminPanelAction')) {

	class admin_config_fpprotect extends AdminPanelAction {

		var $lang = 'plugin:fpprotect';

		function setup() {
			global $lang;

			// Load the language file
			$lang = lang_load('plugin:fpprotect');
			$this->smarty->assign('admin_resource', 'plugin:fpprotect/admin.plugin.fpprotect');
		}

		function main() {
			global $lang;
			$lang = lang_load('plugin:fpprotect');

			// Load the current checkbox selections
			$config = $this->load_config();

			// Process the form once it has been submitted
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				// Use the onsubmit method to save the configuration
				$this->onsubmit($_POST);
			}

			// Transfer the values of the checkboxes to the template
			$this->smarty->assign('allowUnsafeInline', $config['allowUnsafeInline']);
			$this->smarty->assign('allowPrettyURLEdit', $config['allowPrettyURLEdit']);

			// Render template
			$this->smarty->assign('admin_resource', 'plugin:fpprotect/admin.plugin.fpprotect');
		}

		function load_config() {
			// Load the plugin options
			$config = plugin_getoptions('fpprotect');

			// Return the values of the options, or set default values if they do not exist
			return array(
				'allowUnsafeInline' => isset($config['allowUnsafeInline']) ? (bool)$config['allowUnsafeInline'] : false,
				'allowPrettyURLEdit' => isset($config['allowPrettyURLEdit']) ? (bool)$config['allowPrettyURLEdit'] : false,
			);
		}

		function onsubmit($data = null) {
			// Check whether the checkboxes are set or not
			$allowUnsafeInline = isset($_POST['allowUnsafeInline']) ? true : false;
			$allowPrettyURLEdit = isset($_POST['allowPrettyURLEdit']) ? true : false;

			// Save the new settings in the configuration
			plugin_addoption('fpprotect', 'allowUnsafeInline', $allowUnsafeInline);
			plugin_addoption('fpprotect', 'allowPrettyURLEdit', $allowPrettyURLEdit); // Neue Option

			plugin_saveoptions('fpprotect');

			// Show success message
			global $lang;
			$this->smarty->assign('success', 1);
		}
	}

	// Registration of the panel in the 'config' menu
	admin_addpanelaction('config', 'fpprotect', true);
}
?>
