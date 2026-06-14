<?php

/**
 * The main class of the plugin.
 * All the magic happens here.
 */
class AudioVideoPlugin {

	/**
	 * Callback function called for [audioplayer] tags which returns the HTML code for an audio player.
	 *
	 * @param mixed $action
	 * @param array $attr
	 *        	the attributes given in the tag
	 * @param mixed $content
	 * @param mixed $params
	 * @param mixed $node_object
	 * @return boolean|string the HTML code for an audio player
	 */
	static function getAudioHtml($action, $attr, $content, $params, $node_object) {
		if ($action == 'validate') {
			// not used for now
			return true;
		}
		return self::getPlayerHtml('audio', is_array($attr) ? $attr : array(), $content);
	}

	// getAudioHtml()

	/**
	 * Callback function called for [videoplayer] tags which returns the HTML code for a video player.
	 *
	 * @param mixed $action
	 * @param array $attr
	 *        	the attributes given in the tag
	 * @param mixed $content
	 * @param mixed $params
	 * @param mixed $node_object
	 * @return boolean|string the HTML code for a video player
	 */
	static function getVideoHtml($action, $attr, $content, $params, $node_object) {
		if ($action == 'validate') {
			return true;
		}
		return self::getPlayerHtml('video', is_array($attr) ? $attr : array(), $content);
	}

	// getVideoHtml()

	/**
	 * Generates the HTML code for a media player element.
	 *
	 * @param string $mediatypeToUse
	 *        	the media type to use ("audio" or "video")
	 * @param array $attr
	 *        	the attributes given in the tag
	 * @param mixed $content
	 *        	optional description between opening and closing BBCode tags
	 * @return string the HTML code for a media player element (or an error message if something goes wrong)
	 */
	private static function getPlayerHtml($mediatypeToUse, $attr, $content = '') {
		// check given file path
		$file = isset($attr ['default']) ? $attr ['default'] : '';
		try {
			$filePathRel = self::getFilePath($file, $mediatypeToUse);
		} catch (\Exception $e) {
			return self::escapeHtml($e->getMessage());
		}
		$filePathAbs = self::getAbsolutePath($filePathRel);
		$fileUrl = self::getFileUrl($filePathRel);
		$mimeType = self::getMimeType($filePathAbs);
		if (!self::mimeMatchesPlayer($mimeType, $mediatypeToUse, $filePathAbs)) {
			return self::escapeHtml('Given file is not a supported ' . $mediatypeToUse . ' file.');
		}

		// Show control elements? Set by default
		$controls = !isset($attr ['controls']) || self::isTrue($attr ['controls']) ? ' controls' : '';
		// Autoplay on load? Not set by default
		$autoplay = isset($attr ['autoplay']) && self::isTrue($attr ['autoplay']) ? ' autoplay' : '';
		// Loop play? Not set by default
		$loop = isset($attr ['loop']) && self::isTrue($attr ['loop']) ? ' loop' : '';
		$description = self::getDescription($content, $attr);
		$descriptionAttr = $description !== '' ? ' title="' . self::escapeHtml($description) . '" aria-label="' . self::escapeHtml($description) . '"' : '';
		$fallbackDescription = $description !== '' ? self::escapeHtml($description) . ' ' : '';
		// pre-initialize video attributes
		$poster = $width = $height = '';

		// now the options for video elements only
		if ($mediatypeToUse == 'video') {
			// Which image file to set as poster? Not set by default
			try {
				if (isset($attr ['poster']) && trim((string) $attr ['poster']) !== '') {
					$posterPathRel = self::getFilePath($attr ['poster'], 'image');
					$poster = ' poster="' . self::escapeHtml(self::getFileUrl($posterPathRel)) . '"';
				}
			} catch (\Exception $e) {
				return self::escapeHtml($e->getMessage());
			}

			// Custom width and height
			if (isset($attr ['width']) && is_numeric($attr ['width']) && (int) $attr ['width'] > 0) {
				$width = ' width="' . (int) $attr ['width'] . '"';
			}
			if (isset($attr ['height']) && is_numeric($attr ['height']) && (int) $attr ['height'] > 0) {
				$height = ' height="' . (int) $attr ['height'] . '"';
			}
		}

		$html = '<' . $mediatypeToUse . ' class="audiovideo"' . $controls . $autoplay . $loop . $poster . $width . $height . $descriptionAttr . '>' . //
			'<source src="' . self::escapeHtml($fileUrl) . '" type="' . self::escapeHtml($mimeType) . '">' . //
			$fallbackDescription . '<a href="' . self::escapeHtml($fileUrl) . '">No description available.</a>' . //
			'</' . $mediatypeToUse . '>';
		return $html;
	}

