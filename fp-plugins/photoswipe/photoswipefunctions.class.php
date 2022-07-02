<?php

/**
 * Static functions for the plugin.
 */
class PhotoSwipeFunctions {

	/**
	 * Class-wide flag that the PhotoSwipe overlay UI has already been initialized
	 *
	 * @var boolean
	 */
	private static $photoswipeUiIsInitialized = false;

	/**
	 * Class-wide index counter for the shown images
	 *
	 * @var int
	 */
	private static $lastusedDataIndex = 0;

	/**
	 * Callback function called for [img] tags which returns the HTML code for a single PhotoSwipe image.
	 *
	 * @param string $action
	 * @param array $attr
	 *        	the attributes given in the tag
	 * @param string $content
	 * @param array $params
	 * @param string $node_object
	 * @return boolean|string the HTML code for a single PhotoSwipe image
	 */
	static function getImageHtml($action, $attr, $content, $params, $node_object) {
		global $lang;

		if ($action == 'validate') {
			// not used for now
			return true;
		}

		// the name of the image - and its relative path
		$img = $attr ['default'];

		// sanitize first
		if (strpos($img, '..') !== false) {
			return $lang ['plugin'] ['photoswipe'] ['label_imagedoesntexist'];
		}
		$imgPathRel = 'fp-content/' . $img;
		$imgUrl = BLOG_BASEURL . 'fp-content/' . $img;

		// check if dir exists
		if (!file_exists($imgPathRel)) {
			return $lang ['plugin'] ['photoswipe'] ['label_imagedoesntexist'] . ' ' . $img;
		}

		// image title will be its file name - or the title from the tag attributes, if given
		$title = isset($attr ['title']) ? $attr ['title'] : '';
		// for usage in HTML attributes, we need to remove quotes and HTML tags from the title
		$titleForAttributes = isset($attr ['title']) ? htmlentities(strip_tags($attr ['title'])) : '';

		// image may float, according the the given float attribute - if not given, use "nofloat" class
		$floatClasses = 'thumbnail nofloat';
		if (isset($attr ['float'])) {
			$floatClasses = 'float' . $attr ['float'];
		}

		// to get the HTML code for preview image, we use the Flatpress standard function do_bbcode_img()
		$attr ['title'] = $titleForAttributes;
		$previewHtml = do_bbcode_img(null, $attr, null, null, null);
		// but: we don't need the popup link surrounding the resulting <img> tag
		$matches = null;
		preg_match('/<img[^>]*/u', $previewHtml, $matches);
		$previewHtml = $matches [0];
		if ($previewHtml [strlen($previewHtml) - 1] === '/') {
			$previewHtml = substr($previewHtml, 0, strlen($previewHtml) - 1);
		}
		// add some additional attributes to the <img> tag and close it properly
		$previewHtml .= ' itemprop="thumbnail" title="' . $titleForAttributes . '">';

		// PhotoSwipe needs to know the dimensions of the image - so we read them
		$imgsize = @getimagesize($imgPathRel);
		$datasizeAttr = ($imgsize === false) ? '' : 'data-size="' . $imgsize [0] . 'x' . $imgsize [1] . '" ';

		// set max width of the figure according to the width attribute
		$styleAttr = isset($attr ['width']) ? ' style="width:' . $attr ['width'] . 'px" ' : '';

		// now lets assemble the whole HTML code - including the overlay HTML, if not inserted into the DOM before
		$imgHtml = self::getPhotoSwipeOverlay() . //
		'<div ' . //
		'class="photoswipe ' . $floatClasses . '"' . $styleAttr . //
		'>' . //
		'<figure ' . //
		'itemprop="associatedMedia" ' . //
		'itemscope ' . //
		'itemtype="http://schema.org/ImageObject" ' . //
		'data-index="' . self::$lastusedDataIndex . '" ' . //
		'class="' . $floatClasses . '" ' . //
		'>' . //
		'<a ' . //
		'href="' . $imgUrl . '" ' . //
		'itemprop="contentUrl" ' . //
		$datasizeAttr . //
		'data-index="' . self::$lastusedDataIndex . '" ' . //
		'title="' . htmlentities($title) . '" ' . //
		'>' . //
		$previewHtml . //
		'</a>' . //
		'<figcaption' . $styleAttr . '>' . $title . '</figcaption>' . //
		'</figure>' . //
		'</div>';

		self::$lastusedDataIndex++;
		return $imgHtml;
	}

