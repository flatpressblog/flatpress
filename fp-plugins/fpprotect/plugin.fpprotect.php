<?php
/**
 * Plugin Name: FlatPress Protect
 * Plugin URI: https://www.flatpress.org/
 * Description: Offers various options for the security of your blog.<br><a href="./fp-plugins/fpprotect/doc_fpprotect.txt" title="More information" target="_blank">[More information]</a><br>Part of the standard distribution.
 * Author: FlatPress
 * Version: 1.2.1
 * Author URI: https://www.flatpress.org
 */

// Define default options for the plugin
define('FP_PROTECT_DEFAULT_OPTIONS', [
	'allowUnsafeInline' => false,
	'allowExternalIframe' => false,
	'allowPrettyURLEdit' => false,
	'allowImageMetadata' => false,
	'allowSvgUpload' => false,
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

/**
 * Check whether a plugin is enabled (listed in fp-content/config/plugins.conf.php).
 *
 * We cannot reliably use function_exists() here because FlatPress loads plugin files
 * in the order of fp_plugins, and fpprotect may be included before gdprvideoembed.
 *
 * @param string $id Plugin ID.
 * @return bool
 */
function fpprotect_is_plugin_enabled($id) {
	// Preferred: the enabled plugin list already loaded for this request
	if (isset($GLOBALS ['fp_plugins']) && is_array($GLOBALS ['fp_plugins'])) {
		return in_array($id, $GLOBALS ['fp_plugins'], true);
	}

	// Fallback: load enabled plugin list from configuration
	if (defined('CONFIG_DIR')) {
		$conf = CONFIG_DIR . 'plugins.conf.php';
		if (file_exists($conf)) {
			$fp_plugins = array();
			include ($conf);
			if (isset($fp_plugins) && is_array($fp_plugins)) {
				return in_array($id, $fp_plugins, true);
			}
		}
	}

	return false;
}

// Get options once for this scope
$options = fpprotect_get_options();

$allow_external_iframe = !empty($options ['allowExternalIframe']);

// Allow GDPR Video embed placeholders (BBCode [video]) to be activated without opening all external iframes
$gdprvideoembed_enabled = fpprotect_is_plugin_enabled('gdprvideoembed');
$gdpr_video_frames = array(
	'https://www.youtube.com',
	'https://www.youtube-nocookie.com',
	'https://player.vimeo.com',
	'https://www.facebook.com',
);

if (function_exists('is_https') && is_https()) {

	$random_hex = RANDOM_HEX;

	// Create the script-src header based on the configuration
	$scriptSrc = 'script-src \'self\' ';
	$scriptSrc .= $options ['allowUnsafeInline'] ? '\'unsafe-inline\' https:;' : '\'nonce-' . $random_hex . '\' https:;';

	/**
	 * iFrame embedding policy
	 * - frame-src controls which origins this site may embed via <iframe>
	 * - child-src is used by some browsers as a fallback for frame-src
	 */
	$frameSrc = 'frame-src \'self\'';
	$childSrc = 'child-src \'self\'';

	if ($allow_external_iframe) {
		$frameSrc .= ' https: data:';
		$childSrc .= ' https: data:';
	} elseif ($gdprvideoembed_enabled) {
		$frameSrc .= ' ' . implode(' ', $gdpr_video_frames);
		$childSrc .= ' ' . implode(' ', $gdpr_video_frames);
	}

	$frameSrc .= '; ';
	$childSrc .= '; ';

	/**
	 * Content Security Policy rules for Youtube, Facebook and Vimeo embedded video / BBCode [video], embedded OSM '
	 * https://scotthelme.co.uk/content-security-policy-an-introduction/
	 */
	header('Content-Security-Policy: upgrade-insecure-requests; ' . // Is migrating from HTTP to HTTPS, will ensure that all requests will be sent over HTTPS with no fallback to HTTP
		'default-src \'none\'; ' . // The default-src directive is the default setting for all directives that load additional content such as JavaScript, images, CSS, fonts, AJAX requests, frames and HTML5 media.
		$frameSrc . // Allows iframes from other sources
		'frame-ancestors \'self\'; ' . // Defines permitted sources that may have embedded content, such as <frame>, <iframe>, <object>, <embed> and <applet>.
		'base-uri \'self\'; ' . //
		'font-src \'self\' https: data:; ' . // Allows fonts from other sources (e.g. font awesome) - only via https
		$scriptSrc . // Use the dynamic script-src directive based on the configuration
		'style-src \'self\' \'unsafe-inline\' https:; ' . // Defines permitted sources for stylesheets e.g Youtube - only via https
		'img-src \'self\' https: data:; ' . // Defines permitted sources for images - only via https (e.g. base64-encoded images)
		'manifest-src \'self\'; ' . // Specifies the URLs from which video, audio and text track resources can be loaded from
		'worker-src \'self\' blob:; ' . //
		'connect-src \'self\' https:; ' . // Applies to XMLHttpRequests (AJAX), WebSockets or EventSource - only via https. Otherwise emulate 400 HTTP status code
		'media-src \'self\' https:; ' . // Defines permitted sources for audio and video, e.g. HTML5 <audio>, <video> elements.
		$childSrc . // Defines permitted sources for web workers and nested browsing contexts for elements such as <frame> and <iframe>
		'form-action \'self\'; ' . // Defines permitted targets for HTML forms
		'object-src \'self\' https:;' // Defines permitted sources for plugins, e.g. <object>, <embed> or <applet> - only via https
	);
	// End of Content Security Policy rules

	header('Permissions-Policy: interest-cohort=(), autoplay=(self), camera=(self), fullscreen=*, geolocation=(self), microphone=(self), payment=()');
	header('Referrer-Policy: strict-origin-when-cross-origin');
	header('Strict-Transport-Security: max-age=15552000; includeSubDomains');
	header('Cross-Origin-Embedder-Policy: unsafe-none');
	header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
	header('Cross-Origin-Resource-Policy: same-site');
	header('X-Permitted-Cross-Domain-Policies: none');
	header('X-Download-Options: noopen');
} else {
	if ($allow_external_iframe) {
		// Allow embedding external content via <iframe> on HTTP installs as well
		header('Content-Security-Policy: frame-ancestors \'self\'; ' . //
			'frame-src \'self\' http: https: data:; ' . //
			'child-src \'self\' http: https: data:;');
	} else {
		// Keep external iframes blocked, but allow known video providers when GDPR Video embed is enabled
		$frameSrc = 'frame-src \'self\'';
		$childSrc = 'child-src \'self\'';
		if ($gdprvideoembed_enabled) {
			$frameSrc .= ' ' . implode(' ', $gdpr_video_frames);
			$childSrc .= ' ' . implode(' ', $gdpr_video_frames);
		}
		$frameSrc .= '; ';
		$childSrc .= '; ';
		header('Content-Security-Policy: frame-ancestors \'self\'; ' . $frameSrc . $childSrc);
	}
}

// Emergency solution for Shared hosting environments; should already be done in the php.ini file or server configuration
if (PHP_SAPI !== 'cli' && !headers_sent()) {
	header_remove('X-Powered-By'); // Hide server information
	header_remove('Server');
	header('Server: FlatPress');
}

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
			global $fp_config;
			$options = fpprotect_get_options();
			foreach ($options as $key => $value) {
				$this->smarty->assign($key, $value);
			}

			// Expose admin-session timeout to template (minutes)
			$admin_timeout = isset($fp_config ['auth'] ['session_timeout']) ? (int)$fp_config ['auth'] ['session_timeout'] : 3600;
			if ($admin_timeout <= 0) {
				$admin_timeout = 3600;
			}
			$this->smarty->assign('session_timeout_minutes', (int) ceil($admin_timeout / 60));

			// Define warnings for specific options
			$warnings = [
				'allowUnsafeInline' => 'warning_allowUnsafeInline',
				'allowExternalIframe' => 'warning_allowExternalIframe',
				'allowSvgUpload' => 'warning_allowSvgUpload',
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

			// Save admin-session timeout to global config
			$minutes = isset($_POST ['session_timeout_minutes']) ? (int) $_POST ['session_timeout_minutes'] : 0;
			global $fp_config;
			if (!isset($fp_config ['auth']) || !is_array($fp_config ['auth'])) {
				$fp_config ['auth'] = array();
			}
			if ($minutes > 0) {
				$fp_config ['auth'] ['session_timeout'] = $minutes * 60;
			} else {
				// Remove to fall back to default 3600
				unset($fp_config ['auth'] ['session_timeout']);
			}
			config_save();

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