	/**
	 * Returns the optional media description from BBCode content or legacy attributes.
	 *
	 * @param mixed $content
	 * @param array $attr
	 * @return string
	 */
	private static function getDescription($content, $attr) {
		$description = self::normalizeDescription($content);
		if ($description !== '') {
			return $description;
		}
		foreach (array('description', 'title', 'alt') as $key) {
			if (isset($attr [$key])) {
				$description = self::normalizeDescription($attr [$key]);
				if ($description !== '') {
					return $description;
				}
			}
		}
		return '';
	}

	/**
	 * Normalize user-provided description text for HTML attributes and fallback text.
	 *
	 * @param mixed $value
	 * @return string
	 */
	private static function normalizeDescription($value) {
		if (!is_scalar($value)) {
			return '';
		}
		$text = trim((string) $value);
		if ($text === '') {
			return '';
		}
		$text = strip_tags($text);
		$withoutBbcode = preg_replace('/\[[^\]]+\]/u', ' ', $text);
		if (is_string($withoutBbcode)) {
			$text = $withoutBbcode;
		}
		$text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$normalized = preg_replace('/\s+/u', ' ', $text);
		if (is_string($normalized)) {
			$text = $normalized;
		}
		return trim($text);
	}

	/**
	 * Converts paired AudioVideo tags with description text into single tags before the BBCode tree is built.
	 *
	 * Optional closing tags cannot be handled safely by StringParser_BBCode when old single tags of the
	 * same name appear before a paired tag. A prefilter keeps legacy single tags independent and still
	 * lets the player callback receive the description through the attributes.
	 *
	 * @param mixed $text
	 * @return string
	 */
	public static function prepareDescriptionTags($text) {
		$text = (string) $text;
		foreach (array('audioplayer', 'videoplayer') as $tagName) {
			$pattern = '~\[\s*' . $tagName . '\b([^\]]*)\]((?:(?!\[\s*/?\s*' . $tagName . '\b).)*?)\[\s*/\s*' . $tagName . '\s*\]~isu';
			$updated = preg_replace_callback(
				$pattern,
				function ($matches) use ($tagName) {
					$attrText = rtrim((string) $matches [1]);
					$description = AudioVideoPlugin::normalizeDescription($matches [2]);
					if ($description === '') {
						return '[' . $tagName . $attrText . ']';
					}
					$attrText = AudioVideoPlugin::removeDescriptionAttribute($attrText);
					return '[' . $tagName . $attrText . ' description="' . AudioVideoPlugin::escapeBbcodeAttribute($description) . '"]';
				},
				$text
			);
			if (is_string($updated)) {
				$text = $updated;
			}
		}
		return $text;
	}

	/**
	 * Remove an existing description attribute so paired-tag content can take precedence.
	 *
	 * @param string $attrText
	 * @return string
	 */
	private static function removeDescriptionAttribute($attrText) {
		$updated = preg_replace('/\s+description\s*=\s*("([^"]*)"|\'([^\']*)\'|[^\s\]]+)/iu', '', (string) $attrText);
		return is_string($updated) ? rtrim($updated) : rtrim((string) $attrText);
	}