	/**
	 * Callback function called for [gallery] tags which returns the HTML code for a PhotoSwipe gallery.
	 *
	 * @param string $action
	 * @param array $attr
	 *        	the attributes given in the tag
	 * @param string $content
	 * @param array $params
	 * @param string $node_object
	 * @return boolean|string the HTML code for a PhotoSwipe gallery
	 */
	static function getGalleryHtml($action, $attr, $content, $params, $node_object) {
		global $lang;
		if ($action == 'validate') {
			// not used for now
			return true;
		}

		// gallery dir is set as tag attribute
		$dir = $attr ['default'];

		// sanitize first
		if (strpos($dir, '..') !== false) {
			return $lang ['plugin'] ['photoswipe'] ['label_gallerydoesntexist'];
		}
		// check if dir exists
		if (!file_exists("fp-content/" . $dir)) {
			return $lang ['plugin'] ['photoswipe'] ['label_gallerydoesntexist'] . ' ' . $dir;
		}
		// force slash at the end
		if (substr($dir, -1) != '/') {
			$dir .= '/';
		}

		// read images from gallery directory
		$imagefiles = gallery_read_images($dir);

		// read image caption from captions file (if existant in the gallery directory)
		$captions = gallery_read_captions($dir);

		// call getImageHtml() to get the image's HTML code for each image in the gallery dir
		$imgattr = $attr;
		$str = '<div class="img-gallery ' . sanitize_title($dir) . '">';
		foreach ($imagefiles as $f) {
			// set the image's caption as title attribut of the img tag
			$imgattr ['default'] = $dir . $f;
			$imgattr ['title'] = array_key_exists($f, $captions) ? $captions [$f] : '';
			$str .= self::getImageHtml($action, $imgattr, $content, $params, $node_object);
		}
		return $str . '</div>';
	}

	/**
	 * Returns the overlay HTML.
	 * This needs to be inserted into the DOM only once, so a second call will return an empty string.
	 *
	 * @return string the PhotoSwipe overlay HTML
	 */
	static function getPhotoSwipeOverlay() {
		global $lang;

		$photoswipeoverlay = self::$photoswipeUiIsInitialized ? '' : '<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">' . //
		'<div class="pswp__bg"></div>' . //
		'<div class="pswp__scroll-wrap">' . //
		'<div class="pswp__container">' . //
		'<div class="pswp__item"></div>' . //
		'<div class="pswp__item"></div>' . //
		'<div class="pswp__item">' . //
		'</div>' . //
		'</div>' . //
		'<div class="pswp__ui pswp__ui--hidden">' . //
		'<div class="pswp__top-bar">' . //
		'<div class="pswp__counter"></div>' . //
		'<button class="pswp__button pswp__button--close" title="' . $lang ['plugin'] ['photoswipe'] ['tooltip_closebutton'] . '"></button>' . //
		'<button class="pswp__button pswp__button--share" title="' . $lang ['plugin'] ['photoswipe'] ['tooltip_sharebutton'] . '"></button>' . //
		'<button class="pswp__button pswp__button--fs" title="' . $lang ['plugin'] ['photoswipe'] ['tooltip_fullscreenbutton'] . '"></button>' . //
		'<button class="pswp__button pswp__button--zoom" title="' . $lang ['plugin'] ['photoswipe'] ['tooltip_zoombutton'] . '"></button>' . //
		'<div class="pswp__preloader">' . //
		'<div class="pswp__preloader__icn">' . //
		'<div class="pswp__preloader__cut">' . //
		'<div class="pswp__preloader__donut"></div>' . //
		'</div>' . //
		'</div>' . //
		'</div>' . //
		'</div>' . //
		'<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">' . //
		'<div class="pswp__share-tooltip"></div>' . //
		'</div>' . //
		'<button class="pswp__button pswp__button--arrow--left" title="' . $lang ['plugin'] ['photoswipe'] ['tooltip_prevbutton'] . '"></button>' . //
		'<button class="pswp__button pswp__button--arrow--right" title="' . $lang ['plugin'] ['photoswipe'] ['tooltip_nextbutton'] . '"></button>' . //
		'<div class="pswp__caption">' . //
		'<div class="pswp__caption__center"></div>' . //
		'</div>' . //
		'</div>' . //
		'</div>' . //
		'</div>';
		self::$photoswipeUiIsInitialized = true;
		return $photoswipeoverlay;
	}

