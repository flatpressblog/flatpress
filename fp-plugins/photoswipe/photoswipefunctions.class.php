<?php

/**
 * Static functions for the plugin.
 */
class PhotoSwipeFunctions {

	/**
	 * Class-wide flag that the PhotoSwipe overlay UI has already been initialized
	 *
	 * @var bool
	 */
	private static $photoswipeUiIsInitialized = false;

	/**
	 * Class-wide index counter for the shown images
	 *
	 * @var int
	 */
	private static $lastusedDataIndex = 0;

	/**
	 * Decode captions/titles that might already contain HTML entities.
	 *
	 * Captions created by some plugins are stored with entities (e.g. &amp;). If we escape again
	 * for attributes, this would result in &amp;amp; and the user would see "&amp;" in the caption.
	 *
	 * @param string $text
	 * @param string $charset
	 * @return string
	 */
	private static function decode_title_entities(string $text, string $charset): string {
		$decoded = $text;
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
	 * Callback function for [img] BBCode tag. Returns the HTML for a PhotoSwipe image,
	 * or a standard <img> element if popup="false" is set.
	 *
	 * @param string $action Action context, e.g. "validate" or "replace".
	 * @param array $attr the attributes given in the tag
	 * @param string|null $content Tag content (not used for [img], may be null).
	 * @param array|null $params Internal use (optional, usually null).
	 * @param mixed $node_object $node_object Internal use (optional, may be null or an object).
	 * @return string|bool HTML string or true if $action == "validate".
	 */
	static function getImageHtml($action, $attr, $content, $params, $node_object) {
		global $lang, $fp_config;

		if ($action == 'validate') {
			// not used for now
			return true;
		}

		// the name of the image - and its relative path
		$img = isset($attr ['default']) ? $attr ['default'] : '';
		if (empty($img)) {
			return $lang ['plugin'] ['photoswipe'] ['label_imagedoesntexist'];
		}

		// sanitize first
		if (strpos($img, '..') !== false) {
			return $lang ['plugin'] ['photoswipe'] ['label_imagedoesntexist'];
		}

		$imgPathRel = 'fp-content/' . $img;
		// check if given image path is a local path
		$imageIsLocal = bbcode_remap_url($imgPathRel);
		$imgUrl = $imageIsLocal ? BLOG_BASEURL . 'fp-content/' . $img : $img;

		// error message if local image file does not exist
		if ($imageIsLocal && !file_exists($imgPathRel)) {
			return $lang ['plugin'] ['photoswipe'] ['label_imagedoesntexist'] . ' ' . $img;
		}

		// Determine charset for entity decoding/encoding
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

		// image title will be empty - or the title from the tag attributes, if given
		$titleRaw = isset($attr['title']) ? (string)$attr['title'] : '';
		$titleDecoded = self::decode_title_entities($titleRaw, $charset);
		$titlePlain = str_replace(array("\r", "\n"), ' ', strip_tags($titleDecoded));
		$titleEscaped = htmlspecialchars($titlePlain, ENT_QUOTES | ENT_HTML5, $charset);

		// image may float, according the the given float attribute - if not given, use "nofloat" class
		$floatClasses = 'thumbnail nofloat';
		if (isset($attr ['float'])) {
			$floatClasses = 'float' . $attr ['float'];
		}

		// to get the HTML code for preview image, we use the Flatpress standard function do_bbcode_img()
		$attr ['title'] = $titlePlain;
		$previewHtml = do_bbcode_img(null, $attr, null, null, null);
		// but: we don't need the popup link surrounding the resulting <img> tag
		$matches = null;
		preg_match('/<img[^>]*/u', $previewHtml, $matches);
		$previewHtml = $matches [0];
		if ($previewHtml [strlen($previewHtml) - 1] === '/') {
			$previewHtml = substr($previewHtml, 0, strlen($previewHtml) - 1);
		}
		// add some additional attributes to the <img> tag and close it properly
		$previewHtml .= ' itemprop="thumbnail" title="' . $titleEscaped . '">';

		// PhotoSwipe needs to know the dimensions of the *full* image.
		// For local images, we can read this on the server side. For external URLs, we deliberately do NOT call getimagesize().
		$w = 0;
		$h = 0;

		if ($imageIsLocal) {
			$imgsize = @getimagesize($imgPathRel);
			if (is_array($imgsize) && isset($imgsize [0], $imgsize [1])) {
				$w = (int)$imgsize [0];
				$h = (int)$imgsize [1];
			}
		}

		// Fallback to explicitly provided width/height attributes (BBCode parameters).
		// If one or both remain 0, JavaScript will determine the natural dimensions at runtime.
		if ($w < 1 && isset($attr ['width']) && is_scalar($attr ['width'])) {
			$tmp = (string)$attr ['width'];
			if (preg_match('/^\d+$/', $tmp)) {
				$w = (int)$tmp;
			}
		}
		if ($h < 1 && isset($attr ['height']) && is_scalar($attr ['height'])) {
			$tmp = (string)$attr ['height'];
			if (preg_match('/^\d+$/', $tmp)) {
				$h = (int)$tmp;
			}
		}

		// Avoid half-known dimensions (e.g. width-only). Let JavaScript determine both dimensions.
		if ($w < 1 || $h < 1) {
			$w = 0;
			$h = 0;
		}

		$datasizeAttr = 'data-size="' . $w . 'x' . $h . '" ';

		// set max width of the figure according to the width attribute
		$styleAttr = isset($attr ['width']) ? ' style="width:' . $attr ['width'] . 'px" ' : ' ';

		// now lets assemble the whole HTML code - including the overlay HTML, if not inserted into the DOM before
		$imgHtml = "\n\n" . //
		'<div ' . //
		'class="photoswipe ' . $floatClasses . '"' . $styleAttr . //
		'itemscope itemtype="http://schema.org/ImageGallery"' . //
		'>' . //
		'<figure ' . //
		'itemprop="associatedMedia" ' . //
		'itemscope ' . //
		'itemtype="http://schema.org/ImageObject" ' . //
		'data-index="' . self::$lastusedDataIndex . '" ' . //
		'class="' . $floatClasses . '"' . //
		'>' . //
		'<a ' . //
		'href="' . $imgUrl . '" ' . //
		'itemprop="contentUrl" ' . //
		$datasizeAttr . //
		'data-index="' . self::$lastusedDataIndex . '" ' . //
		'title="' . $titleEscaped . '"' . //
		'>' . //
		$previewHtml . //
		'</a>' . //
		'<figcaption' . $styleAttr . '>' . $titleEscaped . '</figcaption>' . //
		'</figure>' . //
		'</div>' . //
		"\n\n";

		self::$lastusedDataIndex++;

		// If popup=“false”, do NOT use the PhotoSwipe wrapper
		if (isset($attr ['popup']) && strtolower($attr ['popup']) === 'false') {
			// Popup explicitly deactivated
			$attr ['popup'] = false;

			// image HTML generated without PhotoSwipe functions by the default BBCode plugin
			$previewHtml = do_bbcode_img(null, $attr, null, null, null);

			// Extract <img ...> only
			preg_match('/<img[^>]*>/u', $previewHtml, $matches);
			$imgTag = $matches[0] ?? '';

			// Link around it, if available
			if (!empty($attr ['link'])) {
				$imgTag = '<a href="' . htmlspecialchars($attr ['link']) . '">' . $imgTag . '</a>';
			}

			// Standard behavior with BBCode
			return $imgTag;
		}

		// Standard behavior with PhotoSwipe
		return $imgHtml;
	}

	/**
	 * Callback function called for [gallery] tags which returns the HTML code for a PhotoSwipe gallery.
	 *
	 * @param string $action Action context, e.g. "validate" or "replace".
	 * @param array $attr Parsed tag attributes (e.g. 'default' for the gallery folder).
	 * @param string|null $content Tag content (not used for [gallery], may be null).
	 * @param array|null $params Internal use (usually null).
	 * @param mixed $node_object Internal use (optional, may be null or an object).
	 * @return string|bool HTML string with gallery markup, or true if $action == "validate".
	 */
	static function getGalleryHtml($action, $attr, $content, $params, $node_object) {
		global $lang;
		if ($action == 'validate') {
			// not used for now
			return true;
		}

		// gallery dir is set as tag attribute
		$dir = isset($attr ['default']) ? $attr ['default'] : '';
		if (empty($dir)) {
			return $lang ['plugin'] ['photoswipe'] ['label_gallerydoesntexist'];
		}

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
	 * Returns the HTML for the PhotoSwipe overlay container.
	 * This overlay must only be present once per page. On the first call,
	 * it returns the complete HTML markup; subsequent calls return an empty string.
	 *
	 * @return string HTML markup for the PhotoSwipe overlay (or empty string if already initialized)
	 */
	static function getPhotoSwipeOverlay() {
		global $lang;

		$photoswipeoverlay = self::$photoswipeUiIsInitialized ? '' : '<div class="pswp" tabindex="-1" role="dialog" aria-modal="true">' . //
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
	 * Outputs the required <script> and <link> tags for PhotoSwipe.
	 *
	 * This method should be called in the HTML <head> section to load
	 * PhotoSwipe CSS resources. It also includes jQuery
	 * if not already present.
	 *
	 * Uses a nonce for CSP compatibility.
	 *
	 * @return void
	 */
	static function pswpHead() {
		$random_hex = RANDOM_HEX;
		$pdir = plugin_geturl('photoswipe');
		$jQueryOldJs = utils_asset_ver($pdir . 'res/jquery-2.2.2/jquery-2.2.2.min.js', SYSTEM_VER);
		$pswpUiJs = utils_asset_ver($pdir . 'res/photoswipe-4.1.3/photoswipe-ui-default.js', SYSTEM_VER);
		$pswpJs = utils_asset_ver($pdir . 'res/photoswipe-4.1.3/photoswipe.js', SYSTEM_VER);
		$pswpSkinCss = utils_asset_ver($pdir . 'res/photoswipe-4.1.3/default-skin/default-skin.css', SYSTEM_VER);
		$pswpCss = utils_asset_ver($pdir . 'res/photoswipe-4.1.3/photoswipe.css', SYSTEM_VER);
		echo '
		<!-- BOF PhotoSwipe head -->';
		if (!function_exists('plugin_jquery_head')) {
			echo '<script nonce="' . $random_hex . '" src="' . $jQueryOldJs . '"></script>
		';
		}
		echo '
		<script nonce="' . $random_hex . '" src="' . $pswpUiJs . '" defer></script>
		<script nonce="' . $random_hex . '" src="' . $pswpJs . '" defer></script>
		<link rel="stylesheet" property="stylesheet" href="' . $pswpSkinCss . '">
		<link media="screen" href="' . $pswpCss . '" type="text/css" rel="stylesheet">
		<!-- EOF PhotoSwipe head -->
		';
	}

	/**
	 * Outputs the required <script> for PhotoSwipe.
	 *
	 * This method should be called in the HTML Footer section to load
	 * PhotoSwipe JavaScript resources.
	 *
	 * Uses a nonce for CSP compatibility.
	 *
	 * @return void
	 */
	static function pswpFooter() {
		echo '
		<!-- BOF PhotoSwipe footer -->
		';

		// Outputs the Overlay-Container
		echo self::getPhotoSwipeOverlay();

		$random_hex = RANDOM_HEX;
		$pdir = plugin_geturl('photoswipe');
		echo '
		<script nonce="' . $random_hex . '">';
		include_once (dirname(__FILE__) . '/res/photoswipe.js.php');
		echo '
		</script>
		<!-- EOF PhotoSwipe footer -->';
	}

	/**
	 * Registers the PhotoSwipe-specific BBCode tags ([img], [gallery], etc.).
	 *
	 * This method hooks into the FlatPress BBCode system to override or add
	 * custom BBCode tags used by the PhotoSwipe plugin:
	 *
	 * - [img] or [photoswipeimage]: single image with optional zoom overlay
	 * - [gallery] or [photoswipegallery]: auto-generated gallery from a folder
	 *
	 * @return void
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
?>
