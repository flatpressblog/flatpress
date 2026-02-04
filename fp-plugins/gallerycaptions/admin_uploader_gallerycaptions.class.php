<?php

/**
 * The plugin's configuration panel
 *
 * @author zimmermann
 *        
 */
class admin_uploader_gallerycaptions extends AdminPanelAction {

	/**
	 * Determine the charset used for HTML entity encoding/decoding.
	 *
	 * @return string
	 */
	private function get_charset(): string {
		global $fp_config;
		$charset = 'UTF-8';
		if (isset($fp_config) && is_array($fp_config)) {
			$locale = $fp_config ['locale'] ?? null;
			if (is_array($locale)) {
				$tmp = $locale ['charset'] ?? null;
				if (is_string($tmp) && $tmp !== '') {
					$charset = strtoupper($tmp);
				}
			}
		}
		return $charset;
	}

	/**
	 * Decode captions that were previously stored with HTML entities.
	 *
	 * This prevents double-escaping when templates escape again (e.g. &amp;amp; -> &amp;).
	 * We decode up to a small fixed number of iterations to also recover from already
	 * double-encoded legacy data.
	 *
	 * @param string $caption
	 * @return string
	 */
	private function decode_caption_entities(string $caption): string {
		$charset = $this->get_charset();
		$decoded = $caption;
		for ($i = 0; $i < 2; $i++) {
			$tmp = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, $charset);
			if ($tmp === $decoded) {
				break;
			}
			$decoded = $tmp;
		}
		return $decoded;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see AdminPanelAction::setup()
	 */
	function setup() {
		// assign this class as resource for the admin area
		$this->smarty->assign('admin_resource', "plugin:gallerycaptions/admin.plugin.gallerycaptions");

		// fetch all gallery names from the image directory
		$allGalleries = gallery_fetch_galleries();

		// current gallery name
		$currentGallery = null;
		// user has selected a gallery already
		if (isset($_SESSION ['gallerycaptions-selectedgallery']) && is_string($_SESSION ['gallerycaptions-selectedgallery'])) {
			$tmp = trim($_SESSION ['gallerycaptions-selectedgallery']);
			if ($tmp !== '') {
				$currentGallery = $tmp;
			}
		}

		$currentGalleryImages = array();
		// current gallery's captions
		$captionsOfCurrentGallery = array();
		if (is_string($currentGallery) && $currentGallery !== '') {
			// current gallery's images
			$currentGalleryImages = gallery_read_images('images/' . $currentGallery);
			$captionsOfCurrentGallery = gallery_read_captions('images/' . $currentGallery);

			// Decode stored captions for display (prevents double-escaping in templates)
			foreach ($captionsOfCurrentGallery as $filename => $caption) {
				$captionsOfCurrentGallery [$filename] = $this->decode_caption_entities((string)$caption);
			}
		}

		// now assign everything to the Smarty variables
		$this->smarty->assign('pluginurl', plugin_geturl('gallerycaptions'));
		$this->smarty->assign('galleries', $allGalleries);
		$this->smarty->assign('currentgallery', $currentGallery);
		$this->smarty->assign('currentgalleryimages', $currentGalleryImages);
		$this->smarty->assign('currentgallerycaptions', $captionsOfCurrentGallery);
	}

	/**
	 * Sanitizes a single caption to prevent XSS
	 *
	 * @param string $caption
	 * @return string
	 */
	private function sanitize_caption(string $caption): string {
		$charset = $this->get_charset();
		return htmlspecialchars($caption, ENT_QUOTES | ENT_HTML5, $charset);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see AdminPanelAction::onsubmit()
	 */
	function onsubmit($data = null) {

		// Gallery select button was pressed
		if (array_key_exists('gallerycaptions-selectgallery', $_REQUEST)) {
			// set selected gallery to the session
			if (isset($_REQUEST ['gallerycaptions-gallery']) && is_string($_REQUEST ['gallerycaptions-gallery'])) {
				$_SESSION ['gallerycaptions-selectedgallery'] = $_REQUEST ['gallerycaptions-gallery'];
			}
		} // Save captions button was pressed
		elseif (isset($_POST ['gallerycaptions-savecaptions'])) {
			$rawCaptions = $_REQUEST ['captions'] ?? array();
			$sanitizedCaptions = [];

			// Sanitize all captions
			if (is_array($rawCaptions)) {
				foreach ($rawCaptions as $filename => $caption) {
					$sanitizedCaptions [(string)$filename] = $this->sanitize_caption(is_scalar($caption) ? (string)$caption : '');
				}
			}

			// Save sanitized captions
			$galleryName = $_REQUEST ['galleryname'] ?? '';
			if (is_string($galleryName) && $galleryName !== '') {
				gallery_write_captions($galleryName, $sanitizedCaptions);
			}
		}

		return 2;
	}

}

admin_addpanelaction('uploader', 'gallerycaptions', true);
?>