	/**
	 * Header hook for loading the PhotoSwipe scripts.
	 * Echoes the <script> tags.
	 */
	static function echoScriptTags() {
		$pdir = plugin_geturl('photoswipe');
		echo '<!-- PhotoSwipe -->
';
		if (!function_exists('plugin_jquery_head')) {
			echo '<script src="' . $pdir . 'res/jquery-2.2.2/jquery-2.2.2.min.js"></script>
';
		}
		echo '
	<script src="' . $pdir . 'res/photoswipe-4.1.1/photoswipe-ui-default.min.js"></script>
	<script src="' . $pdir . 'res/photoswipe-4.1.1/photoswipe.min.js"></script>
	<script>';
		include_once (dirname(__FILE__) . '/res/photoswipe.js.php');
		echo '
	</script>
	<link rel="stylesheet" property="stylesheet" href="' . $pdir . 'res/photoswipe-4.1.1/default-skin/default-skin.css">
	<link media="screen" href="' . $pdir . 'res/photoswipe-4.1.1/photoswipe.css" type="text/css" rel="stylesheet">
	<!-- /PhotoSwipe -->';
	}

	/**
	 * Initializes the BBCode tags of the plugin.
	 */
	public static function initializePluginTags() {
		// At first: check if BBCode plugin is active
		if (!function_exists('plugin_bbcode_init')) {
			// if not, there's no use in adding any bbcode tags :)
			return;
		}

		// get the global bbcode object
		$bbcode = plugin_bbcode_init();

		// gallery tags
		$supportedGalleryTags = array(
			'gallery', // default tag
			'photoswipegallery' // legacy tag - maintaining compatibility with plugin versions <= 1.1
		);
		foreach ($supportedGalleryTags as $tag) {
			// add gallery tag
			$bbcode->addCode($tag, // tag name: this will go between square brackets
			'callback_replace_single', // type of action: we'll use a callback function
			'PhotoSwipeFunctions::getGalleryHtml', // name of the callback function
			array(
				'usecontent_param' => array(
					'default'
				)
			), // supported parameters: "default" is [acronym=valore]
			'inline', // type of the tag, inline or block, etc
			array(
				'listitem',
				'block',
				'inline',
				'link'
			), // type of elements in which you can use this tag
			array()); // type of elements where this tag CAN'T go (in this case, none, so it can go everywhere)
			$bbcode->setCodeFlag($tag, 'closetag', BBCODE_CLOSETAG_FORBIDDEN); // a closing tag is forbidden (no [/tag])
		}

		// single image tags
		$supportedImageTags = array(
			'img', // default tag
			'photoswipeimage' // legacy tag - maintaining compatibility with plugin versions <= 1.1
		);
		foreach ($supportedImageTags as $tag) {
			// at first: remove tag to make sure it will be overridden
			$bbcode->removeCode($tag);
			// add image tag
			$bbcode->addCode($tag, // tag name: this will go between square brackets
			'callback_replace_single', // type of action: we'll use a callback function
			'PhotoSwipeFunctions::getImageHtml', // name of the callback function
			array(
				'usecontent_param' => array(
					'default'
				)
			), // supported parameters: "default" is [acronym=valore]
			'inline', // type of the tag, inline or block, etc
			array(
				'listitem',
				'block',
				'inline',
				'link'
			), // type of elements in which you can use this tag
			array()); // type of elements where this tag CAN'T go (in this case, none, so it can go everywhere)
			$bbcode->setCodeFlag($tag, 'closetag', BBCODE_CLOSETAG_FORBIDDEN); // a closing tag is forbidden (no [/tag])
		}
	}

} 