	/**
	 * Escape a description for use inside a BBCode attribute.
	 *
	 * @param string $value
	 * @return string
	 */
	private static function escapeBbcodeAttribute($value) {
		$value = str_replace(array("\r", "\n"), ' ', (string) $value);
		$value = trim($value);
		return str_replace(array('"', '[', ']'), array('&quot;', '(', ')'), $value);
	}

	/**
	 * Escapes a value for use in HTML text and attributes.
	 *
	 * @param mixed $value
	 * @return string
	 */
	private static function escapeHtml($value) {
		return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	/**
	 * Returns an absolute filesystem path for a relative FlatPress path.
	 *
	 * @param string $filePathRel
	 * @return string
	 */
	private static function getAbsolutePath($filePathRel) {
		return (defined('ABS_PATH') ? ABS_PATH : getcwd() . DIRECTORY_SEPARATOR) . $filePathRel;
	}

	/**
	 * Encode a FlatPress-relative path for use as a public URL.
	 *
	 * @param string $path
	 * @return string
	 */
	private static function encodeUrlPath($path) {
		$segments = explode('/', str_replace('\\', '/', (string) $path));
		foreach ($segments as $index => $segment) {
			$segments [$index] = rawurlencode($segment);
		}
		return implode('/', $segments);
	}

	/**
	 * Returns the public URL for a relative FlatPress path.
	 *
	 * @param string $filePathRel
	 * @return string
	 */
	private static function getFileUrl($filePathRel) {
		return (defined('BLOG_BASEURL') ? BLOG_BASEURL : '') . self::encodeUrlPath($filePathRel);
	}

	/**
	 * Returns a MIME type without requiring fileinfo to be available.
	 *
	 * @param string $filePathAbs
	 * @return string
	 */
	private static function getMimeType($filePathAbs) {
		$fileInfoMime = '';
		if (function_exists('mime_content_type')) {
			$mimeType = @mime_content_type($filePathAbs);
			if (is_string($mimeType) && $mimeType !== '' && $mimeType !== 'application/octet-stream') {
				$fileInfoMime = strtolower(trim($mimeType));
				if (strpos($fileInfoMime, 'audio/') === 0 || strpos($fileInfoMime, 'video/') === 0 || strpos($fileInfoMime, 'image/') === 0) {
					return $fileInfoMime;
				}
			}
		}

		$extension = strtolower((string) pathinfo($filePathAbs, PATHINFO_EXTENSION));
		$map = array(
			'mp3' => 'audio/mpeg',
			'wav' => 'audio/wav',
			'wave' => 'audio/wave',
			'ogg' => 'audio/ogg',
			'oga' => 'audio/ogg',
			'opus' => 'audio/ogg',
			'weba' => 'audio/webm',
			'flac' => 'audio/flac',
			'aac' => 'audio/aac',
			'm4a' => 'audio/m4a',
			'mp4' => 'video/mp4',
			'm4v' => 'video/mp4',
			'mov' => 'video/quicktime',
			'webm' => 'video/webm',
			'ogv' => 'video/ogg',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif',
			'webp' => 'image/webp',
			'avif' => 'image/avif'
		);
		return isset($map [$extension]) ? $map [$extension] : ($fileInfoMime !== '' ? $fileInfoMime : 'application/octet-stream');
	}

	/**
	 * Check whether a MIME type is usable by the requested HTML media element.
	 *
	 * @param string $mimeType
	 * @param string $playerType
	 * @param string $path
	 * @return bool
	 */
	private static function mimeMatchesPlayer($mimeType, $playerType, $path) {
		$mimeType = strtolower((string) $mimeType);
		if ($playerType === 'audio') {
			return strpos($mimeType, 'audio/') === 0;
		}
		if ($playerType === 'video') {
			return strpos($mimeType, 'video/') === 0;
		}
		if ($playerType === 'image') {
			return strpos($mimeType, 'image/') === 0;
		}
		return false;
	}

	// getPlayerHtml()

	/**
	 * Checks if the given value means "true".
	 *
	 * @param mixed $val
	 *        	the value to be checked
	 * @return boolean <code>true</code>, if the given value means "true"; otherwise <code>false</code>
	 */
	private static function isTrue($val) {
		if (is_bool($val)) {
			return $val;
		}
		if (is_scalar($val)) {
			switch (strtolower(trim((string) $val))) {
				case "true":
				case "1":
				case "yes":
				case "ja":
				case "si":
					return true;
				default:
					return false;
			}
		}
		return false;
	}

	// isTrue()

	/**
	 * Returns the relative path of the given local media file.
	 *
	 * @param string $file
	 *        	the path to validate
	 * @param string $expectedType
	 * @throws Exception if the path is invalid
	 * @return string the relative path
	 */
	private static function getFilePath($file, $expectedType = '') {
		// prevent path traversal
		if (!is_scalar($file)) {
			throw new Exception('Invalid file given');
		}
		$file = str_replace('\\', '/', trim((string) $file));
		if ($file === '') {
			throw new Exception('No file given');
		}
		if (preg_match('!^(?:https?:)?//!i', $file) || preg_match('!^[a-z][a-z0-9+.-]*:!i', $file)) {
			throw new Exception('Only local FlatPress media files are supported.');
		}
		$file = ltrim($file, '/');
		if (strpos($file, 'fp-content/') === 0) {
			$file = substr($file, strlen('fp-content/'));
		}
		$parts = explode('/', $file);
		foreach ($parts as $part) {
			if ($part === '' || $part === '.' || $part === '..') {
				throw new Exception('Invalid file given');
			}
		}

		// check if file exists
		$filePathRel = 'fp-content/' . $file;
		$filePathAbs = self::getAbsolutePath($filePathRel);
		if (!is_file($filePathAbs)) {
			throw new Exception('Given file does not exist.');
		}
		if ($expectedType !== '' && !self::mimeMatchesPlayer(self::getMimeType($filePathAbs), $expectedType, $filePathAbs)) {
			throw new Exception('Given file is not a supported ' . $expectedType . ' file.');
		}
		return $filePathRel;
	}

	// getFilePath()

	/**
	 * Initializes the BBCode tags of the plugin.
	 */
	public static function initializePluginTags() {
		// check if BBCode plugin is active
		if (!function_exists('plugin_bbcode_init')) {
			return;
		}

		// get the global bbcode object
		$bbcode = plugin_bbcode_init();
		static $descriptionPrefilterAdded = false;
		if (!$descriptionPrefilterAdded && defined('STRINGPARSER_FILTER_PRE') && method_exists($bbcode, 'addFilter')) {
			$bbcode->addFilter(STRINGPARSER_FILTER_PRE, 'AudioVideoPlugin::prepareDescriptionTags');
			$descriptionPrefilterAdded = true;
		}

		// add tag "audioplayer"
		$bbcode->addCode('audioplayer', //  // tag name: this will go between square brackets
		'callback_replace_single', // type of action: we'll use a callback function
		'AudioVideoPlugin::getAudioHtml', // name of the callback function
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
		$bbcode->setCodeFlag('audioplayer', 'closetag', BBCODE_CLOSETAG_FORBIDDEN); // a closing tag is forbidden (no [/tag])

		// add tag "videoplayer"
		$bbcode->addCode('videoplayer', // tag name: this will go between square brackets
		'callback_replace_single', // type of action: we'll use a callback function
		'AudioVideoPlugin::getVideoHtml', // name of the callback function
		array(
			'usecontent_param' => array(
				'default'
			)
		),// supported parameters: "default" is [acronym=valore]
		'inline', // type of the tag, inline or block, etc
		array(
			'listitem',
			'block',
			'inline',
			'link'
		), // type of elements in which you can use this tag
		array()); // type of elements where this tag CAN'T go (in this case, none, so it can go everywhere)
		$bbcode->setCodeFlag('videoplayer', 'closetag', BBCODE_CLOSETAG_FORBIDDEN); // a closing tag is forbidden (no [/tag])
	}
}
?>
