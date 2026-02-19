<?php
/**
 * Style Name: Stringendo
 * Style URI: https://www.flatpress.org/
 * Description: Modern and responsive - with an energetic "Stringendo" accent palette and smart widget bar.
 * Version:  1.40
 * Author: Fraenkiman
 * Author URI: https://www.flatpress.org/
 */
$style ['name'] = 'Stringendo';
$style ['author'] = 'Fraenkiman';
$style ['www'] = 'https://www.flatpress.org/';

$style ['version'] = '1.40';

/**
 * Admin enhancement: add responsive viewport meta in the admin <head> only for Stringendo.
 * This avoids affecting other Leggero styles (the templates are shared between styles).
 */
if (defined('MOD_ADMIN_PANEL') && function_exists('add_action')) {
	if (!function_exists('stringendo_wp_head_admin_viewport')) {
		function stringendo_wp_head_admin_viewport(): void {
			if (!defined('MOD_ADMIN_PANEL')) {
				return;
			}
			$styleUrl = theme_style_geturl('stringendo');
			$bgRel = 'imgs/bg.svg';
			$bgUrl = $styleUrl . $bgRel;
			$bgFs = (defined('ABS_PATH') ? ABS_PATH : '') . THEMES_DIR . THE_THEME . '/stringendo/' . $bgRel;

			echo '
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="preload" as="image" href="' . htmlspecialchars($bgUrl, ENT_QUOTES, 'UTF-8') . '" type="image/svg+xml" fetchpriority="high">
			';
		}
	}
	add_action('wp_head', 'stringendo_wp_head_admin_viewport', 0);
}

/**
 * Frontend enhancement: if the sidebar widget column is taller than the main content
 * (>= 960px viewport), move overflowing widgets under #main for a balanced layout.
 * This is intentionally JS-based: CSS cannot react to a height comparison.
 */
if (!defined('MOD_ADMIN_PANEL') && function_exists('add_action')) {
	if (!function_exists('stringendo_wp_footer_overflow_widgets')) {
		function stringendo_wp_footer_overflow_widgets(): void {
			if (defined('MOD_ADMIN_PANEL')) {
				return;
			}
			if (!function_exists('theme_style_geturl')) {
				return;
			}
			$random_hex = RANDOM_HEX;
			$styleUrl = theme_style_geturl('stringendo');
			$jsRel = 'res/widgets-under-main.js';
			$jsUrl = $styleUrl . $jsRel;
			$jsUrl = function_exists('utils_asset_ver') ? utils_asset_ver($jsUrl, defined('SYSTEM_VER') ? (string)SYSTEM_VER : null) : $jsUrl;
			echo '
		<!-- BOF of Stingendo intelligent widget bar JS -->
		<script nonce="' . $random_hex . '" src="' . htmlspecialchars($jsUrl, ENT_QUOTES, 'UTF-8') . '" defer></script>
		<!-- EOF of Stingendo intelligent widget bar JS -->
			';
		}
	}

	/**
	 * Frontend UX enhancement: inertia/lerp wheel scrolling (desktop only).
	 * Prevents stutter from CSS smooth scrolling combined with JS scroll animations.
	 * The JS itself is defensive: it disables itself for bots, automation, reduced motion,
	 * touch devices and nested scrollable containers.
	 */
	if (!function_exists('stringendo_wp_head_smooth_scroll')) {
		function stringendo_wp_head_smooth_scroll(): void {
			if (defined('MOD_ADMIN_PANEL')) {
				return;
			}
			if (!function_exists('theme_style_geturl')) {
				return;
			}
			$random_hex = RANDOM_HEX;
			$styleUrl = theme_style_geturl('stringendo');
			$jsRel = 'res/smooth-scroll.js';
			$jsUrl = $styleUrl . $jsRel;
			$jsUrl = function_exists('utils_asset_ver') ? utils_asset_ver($jsUrl, defined('SYSTEM_VER') ? (string)SYSTEM_VER : null) : $jsUrl;
			echo '
		<!-- BOF of Stingendo smooth scroll JS -->
		<script nonce="' . $random_hex . '" src="' . htmlspecialchars($jsUrl, ENT_QUOTES, 'UTF-8') . '" defer></script>
		<!-- EOF of Stingendo smooth scroll JS -->
			';
		}
	}

	/**
	 * Frontend UX enhancement: subtle fade-in for #main first, then #column.
	 * Must not be enabled for search engines (avoid unnecessary animation in crawlers).
	 * Also respect reduced-motion preferences.
	 */
	if (!function_exists('stringendo_wp_head_fadein')) {
		function stringendo_wp_head_fadein(): void {
			if (defined('MOD_ADMIN_PANEL')) {
				return;
			}
			$random_hex = RANDOM_HEX;
			$styleUrl = theme_style_geturl('stringendo');
			$bgRel = 'imgs/bg.svg';
			$bgUrl = $styleUrl . $bgRel;
			$bgFs = (defined('ABS_PATH') ? ABS_PATH : '') . THEMES_DIR . THE_THEME . '/stringendo/' . $bgRel;

			echo '
		<!-- BOF of Stingendo fadein -->
		<link rel="preload" as="image" href="' . htmlspecialchars($bgUrl, ENT_QUOTES, 'UTF-8') . '" type="image/svg+xml" fetchpriority="high">
		<script nonce="' . $random_hex . '">
			/**
			 * Stingendo fadein init
			 *   - Sets the class fp-fadein-column-done after the fade-in of #column and fires the event stringendo:columnShown
			 *   - No effect for bots/search engines, as fp-fadein is only set for non-bots, as before.
			 */
			(function(){
				try {
					if (location && /\\badmin\\.php\\b/i.test(location.pathname || \'\')) {
						return;
					}
					var ua = (navigator.userAgent || \'\').toLowerCase();
					var isBot = /bot|crawl|spider|slurp|bingpreview|duckduckbot|yandex|baiduspider|sogou|exabot|facebot|ia_archiver|twitterbot|linkedinbot|pinterest|embedly/i.test(ua);
					if (isBot) {
						return;
					}
					if (window.matchMedia && window.matchMedia(\'(prefers-reduced-motion: reduce)\').matches) {
						return;
					}
					var root = document.documentElement;
					root.classList.add(\'fp-fadein\');
					window.setTimeout(function () {
						try {
							window.__stringendoColumnShown = true;
						} catch (e0) {}
						root.classList.add(\'fp-fadein-column-done\');
						try {
							window.dispatchEvent(new Event(\'stringendo:columnShown\'));
						} catch (e) {
							try {
								var evt = document.createEvent(\'Event\');
								evt.initEvent(\'stringendo:columnShown\', true, true);
								window.dispatchEvent(evt);
							} catch (e2) {}
						}
					}, 450);
				} catch (e) {}
			})();
		</script>
		<!-- EOF of Stingendo fadein -->
			';
		}
	}

	add_action('wp_head', 'stringendo_wp_head_fadein', 1);
	add_action('wp_head', 'stringendo_wp_head_smooth_scroll', 2);
	// Load early (wp_head) so the widget layout can be calculated before fade-in completes.
	add_action('wp_head', 'stringendo_wp_footer_overflow_widgets', 3);
}

$style ['style_def'] = 'style.css';
$style ['style_admin'] = 'admin.css';
$style ['style_print'] = 'print.css';
$style ['style'] = 'default';
?>
