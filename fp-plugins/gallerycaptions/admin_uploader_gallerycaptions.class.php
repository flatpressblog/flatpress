<?php

/**
 * The plugin's configuration panel
 *
 * @author zimmermann
 *        
 */
class admin_uploader_gallerycaptions extends AdminPanelAction {

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
		if (isset($_SESSION ['gallerycaptions-selectedgallery'])) {
			$currentGallery = $_SESSION ['gallerycaptions-selectedgallery'];
		}
		// current gallery's images
		$currentGalleryImages = gallery_read_images('images/' . $currentGallery);
		// current gallery's captions
		$captionsOfCurrentGallery = isset($currentGallery) ? gallery_read_captions('images/' . $currentGallery) : null;

		// now assign everything to the Smarty variables
		$this->smarty->assign('pluginurl', plugin_geturl('gallerycaptions'));
		$this->smarty->assign('galleries', $allGalleries);
		$this->smarty->assign('currentgallery', $currentGallery);
		$this->smarty->assign('currentgalleryimages', $currentGalleryImages);
		$this->smarty->assign('currentgallerycaptions', $captionsOfCurrentGallery);
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
			$_SESSION ['gallerycaptions-selectedgallery'] = $_REQUEST ['gallerycaptions-gallery'];
		} // Save captions button was pressed
		elseif (isset($_POST ['gallerycaptions-savecaptions'])) {
			gallery_write_captions($_REQUEST ['galleryname'], $_REQUEST ['captions']);
		}

		return 2;
	}

}

admin_addpanelaction('uploader', 'gallerycaptions', true);
