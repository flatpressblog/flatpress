<?php
/*
 * Plugin Name: FlatPress Protect
 * Plugin URI: https://www.flatpress.org/
 * Description: Offers various options for the security of your blog. <a href="./fp-plugins/fpprotect/doc_fpprotect.txt" title="More information" target="_blank">[More information]</a><br>Part of the standard distribution.
 * Author: FlatPress
 * Version: 1.1.0
 * Author URI: https://www.flatpress.org
 */

// Define default options for the plugin
define('FP_PROTECT_DEFAULT_OPTIONS', [
	'allowUnsafeInline' => false,
	'allowPrettyURLEdit' => false,
	'allowImageMetadata' => false,
	'allowVisitorIp' => false,
]);

/**
 * Get the plugin options, merging them with the defaults.
 *
 * @return array The plugin configuration options.
 */
function fpprotect_get_options() {
	global $fp_config;

	// Merge default options with user-defined ones
	$config = $fp_config ['plugins'] ['fpprotect'] ?? [];
	return array_merge(FP_PROTECT_DEFAULT_OPTIONS, array_map('boolval', $config));
}

// Get options once for this scope
$options = fpprotect_get_options();

if (function_exists('is_https') && is_https()) {

	$random_hex = RANDOM_HEX;

	// Create the script-src header based on the configuration
	$scriptSrc = 'script-src \'self\' ';
	$scriptSrc .= $options ['allowUnsafeInline'] ? '\'unsafe-inline\' https:;' : '\'nonce-' . $random_hex . '\' https:;';

	/**
	 * Content Security Policy rules for Youtube, Facebook and Vimeo embedded video / BBCode [video], embedded OSM '
	 * https://scotthelme.co.uk/content-security-policy-an-introduction/
	 */
	header('Content-Security-Policy: upgrade-insecure-requests; ' . // Is migrating from HTTP to HTTPS, will ensure that all requests will be sent over HTTPS with no fallback to HTTP
		'default-src \'none\'; ' . // The default-src directive is the default setting for all directives that load additional content such as JavaScript, images, CSS, fonts, AJAX requests, frames and HTML5 media.
		'frame-src \'self\' https: data:; ' . // Allows iframes from other sources - only via https
		'base-uri \'self\'; ' . //
		'font-src \'self\' https: data:; ' . // Allows fonts from other sources (e.g. font awesome) - only via https
		$scriptSrc . // Use the dynamic script-src directive based on the configuration
		'style-src \'self\' \'unsafe-inline\' https:; ' . // Defines permitted sources for stylesheets e.g Youtube - only via https
		'img-src \'self\' https: data:; ' . // Defines permitted sources for images - only via https (e.g. base64-encoded images)
		'manifest-src \'self\'; ' . // Specifies the URLs from which video, audio and text track resources can be loaded from
		'worker-src \'self\' blob:; ' . //
		'connect-src \'self\' https:; ' . // Applies to XMLHttpRequests (AJAX), WebSockets or EventSource - only via https. Otherwise emulate 400 HTTP status code
		'media-src \'self\' https:; ' . // Defines permitted sources for audio and video, e.g. HTML5 <audio>, <video> elements.
		'child-src \'self\'; ' . // Defines permitted sources for web workers and nested browsing contexts for elements such as <frame> and <iframe> - only via https
		'form-action \'self\'; ' . // Defines permitted targets for HTML forms
		'object-src \'self\' https:' // Defines permitted sources for plugins, e.g. <object>, <embed> or <applet> - only via https
	);
	// End of Content Security Policy rules

	header('Permissions-Policy: interest-cohort=(), autoplay=(self), camera=(self), fullscreen=*, geolocation=(self), microphone=(self), payment=()');
	header('Referrer-Policy: strict-origin-when-cross-origin');
	header('Strict-Transport-Security: max-age=15552000; includeSubDomains');
	header('Cross-Origin-Embedder-Policy: same-origin');
	header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
	header('Cross-Origin-Resource-Policy: same-site');
	header('X-Permitted-Cross-Domain-Policies: none');
	header('X-Download-Options: noopen');

	// Emergency solution for Shared hosting environments; should already be done in the php.ini file or server configuration
	header_remove('X-Powered-By'); // Hide server information
	header_remove('Server');
	header('Server: FlatPress');
}

header('Content-Security-Policy: frame-ancestors \'self\'', false); // Defines permitted sources that may have embedded content, such as <frame>, <iframe>, <object>, <embed> and <applet>.

/**
 * Check if PrettyURLs plugin should allow editing htaccess.
 *
 * @return bool Whether editing is allowed.
 */
function fpprotect_harden_prettyurls_plugin() {
	$options = fpprotect_get_options();
	return !$options ['allowPrettyURLEdit'];
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

			// Process the form once it has been submitted
			if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
				// Use the onsubmit method to save the configuration
				$this->onsubmit();
			}
			// Render template
			$this->assign_config_to_template();
		}

		/**
		 * Assign plugin configuration to the template.
		 */
		function assign_config_to_template() {
			$options = fpprotect_get_options();
			foreach ($options as $key => $value) {
				$this->smarty->assign($key, $value);
			}

			// Define warnings for specific options
			$warnings = [
				'allowUnsafeInline' => 'warning_allowUnsafeInline',
				'allowVisitorIp' => 'warning_allowVisitorIp',
			];

			global $lang;
			foreach ($warnings as $optionKey => $warningKey) {
				// If allow is true, issue a warning
				if (!empty($options [$optionKey])) {
					$this->smarty->append('warnings', $lang ['admin'] ['config'] ['fpprotect'] [$warningKey]);
				}
			}
		}

		/**
		 * Handle form submission and save configuration.
		 *
		 * @param array|null $data The submitted data (if any).
		 * @return int Always returns 0
		 */
		function onsubmit($data = null) {

			// Save the new settings in the configuration
			foreach (array_keys(FP_PROTECT_DEFAULT_OPTIONS) as $key) {
				$value = isset($_POST [$key]) ? true : false;
				plugin_addoption('fpprotect', $key, $value);
			}
			plugin_saveoptions('fpprotect');

			// Update the template
			$this->smarty->assign('success', 1);
			$this->assign_config_to_template();

			return 0;
		}
	}

	// Register the admin panel in the 'config' menu
	admin_addpanelaction('config', 'fpprotect', true);
}
?>
