<?php
/**
 * Mastodon text declarations. Loaded by plugin.mastodon.php.
 * Runtime constants and registrations remain in the main plugin file.
 */

/**
 * Guess a subject line from imported plain text.
 * @param string $text
 * @return string
 */
function plugin_mastodon_guess_subject($text) {
	$text = plugin_mastodon_plain_text_from_bbcode($text);
	if ($text === '') {
		return 'Mastodon';
	}

	$lines = preg_split("/\n+/u", $text);
	$candidate = '';
	foreach ($lines as $line) {
		$line = trim((string) $line);
		if ($line === '') {
			continue;
		}
		if (plugin_mastodon_subject_line_is_noise($line)) {
			continue;
		}
		$candidate = $line;
		break;
	}

	if ($candidate === '' && !empty($lines)) {
		$candidate = trim((string) reset($lines));
	}
	if ($candidate === '') {
		return 'Mastodon';
	}

	$candidate = preg_replace('/\s+/u', ' ', $candidate);
	if (function_exists('mb_substr')) {
		$candidate = mb_substr($candidate, 0, 72, 'UTF-8');
	} else {
		$candidate = substr($candidate, 0, 72);
	}

	return trim((string) $candidate);
}

/**
 * Decode HTML entities using the plugin defaults.
 * @param string $text
 * @return string
 */
function plugin_mastodon_html_entity_decode($text) {
	$text = (string) $text;
	return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Return the absolute base URL of the current FlatPress installation.
 * @return string
 */
function plugin_mastodon_blog_base_url() {
	$url = plugin_mastodon_fp_config_value(array('general', 'www'), '');
	$url = is_string($url) ? trim($url) : '';
	if ($url === '' && defined('BLOG_BASEURL')) {
		$url = (string) BLOG_BASEURL;
	}

	if ($url === '') {
		$scheme = 'http';
		if (!empty($_SERVER ['HTTPS']) && strtolower((string) $_SERVER ['HTTPS']) !== 'off') {
			$scheme = 'https';
		}
		$host = isset($_SERVER ['HTTP_HOST']) ? trim((string) $_SERVER ['HTTP_HOST']) : 'localhost';
		$script = isset($_SERVER ['SCRIPT_NAME']) ? (string) $_SERVER ['SCRIPT_NAME'] : '/';
		$basePath = rtrim(str_replace('\\', '/', dirname($script)), '/');
		$url = $scheme . '://' . $host . ($basePath !== '' ? $basePath . '/' : '/');
	}

	$parts = @parse_url($url);
	if (!is_array($parts) || empty($parts ['host'])) {
		return '';
	}

	$scheme = isset($parts ['scheme']) ? strtolower((string) $parts ['scheme']) : 'https';
	$host = strtolower((string) $parts ['host']);
	$port = isset($parts ['port']) ? ':' . (int) $parts ['port'] : '';
	$path = isset($parts ['path']) ? trim((string) $parts ['path'], '/') : '';

	return $scheme . '://' . $host . $port . ($path !== '' ? '/' . $path : '') . '/';
}

/**
 * Extract the URL token from a BBCode or attribute fragment.
 * @param string $value
 * @return string
 */
function plugin_mastodon_extract_url_token($value) {
	$value = plugin_mastodon_html_entity_decode(strip_tags((string) $value));
	$value = trim($value);
	if ($value === '') {
		return '';
	}

	$value = trim($value, " \t\n\r\0\x0B\"'");
	$parts = preg_split('/\s+/', $value);
	return isset($parts [0]) ? trim((string) $parts [0]) : '';
}

/**
 * Convert a URL or path into an absolute URL when possible.
 * @param string $url
 * @return string
 */
function plugin_mastodon_absolute_url($url) {
	$url = plugin_mastodon_extract_url_token($url);
	if ($url === '') {
		return '';
	}

	if (preg_match('!^(https?://|mailto:)!i', $url)) {
		return $url;
	}

	$base = plugin_mastodon_blog_base_url();
	if ($base === '') {
		return $url;
	}

	$baseParts = @parse_url($base);
	if (!is_array($baseParts) || empty($baseParts ['host'])) {
		return $url;
	}

	$scheme = isset($baseParts ['scheme']) ? strtolower((string) $baseParts ['scheme']) : 'https';
	$host = strtolower((string) $baseParts ['host']);
	$port = isset($baseParts ['port']) ? ':' . (int) $baseParts ['port'] : '';

	if (strpos($url, '//') === 0) {
		return $scheme . ':' . $url;
	}

	if (strpos($url, '/') === 0) {
		return $scheme . '://' . $host . $port . $url;
	}

	$url = preg_replace('!^\./!u', '', $url);
	return $base . ltrim((string) $url, '/');
}

/**
 * Return a localized plugin string or a provided fallback.
 * @param string $key
 * @param string $default
 * @return string
 */
function plugin_mastodon_lang_string($key, $default) {
	static $strings = null;

	if ($strings === null) {
		$strings = array();
		if (function_exists('lang_load')) {
			$lang = lang_load('plugin:mastodon');
			if (isset($lang ['admin'] ['plugin'] ['mastodon']) && is_array($lang ['admin'] ['plugin'] ['mastodon'])) {
				$strings = $lang ['admin'] ['plugin'] ['mastodon'];
			}
		}
	}

	return isset($strings [$key]) && is_string($strings [$key]) && $strings [$key] !== '' ? $strings [$key] : (string) $default;
}

/**
 * Normalize a list of tag labels.
 *
 * @param array<int, string> $tags
 * @return array<int, string>
 */
function plugin_mastodon_normalize_tag_list($tags) {
	$normalized = array();
	$seen = array();

	foreach ((array) $tags as $tag) {
		$tag = trim((string) $tag);
		$tag = ltrim($tag, "# \t\n\r\0\x0B");
		if ($tag === '') {
			continue;
		}
		$key = function_exists('mb_strtolower') ? mb_strtolower($tag, 'UTF-8') : strtolower($tag);
		if (isset($seen [$key])) {
			continue;
		}
		$seen [$key] = true;
		$normalized [] = $tag;
	}

	return $normalized;
}

/**
 * Extract FlatPress Tag plugin labels from an entry body.
 *
 * The Tag plugin stores tags inside [tag]...[/tag] BBCode blocks. Each block
 * contains a comma-separated list. The same semantics are used here so export
 * and import match the Tag plugin's own publish_post processing.
 *
 * @param string $content
 * @return array<int, string>
 */
function plugin_mastodon_extract_flatpress_tags($content) {
	$content = (string) $content;
	if ($content === '' || stripos($content, '[tag') === false) {
		return array();
	}

	$tags = array();
	if (preg_match_all('!\[tag\](.*?)\[/tag\]!is', $content, $matches) > 0) {
		foreach ($matches [1] as $tagGroup) {
			$parts = explode(',', (string) $tagGroup);
			foreach ($parts as $part) {
				$tags [] = (string) $part;
			}
		}
	}

	return plugin_mastodon_normalize_tag_list($tags);
}

/**
 * Remove Tag plugin BBCode blocks from entry content.
 *
 * @param string $content
 * @return string
 */
function plugin_mastodon_strip_flatpress_tag_bbcode($content) {
	$content = preg_replace('!\n?\[tag\].*?\[/tag\]\n?!is', "\n", (string) $content);
	$content = str_replace(array("\r\n", "\r"), "\n", (string) $content);
	$content = preg_replace("/\n{3,}/", "\n\n", (string) $content);
	return trim((string) $content);
}

/**
 * Convert FlatPress tag labels into a Mastodon hashtag footer line.
 *
 * Mastodon exposes status tags as strings without the leading hash sign, while
 * the visible status text uses #hashtag tokens. Local FlatPress tags are therefore
 * normalized into safe hashtag tokens and emitted as a single footer line.
 *
 * @param array<int, string> $tags
 * @return string
 */
function plugin_mastodon_mastodon_hashtag_footer($tags) {
	$tokens = array();

	foreach (plugin_mastodon_normalize_tag_list($tags) as $tag) {
		$token = preg_replace('/\s+/u', '', $tag);
		$token = trim((string) $token);
		if ($token === '') {
			continue;
		}
		$tokens [] = '#' . $token;
	}

	return trim(implode(' ', array_unique($tokens)));
}

/**
 * Collect remote Mastodon tags from a status entity.
 *
 * @param array<string, mixed> $remoteStatus
 * @return array<int, string>
 */
function plugin_mastodon_remote_status_tags($remoteStatus) {
	$tags = array();

	if (!empty($remoteStatus ['tags']) && is_array($remoteStatus ['tags'])) {
		foreach ($remoteStatus ['tags'] as $remoteTag) {
			if (is_array($remoteTag) && !empty($remoteTag ['name'])) {
				$tags [] = (string) $remoteTag ['name'];
			} elseif (is_string($remoteTag)) {
				$tags [] = $remoteTag;
			}
		}
	}

	return plugin_mastodon_normalize_tag_list($tags);
}

/**
 * Remove a trailing Mastodon hashtag footer from imported plain text.
 *
 * Exported FlatPress tags are appended as a dedicated hashtag line. When such a
 * status is imported back, that trailing hashtag-only line is converted back into
 * Tag plugin metadata instead of staying visible in the FlatPress entry body.
 *
 * @param string $content
 * @param array<int, string> $remoteTags
 * @return string
 */
function plugin_mastodon_strip_trailing_mastodon_hashtag_footer($content, $remoteTags) {
	$content = trim((string) $content);
	$remoteTags = plugin_mastodon_normalize_tag_list($remoteTags);
	if ($content === '' || empty($remoteTags)) {
		return $content;
	}

	$normalizedRemote = array();
	foreach ($remoteTags as $tag) {
		$key = function_exists('mb_strtolower') ? mb_strtolower($tag, 'UTF-8') : strtolower($tag);
		$normalizedRemote [$key] = true;
	}

	$lines = preg_split("/\r\n|\r|\n/", $content);
	if (!is_array($lines) || empty($lines)) {
		return $content;
	}

	$index = count($lines) - 1;
	while ($index >= 0 && trim((string) $lines [$index]) === '') {
		$index--;
	}
	if ($index < 0) {
		return '';
	}

	$footerLine = trim((string) $lines [$index]);
	if ($footerLine === '') {
		return $content;
	}

	$tokens = preg_split('/\s+/u', $footerLine);
	if (!is_array($tokens) || empty($tokens)) {
		return $content;
	}

	$matched = array();
	foreach ($tokens as $token) {
		$token = trim((string) $token);
		if ($token === '' || strpos($token, '#') !== 0) {
			return $content;
		}
		$token = ltrim($token, '#');
		if ($token === '') {
			return $content;
		}
		$key = function_exists('mb_strtolower') ? mb_strtolower($token, 'UTF-8') : strtolower($token);
		if (!isset($normalizedRemote [$key])) {
			return $content;
		}
		$matched [$key] = true;
	}

	array_splice($lines, $index, 1);
	return trim((string) preg_replace("/\n{3,}/", "\n\n", implode("\n", $lines)));
}

/**
 * Build Tag plugin BBCode from a list of remote Mastodon tags.
 *
 * @param array<int, string> $tags
 * @return string
 */
function plugin_mastodon_build_flatpress_tag_bbcode($tags) {
	$tags = plugin_mastodon_normalize_tag_list($tags);
	if (empty($tags)) {
		return '';
	}
	return '[tag]' . implode(', ', $tags) . '[/tag]';
}

/**
 * Convert an emoticon HTML entity into a Unicode character.
 * @param string $value
 * @return string
 */
function plugin_mastodon_emoticon_entity_to_unicode($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}

	return html_entity_decode($value, defined('ENT_HTML5') ? ENT_QUOTES | ENT_HTML5 : ENT_QUOTES, 'UTF-8');
}

/**
 * Return the FlatPress emoticon-to-Unicode lookup map.
 * @return array<string, string>
 */
function plugin_mastodon_emoticon_map() {
	static $map = null;

	if ($map !== null) {
		return $map;
	}

	$map = array(
		':smile:' => '😄',
		':smiley:' => '😃',
		':wink:' => '😉',
		':blush:' => '😊',
		':grin:' => '😁',
		':smirk:' => '😏',
		':heart_eyes:' => '😍',
		':sunglasses:' => '😎',
		':laughing:' => '😆',
		':joy:' => '😂',
		':neutral_face:' => '😐',
		':flushed:' => '😳',
		':hushed:' => '😮',
		':dizzy_face:' => '😵',
		':cry:' => '😢',
		':persevere:' => '😣',
		':worried:' => '😟',
		':angry:' => '😠',
		':mag:' => '🔍',
		':hot_beverage:' => '☕',
		':exclamation:' => '❗',
		':question:' => '❓'
	);

	if (isset($GLOBALS ['plugin_emoticons']) && is_array($GLOBALS ['plugin_emoticons'])) {
		$detected = array();
		foreach ($GLOBALS ['plugin_emoticons'] as $shortcode => $entity) {
			$shortcode = trim((string) $shortcode);
			$unicode = plugin_mastodon_emoticon_entity_to_unicode($entity);
			if ($shortcode !== '' && $unicode !== '') {
				$detected [$shortcode] = $unicode;
			}
		}
		if (!empty($detected)) {
			$map = $detected;
		}
	}

	return $map;
}

/**
 * Replace FlatPress emoticon shortcodes with Unicode glyphs.
 * @param string $text
 * @return string
 */
function plugin_mastodon_replace_emoticon_shortcodes_with_unicode($text) {
	$text = (string) $text;
	$map = plugin_mastodon_emoticon_map();
	return empty($map) ? $text : strtr($text, $map);
}

/**
 * Convert FlatPress emoticon shortcodes to Mastodon-safe Unicode glyphs when the plugin is active.
 *
 * Mastodon status creation accepts plain-text status bodies, so FlatPress emoticon shortcodes need
 * to be converted before export if the Emoticons plugin is enabled for the current instance.
 *
 * @param string $text
 * @return string
 */
function plugin_mastodon_prepare_emoticons_for_mastodon($text) {
	$text = (string) $text;
	if ($text === '' || !plugin_mastodon_emoticons_plugin_active()) {
		return $text;
	}
	return plugin_mastodon_replace_emoticon_shortcodes_with_unicode($text);
}

/**
 * Replace Unicode emoticons with FlatPress shortcodes.
 * @param string $text
 * @return string
 */
function plugin_mastodon_replace_unicode_emoticons_with_shortcodes($text) {
	$text = (string) $text;
	$map = plugin_mastodon_emoticon_map();
	if (empty($map)) {
		return $text;
	}

	$reverse = array();
	foreach ($map as $shortcode => $unicode) {
		if ($unicode !== '') {
			$reverse [$unicode] = $shortcode;
		}
	}

	return empty($reverse) ? $text : strtr($text, $reverse);
}

/**
 * Determine whether a host name resolves to a public endpoint.
 * @param string $host
 * @return bool
 */
function plugin_mastodon_is_public_host($host) {
	$host = strtolower(trim((string) $host, ". \t\n\r\0\x0B"));
	if ($host === '') {
		return false;
	}

	if ($host === 'localhost' || substr($host, -10) === '.localhost' || substr($host, -6) === '.local' || substr($host, -5) === '.test' || substr($host, -8) === '.invalid' || substr($host, -8) === '.example') {
		return false;
	}

	$ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : '';
	if ($ip !== '') {
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
	}

	return strpos($host, '.') !== false;
}

/**
 * Return a Mastodon-safe public URL or an empty string.
 * @param string $url
 * @return string
 */
function plugin_mastodon_public_url_for_mastodon($url) {
	$url = plugin_mastodon_absolute_url($url);
	if ($url === '' || preg_match('!^mailto:!i', $url)) {
		return '';
	}

	$parts = @parse_url($url);
	if (!is_array($parts) || empty($parts ['host']) || !plugin_mastodon_is_public_host($parts ['host'])) {
		return '';
	}

	return $url;
}

/**
 * Convert FlatPress BBCode into plain text for Mastodon export.
 * @param string $text
 * @return string
 */
function plugin_mastodon_plain_text_from_bbcode($text) {
	$text = (string) $text;
	if ($text === '') {
		return '';
	}

	$text = preg_replace_callback(
		'!\[url=([^\]]+)\](.*?)\[/url\]!is',
		function ($matches) {
			$label = trim(plugin_mastodon_html_entity_decode(strip_tags((string) $matches [2])));
			if ($label !== '') {
				return $label;
			}
			return plugin_mastodon_absolute_url($matches [1]);
		},
		$text
	);
	$text = preg_replace_callback(
		'!\[url\](.*?)\[/url\]!is',
		function ($matches) {
			return plugin_mastodon_absolute_url($matches [1]);
		},
		$text
	);
	$text = preg_replace('!\[img\](.*?)\[/img\]!is', '', $text);
	$text = preg_replace('!\[\s*img\b[^\]]*\]!is', '', $text);
	$text = preg_replace('!\[\s*gallery\b[^\]]*\]!is', '', $text);
	$text = preg_replace('!\[\s*(audio|video)player\b[^\]]*\].*?\[\s*/\s*\1player\s*\]!is', '', $text);
	$text = preg_replace('!\[\s*(?:audio|video)player\b[^\]]*\]!is', '', $text);
	$text = preg_replace('!\[(\/?)(b|i|u|h1|h2|h3|h4|list|\*|left|right|center|justify|color|size|font|flash|youtube|video|audio|mail|html|raw|more|table|tr|td|th|caption|tbody|thead|tfoot|quote|code)(=[^\]]*)?\]!is', '', $text);
	$text = strip_tags($text);
	$text = plugin_mastodon_html_entity_decode($text);
	$text = str_replace(array("\r\n", "\r"), "\n", $text);
	$text = preg_replace("/[ \t]+\n/u", "\n", $text);
	$text = preg_replace("/\n{3,}/", "\n\n", $text);

	return trim((string) $text);
}

/**
 * Determine whether an extracted line should be ignored as a subject.
 * @param mixed $line
 * @return bool
 */
function plugin_mastodon_subject_line_is_noise($line) {
	$line = trim((string) $line);
	if ($line === '') {
		return true;
	}

	if (preg_match('!^(https?://\S+|www\.\S+)$!iu', $line)) {
		return true;
	}
	if (preg_match('/^(?:@[\pL\pN._-]+(?:@[\pL\pN.-]+)?\s*)+$/u', $line)) {
		return true;
	}
	if (preg_match('/^@[^\s]+(?:\s+[A-Za-z0-9.-]+\.[A-Za-z]{2,})$/u', $line)) {
		return true;
	}
	if (preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/u', $line)) {
		return true;
	}

	return false;
}

/**
 * Determine whether two host names belong to the same domain family.
 * @param mixed $left
 * @param mixed $right
 * @return bool
 */
function plugin_mastodon_domains_match($left, $right) {
	$left = strtolower(trim((string) $left, ". \t\n\r\0\x0B"));
	$right = strtolower(trim((string) $right, ". \t\n\r\0\x0B"));
	if ($left === '' || $right === '') {
		return false;
	}
	return $left === $right || substr($left, -strlen($right) - 1) === '.' . $right || substr($right, -strlen($left) - 1) === '.' . $left;
}

/**
 * Clean imported text before saving it to FlatPress.
 * @param string $text
 * @return string
 */
function plugin_mastodon_cleanup_imported_text($text) {
	$text = str_replace(array("\r\n", "\r"), "\n", (string) $text);
	$text = preg_replace("/\n{3,}/", "\n\n", $text);
	$lines = explode("\n", $text);
	$clean = array();

	foreach ($lines as $line) {
		$line = preg_replace('/[ \t]+/u', ' ', rtrim((string) $line));
		$trimmed = trim((string) $line);

		if ($trimmed !== '' && !empty($clean)) {
			$lastIndex = null;
			for ($i = count($clean) - 1; $i >= 0; $i--) {
				if (trim((string) $clean [$i]) !== '') {
					$lastIndex = $i;
					break;
				}
			}

			if ($lastIndex !== null) {
				$previous = trim((string) $clean [$lastIndex]);

				if (preg_match('!^\[url=([^\]]+)\](@[^\[]+)\[/url\]$!u', $previous, $matches) && preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/u', $trimmed)) {
					$host = @parse_url(plugin_mastodon_absolute_url($matches [1]), PHP_URL_HOST);
					if (is_string($host) && plugin_mastodon_domains_match($host, $trimmed) && strpos($matches [2], '@' . $trimmed) === false) {
						$clean [$lastIndex] = '[url=' . plugin_mastodon_absolute_url($matches [1]) . ']' . $matches [2] . '@' . $trimmed . ' [/url]';
						continue;
					}
				}

				if (preg_match('!^\[url\](https?://[^\[]+)\[/url\]$!u', $previous, $matches) && preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/u', $trimmed)) {
					$host = @parse_url(plugin_mastodon_absolute_url($matches [1]), PHP_URL_HOST);
					if (is_string($host) && plugin_mastodon_domains_match($host, $trimmed)) {
						continue;
					}
				}
			}
		}

		if ($trimmed === '' && (!empty($clean) && end($clean) === '')) {
			continue;
		}

		$clean [] = $trimmed;
	}

	return trim(implode("\n", $clean));
}

/**
 * Convert DOM child nodes into FlatPress BBCode text.
 * @param DOMNode $node
 * @return string
 */
function plugin_mastodon_dom_children_to_flatpress($node) {
	$output = '';
	if (!$node || !$node->hasChildNodes()) {
		return $output;
	}

	foreach ($node->childNodes as $child) {
		$output .= plugin_mastodon_dom_node_to_flatpress($child);
	}

	return $output;
}

/**
 * Convert a single DOM node into FlatPress BBCode text.
 * @param DOMNode $node
 * @return string
 */
function plugin_mastodon_dom_node_to_flatpress($node) {
	if (!$node) {
		return '';
	}

	if ($node->nodeType === XML_TEXT_NODE || $node->nodeType === XML_CDATA_SECTION_NODE) {
		return plugin_mastodon_html_entity_decode($node->nodeValue);
	}

	if ($node->nodeType !== XML_ELEMENT_NODE) {
		return '';
	}

	$name = strtolower((string) $node->nodeName);
	if (in_array($name, array('script', 'style', 'template'), true)) {
		return '';
	}

	if ($name === 'br') {
		return "\n";
	}
	if ($name === 'hr') {
		return "\n----\n";
	}
	if ($name === 'img') {
		if (!($node instanceof DOMElement)) {
			return '';
		}
		$alt = trim((string) $node->getAttribute('alt'));
		$class = ' ' . strtolower(trim((string) $node->getAttribute('class'))) . ' ';
		if ($alt !== '' && preg_match('/^:[A-Za-z0-9_+\-]+:$/', $alt)) {
			return $alt;
		}
		if ($alt !== '' && (strpos($class, ' emoji ') !== false || strpos($class, ' emojione ') !== false || strpos($class, ' custom-emoji ') !== false)) {
			return $alt;
		}
		return '';
	}
	if ($name === 'blockquote') {
		$inner = trim(plugin_mastodon_dom_children_to_flatpress($node));
		return $inner === '' ? '' : "\n[quote]\n" . $inner . "\n[/quote]\n";
	}
	if ($name === 'strong' || $name === 'b') {
		return '[b]' . trim(plugin_mastodon_dom_children_to_flatpress($node)) . '[/b]';
	}
	if ($name === 'em' || $name === 'i') {
		return '[i]' . trim(plugin_mastodon_dom_children_to_flatpress($node)) . '[/i]';
	}
	if ($name === 'pre') {
		$inner = plugin_mastodon_html_entity_decode($node->textContent);
		$inner = str_replace(array("\r\n", "\r"), "\n", $inner);
		return "\n[code]\n" . trim($inner) . "\n[/code]\n";
	}
	if ($name === 'code') {
		$parentName = $node->parentNode ? strtolower((string) $node->parentNode->nodeName) : '';
		if ($parentName === 'pre') {
			return plugin_mastodon_html_entity_decode($node->textContent);
		}
		return '[code]' . trim(plugin_mastodon_html_entity_decode($node->textContent)) . '[/code]';
	}
	if ($name === 'ul' || $name === 'ol') {
		$items = trim(plugin_mastodon_dom_children_to_flatpress($node));
		return $items === '' ? '' : "\n[list]\n" . $items . "\n[/list]\n";
	}
	if ($name === 'li') {
		$item = trim(plugin_mastodon_dom_children_to_flatpress($node));
		return $item === '' ? '' : '[*] ' . $item . "\n";
	}
	if ($name === 'a') {
		if (!($node instanceof DOMElement)) {
			return plugin_mastodon_plain_text_from_bbcode(plugin_mastodon_dom_children_to_flatpress($node));
		}
		$href = plugin_mastodon_absolute_url($node->getAttribute('href'));
		$label = plugin_mastodon_plain_text_from_bbcode(plugin_mastodon_dom_children_to_flatpress($node));
		$label = trim(preg_replace('/\s+/u', ' ', $label));

		if ($href === '') {
			return $label;
		}
		if ($label === '') {
			return '[url]' . $href . '[/url]';
		}

		$normalizedLabel = preg_replace('!^https?://!iu', '', rtrim($label, '/'));
		$normalizedHref = preg_replace('!^https?://!iu', '', rtrim($href, '/'));
		if ($normalizedLabel === $normalizedHref) {
			return '[url]' . $href . '[/url]';
		}

		return '[url=' . $href . ']' . $label . '[/url]';
	}
	if (in_array($name, array('p', 'div', 'section', 'article', 'header', 'footer'), true)) {
		$inner = trim(plugin_mastodon_dom_children_to_flatpress($node));
		return $inner === '' ? '' : $inner . "\n\n";
	}
	if (in_array($name, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'), true)) {
		$inner = trim(plugin_mastodon_dom_children_to_flatpress($node));
		return $inner === '' ? '' : '[b]' . $inner . '[/b]' . "\n\n";
	}

	return plugin_mastodon_dom_children_to_flatpress($node);
}

/**
 * Return the public URL for a FlatPress entry.
 * @param string $entryId
 * @param array<string, mixed> $entry
 * @return string
 */
function plugin_mastodon_public_entry_url($entryId, $entry) {
	$entryId = (string) $entryId;
	if ($entryId === '') {
		return '';
	}

	$entry = is_array($entry) ? $entry : array();
	$currentPost = isset($GLOBALS ['post']) ? $GLOBALS ['post'] : null;
	$hadPost = array_key_exists('post', $GLOBALS);

	$GLOBALS ['post'] = $entry;
	$link = function_exists('get_permalink') ? get_permalink($entryId) : '';
	if ($hadPost) {
		$GLOBALS ['post'] = $currentPost;
	} else {
		unset($GLOBALS ['post']);
	}

	return plugin_mastodon_absolute_url($link);
}

/**
 * Return the public comments URL for a FlatPress entry.
 * @param string $entryId
 * @param array<string, mixed> $entry
 * @return string
 */
function plugin_mastodon_public_comments_url($entryId, $entry) {
	$entryId = (string) $entryId;
	if ($entryId === '') {
		return '';
	}

	$entry = is_array($entry) ? $entry : array();
	$currentPost = isset($GLOBALS ['post']) ? $GLOBALS ['post'] : null;
	$hadPost = array_key_exists('post', $GLOBALS);

	$GLOBALS ['post'] = $entry;
	$link = function_exists('get_comments_link') ? get_comments_link($entryId) : '';
	if ($hadPost) {
		$GLOBALS ['post'] = $currentPost;
	} else {
		unset($GLOBALS ['post']);
	}

	return plugin_mastodon_absolute_url($link);
}

/**
 * Return the public URL for a specific FlatPress comment.
 * @param string $entryId
 * @param array<string, mixed> $entry
 * @param string $commentId
 * @return string
 */
function plugin_mastodon_public_comment_url($entryId, $entry, $commentId) {
	$baseUrl = plugin_mastodon_public_comments_url($entryId, $entry);
	$commentId = trim((string) $commentId);
	if ($baseUrl === '' || $commentId === '') {
		return '';
	}

	return $baseUrl . '#' . $commentId;
}

/**
 * Convert Mastodon HTML content into FlatPress BBCode.
 * @param string $html
 * @return string
 */
function plugin_mastodon_mastodon_html_to_flatpress($html) {
	$html = (string) $html;
	if ($html === '') {
		return '';
	}

	$text = '';
	if (class_exists('DOMDocument')) {
		$internalErrors = function_exists('libxml_use_internal_errors') ? libxml_use_internal_errors(true) : false;
		$doc = new DOMDocument('1.0', 'UTF-8');
		$flags = 0;
		if (defined('LIBXML_NONET')) {
			$flags |= LIBXML_NONET;
		}
		$loaded = @$doc->loadHTML('<?xml encoding="UTF-8"><!DOCTYPE html><html><body>' . $html . '</body></html>', $flags);
		if ($loaded) {
			$body = $doc->getElementsByTagName('body')->item(0);
			if ($body) {
				$text = trim(plugin_mastodon_dom_children_to_flatpress($body));
			}
		}
		if (function_exists('libxml_clear_errors')) {
			libxml_clear_errors();
		}
		if (function_exists('libxml_use_internal_errors')) {
			libxml_use_internal_errors($internalErrors);
		}
	}

	if ($text === '') {
		$replacements = array(
			'!<br\s*/?>!i' => "\n",
			'!</p>\s*<p[^>]*>!i' => "\n\n",
			'!<p[^>]*>!i' => '',
			'!</p>!i' => '',
			'!<blockquote\b[^>]*>!i' => '[quote]',
			'!</blockquote>!i' => '[/quote]',
			'!<(strong|b)\b[^>]*>!i' => '[b]',
			'!</(strong|b)>!i' => '[/b]',
			'!<(em|i)\b[^>]*>!i' => '[i]',
			'!</(em|i)>!i' => '[/i]',
			'!<pre\b[^>]*><code\b[^>]*>!i' => '[code]',
			'!</code></pre>!i' => '[/code]',
			'!<pre\b[^>]*>!i' => '[code]',
			'!</pre>!i' => '[/code]',
			'!<code\b[^>]*>!i' => '[code]',
			'!</code>!i' => '[/code]',
			'!<li\b[^>]*>!i' => "\n[*] ",
			'!</li>!i' => '',
			'!<ul\b[^>]*>!i' => "\n[list]\n",
			'!</ul>!i' => "\n[/list]\n",
			'!<ol\b[^>]*>!i' => "\n[list]\n",
			'!</ol>!i' => "\n[/list]\n"
		);

		foreach ($replacements as $pattern => $replacement) {
			$html = preg_replace($pattern, $replacement, $html);
		}

		$html = preg_replace_callback(
			'!<img\s[^>]*alt=(["\'])(.*?)\1[^>]*>!is',
			function ($matches) {
				$alt = trim(plugin_mastodon_html_entity_decode((string) $matches [2]));
				return preg_match('/^:[A-Za-z0-9_+\-]+:$/', $alt) ? $alt : '';
			},
			$html
		);

		$text = preg_replace_callback(
			'!<a\s[^>]*href=(["\'])(.*?)\1[^>]*>(.*?)</a>!is',
			function ($matches) {
				$href = plugin_mastodon_absolute_url($matches [2]);
				$label = trim(plugin_mastodon_html_entity_decode(strip_tags($matches [3])));
				if ($href === '') {
					return $label;
				}
				if ($label === '') {
					return '[url]' . $href . '[/url]';
				}
				return '[url=' . $href . ']' . $label . '[/url]';
			},
			$html
		);

		$text = strip_tags($text);
		$text = plugin_mastodon_html_entity_decode($text);
	}

	$text = plugin_mastodon_replace_unicode_emoticons_with_shortcodes($text);
	$text = str_replace(array("\r\n", "\r"), "\n", $text);
	$text = preg_replace("/[ \t]+\n/u", "\n", $text);
	$text = preg_replace("/\n{3,}/", "\n\n", $text);
	$text = plugin_mastodon_cleanup_imported_text($text);

	return trim((string) $text);
}

/**
 * Convert FlatPress content into Mastodon-ready plain text.
 * @param string $text
 * @return string
 */
function plugin_mastodon_flatpress_to_mastodon($text) {
	$text = plugin_mastodon_prepare_emoticons_for_mastodon((string) $text);
	if ($text === '') {
		return '';
	}
	if (plugin_mastodon_tag_plugin_active()) {
		$text = plugin_mastodon_strip_flatpress_tag_bbcode($text);
	}

	$text = preg_replace_callback(
		'!<a\s[^>]*href=(["\'])(.*?)\1[^>]*>(.*?)</a>!is',
		function ($matches) {
			$url = plugin_mastodon_public_url_for_mastodon($matches [2]);
			$label = trim(plugin_mastodon_html_entity_decode(strip_tags((string) $matches [3])));
			if ($url === '') {
				return $label;
			}
			if ($label === '' || $label === $url) {
				return $url;
			}
			return $label . ' ' . $url;
		},
		$text
	);

	$text = preg_replace_callback(
		'!\[url=([^\]]+)\](.*?)\[/url\]!is',
		function ($matches) {
			$url = plugin_mastodon_public_url_for_mastodon($matches [1]);
			$label = trim(plugin_mastodon_html_entity_decode(strip_tags((string) $matches [2])));
			if ($url === '') {
				return $label;
			}
			if ($label === '' || $label === $url) {
				return $url;
			}
			return $label . ' ' . $url;
		},
		$text
	);

	$text = preg_replace_callback(
		'!\[url\](.*?)\[/url\]!is',
		function ($matches) {
			return plugin_mastodon_public_url_for_mastodon($matches [1]);
		},
		$text
	);

	$text = preg_replace_callback(
		'!\[img\](.*?)\[/img\]!is',
		function ($matches) {
			return plugin_mastodon_public_url_for_mastodon($matches [1]);
		},
		$text
	);

	$text = preg_replace('!\[\s*img\b[^\]]*\]!is', '', $text);
	$text = preg_replace('!\[\s*gallery\b[^\]]*\]!is', '', $text);
	$text = preg_replace('!\[\s*(audio|video)player\b[^\]]*\].*?\[\s*/\s*\1player\s*\]!is', '', $text);
	$text = preg_replace('!\[\s*(?:audio|video)player\b[^\]]*\]!is', '', $text);

	$text = preg_replace_callback(
		'!\[list(?:=[^\]]*)?\](.*?)\[/list\]!is',
		function ($matches) {
			$body = str_replace(array("\r\n", "\r"), "\n", (string) $matches [1]);
			$body = preg_replace('/\[\*\]\s*/iu', "\n• ", $body);
			return "\n" . trim($body) . "\n";
		},
		$text
	);

	$text = preg_replace_callback(
		'!\[quote\](.*?)\[/quote\]!is',
		function ($matches) {
			$body = trim((string) $matches [1]);
			$body = preg_replace('/^\s+/m', '', $body);
			$body = preg_replace('/^/m', '> ', $body);
			return "\n" . $body . "\n";
		},
		$text
	);

	$text = preg_replace_callback(
		'!\[code\](.*?)\[/code\]!is',
		function ($matches) {
			return "\n```\n" . trim((string) $matches [1]) . "\n```\n";
		},
		$text
	);

	$text = preg_replace_callback(
		'!\[(h1|h2|h3|h4)\](.*?)\[/\1\]!is',
		function ($matches) {
			$heading = trim(plugin_mastodon_html_entity_decode(strip_tags((string) $matches [2])));
			return $heading === '' ? '' : "\n" . $heading . "\n";
		},
		$text
	);

	$text = preg_replace('!\[more\]!is', "\n", $text);
	$text = preg_replace('!\[(\/?)(b|i|u|left|right|center|justify|color|size|font|flash|youtube|video|audio|mail|html|raw|table|tr|td|th|caption|tbody|thead|tfoot)(=[^\]]*)?\]!is', '', $text);
	$text = strip_tags($text);
	$text = plugin_mastodon_html_entity_decode($text);
	$text = str_replace(array("\r\n", "\r"), "\n", $text);
	$text = preg_replace("/[ \t]+\n/u", "\n", $text);
	$text = preg_replace("/\n{3,}/", "\n\n", $text);

	return trim((string) $text);
}

/**
 * Limit text to a maximum number of characters.
 * @param string $text
 * @param int $limit
 * @return string
 */
function plugin_mastodon_limit_text($text, $limit) {
	$text = trim((string) $text);
	$limit = (int) $limit;
	if ($limit <= 0) {
		return $text;
	}
	$length = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
	if ($length <= $limit) {
		return $text;
	}
	$suffix = '…';
	if (function_exists('mb_substr')) {
		return rtrim(mb_substr($text, 0, $limit - 1, 'UTF-8')) . $suffix;
	}
	return rtrim(substr($text, 0, $limit - 1)) . $suffix;
}

/**
 * Escape a value for safe BBCode attribute usage.
 * @param string $value
 * @return string
 */
function plugin_mastodon_bbcode_attr_escape($value) {
	$value = str_replace(array("\r", "\n"), ' ', (string) $value);
	$value = trim((string) $value);
	$value = str_replace(array('"', '[', ']'), array('&quot;', '(', ')'), $value);
	return $value;
}

/**
 * Escape plain text embedded between BBCode tags.
 * @param mixed $value
 * @return string
 */
function plugin_mastodon_bbcode_text_escape($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}
	$value = str_replace(array("\r\n", "\r"), "\n", $value);
	$value = preg_replace("/[ \t]+\n/u", "\n", $value);
	if (!is_string($value)) {
		return '';
	}
	$value = preg_replace("/\n{3,}/u", "\n\n", $value);
	if (!is_string($value)) {
		return '';
	}
	return str_replace(array('[', ']'), array('&#91;', '&#93;'), trim($value));
}

/**
 * Extract Mastodon-countable URL spans from a plain-text status.
 *
 * Mastodon gives the configured URL budget only to URL entities that its formatter
 * can parse. Sentence punctuation appended after an URL therefore remains normal
 * text and must not disappear into the fixed URL placeholder budget.
 *
 * @param string $text
 * @return array<int, array{url: string, position: int, length: int}>
 */
function plugin_mastodon_status_text_url_spans($text) {
	$text = (string) $text;
	$spans = array();
	$matches = array();
	if ($text === '' || !preg_match_all('~https?://[^\s<>"\x00-\x1F\x7F]+~iu', $text, $matches, PREG_OFFSET_CAPTURE)) {
		return $spans;
	}

	foreach ($matches [0] as $match) {
		$candidate = (string) $match [0];
		$position = (int) $match [1];

		do {
			$previousCandidate = $candidate;
			$candidate = preg_replace('/[.,;:!?]+$/u', '', $candidate);
			$candidate = is_string($candidate) ? $candidate : $previousCandidate;

			foreach (array(')' => '(', ']' => '[', '}' => '{') as $closing => $opening) {
				while ($candidate !== '' && substr($candidate, -1) === $closing && substr_count($candidate, $closing) > substr_count($candidate, $opening)) {
					$candidate = substr($candidate, 0, -1);
				}
			}
		} while ($candidate !== $previousCandidate);

		if ($candidate === '' || !preg_match('~^https?://~i', $candidate)) {
			continue;
		}
		$host = @parse_url($candidate, PHP_URL_HOST);
		if (!is_string($host) || trim($host) === '') {
			continue;
		}

		$spans [] = array(
			'url' => $candidate,
			'position' => $position,
			'length' => strlen($candidate)
		);
	}

	return $spans;
}

/**
 * Calculate the Mastodon-visible length of a plain-text status.
 * @param string $text
 * @param int $urlReservedLength
 * @return int
 */
function plugin_mastodon_status_text_length($text, $urlReservedLength = 23) {
	$text = trim((string) $text);
	$urlReservedLength = max(1, (int) $urlReservedLength);
	if ($text === '') {
		return 0;
	}
	$urlSpans = plugin_mastodon_status_text_url_spans($text);
	if ($urlSpans === array()) {
		return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
	}

	$length = 0;
	$offset = 0;
	foreach ($urlSpans as $span) {
		$position = (int) $span ['position'];
		$urlLength = (int) $span ['length'];
		$segment = substr($text, $offset, $position - $offset);
		$length += function_exists('mb_strlen') ? mb_strlen($segment, 'UTF-8') : strlen($segment);
		$length += $urlReservedLength;
		$offset = $position + $urlLength;
	}
	$tail = substr($text, $offset);
	$length += function_exists('mb_strlen') ? mb_strlen($tail, 'UTF-8') : strlen($tail);
	return $length;
}

/**
 * Limit Mastodon plain text while respecting the instance URL budget.
 * @param string $text
 * @param int $limit
 * @param int $urlReservedLength
 * @return string
 */
function plugin_mastodon_limit_status_text($text, $limit, $urlReservedLength = 23) {
	$text = trim((string) $text);
	$limit = (int) $limit;
	$urlReservedLength = max(1, (int) $urlReservedLength);
	if ($limit <= 0 || $text === '') {
		return $text;
	}
	if (plugin_mastodon_status_text_length($text, $urlReservedLength) <= $limit) {
		return $text;
	}
	if ($limit === 1) {
		return '…';
	}

	$budget = $limit - 1;
	$result = '';
	$consumed = 0;
	$urlSpans = plugin_mastodon_status_text_url_spans($text);
	if ($urlSpans === array()) {
		return plugin_mastodon_limit_text($text, $limit);
	}

	$offset = 0;
	$truncated = false;
	foreach ($urlSpans as $span) {
		$position = (int) $span ['position'];
		$urlLength = (int) $span ['length'];
		$url = substr($text, $position, $urlLength);
		$segment = substr($text, $offset, $position - $offset);
		$segmentLength = function_exists('mb_strlen') ? mb_strlen($segment, 'UTF-8') : strlen($segment);
		$available = $budget - $consumed;
		if ($segmentLength > $available) {
			if ($available > 0) {
				$result .= function_exists('mb_substr') ? mb_substr($segment, 0, $available, 'UTF-8') : substr($segment, 0, $available);
			}
			$truncated = true;
			break;
		}
		$result .= $segment;
		$consumed += $segmentLength;
		$available = $budget - $consumed;
		if ($urlReservedLength > $available) {
			$truncated = true;
			break;
		}
		$result .= $url;
		$consumed += $urlReservedLength;
		$offset = $position + $urlLength;
	}

	if (!$truncated) {
		$tail = substr($text, $offset);
		$tailLength = function_exists('mb_strlen') ? mb_strlen($tail, 'UTF-8') : strlen($tail);
		$available = $budget - $consumed;
		if ($tailLength > $available) {
			if ($available > 0) {
				$result .= function_exists('mb_substr') ? mb_substr($tail, 0, $available, 'UTF-8') : substr($tail, 0, $available);
			}
			$truncated = true;
		} else {
			$result .= $tail;
		}
	}

	if (!$truncated) {
		return trim((string) $result);
	}
	$result = rtrim((string) $result);
	return $result === '' ? '…' : $result . '…';
}

/**
 * Build the status body used when exporting a FlatPress entry.
 * @param string $entryId
 * @param array<string, mixed> $entry
 * @param int $charLimit
 * @return string
 */
function plugin_mastodon_build_entry_status_text($entryId, $entry, $charLimit) {
	$subject = plugin_mastodon_prepare_emoticons_for_mastodon(isset($entry ['subject']) ? trim((string) $entry ['subject']) : '');
	$content = isset($entry ['content']) ? trim((string) $entry ['content']) : '';
	$urlReservedLength = plugin_mastodon_instance_url_reserved_length(plugin_mastodon_get_options());
	$parts = array();

	if ($subject !== '') {
		$parts [] = $subject;
	}
	if ($content !== '') {
		/**
		 * [more] limits only the entry body; keep full content for tag metadata.
		 * Apply the existing instance/URL budget below even if the teaser is longer.
		 */
		$morePosition = stripos($content, '[more]');
		$bodyContent = $morePosition === false ? $content : substr($content, 0, $morePosition);
		$body = plugin_mastodon_flatpress_to_mastodon($bodyContent);
		if ($body !== '') {
			$parts [] = $body;
		}
	}

	$text = trim(implode("\n\n", $parts));
	$link = plugin_mastodon_public_url_for_mastodon(plugin_mastodon_public_entry_url($entryId, $entry));
	$hashtags = '';
	if (plugin_mastodon_tag_plugin_active()) {
		$hashtags = plugin_mastodon_mastodon_hashtag_footer(plugin_mastodon_extract_flatpress_tags($content));
	}

	if ($hashtags !== '') {
		$suffixParts = array();
		if ($link !== '') {
			$suffixParts [] = $link;
		}
		$suffixParts [] = $hashtags;
		$suffix = implode("\n", $suffixParts);
		$suffixLength = plugin_mastodon_status_text_length($suffix, $urlReservedLength);
		$separatorLength = $text !== '' ? 1 : 0;
		$available = $charLimit - $separatorLength - $suffixLength;
		if ($available >= 32) {
			$text = trim((string) plugin_mastodon_limit_status_text($text, $available, $urlReservedLength));
			$text = $text === '' ? $suffix : $text . "\n" . $suffix;
			return trim((string) $text);
		}

		$suffixLength = plugin_mastodon_status_text_length($hashtags, $urlReservedLength);
		$available = $charLimit - $separatorLength - $suffixLength;
		if ($available >= 32) {
			$text = trim((string) plugin_mastodon_limit_status_text($text, $available, $urlReservedLength));
			$text = $text === '' ? $hashtags : $text . "\n" . $hashtags;
			return trim((string) $text);
		}
	}

	if ($link !== '') {
		$available = $charLimit - 1 - plugin_mastodon_status_text_length($link, $urlReservedLength);
		if ($available < 32) {
			return trim((string) plugin_mastodon_limit_status_text($text, max(0, $charLimit), $urlReservedLength));
		}
		$text = plugin_mastodon_limit_status_text($text, $available, $urlReservedLength) . "\n" . $link;
		return trim((string) $text);
	}

	return trim((string) plugin_mastodon_limit_status_text($text, $charLimit, $urlReservedLength));
}

/**
 * Build the status body used when exporting a FlatPress comment.
 * @param string $entryId
 * @param array<string, mixed> $entry
 * @param array<string, mixed> $comment
 * @param int $charLimit
 * @return string
 */
function plugin_mastodon_build_comment_status_text($entryId, $entry, $comment, $charLimit) {
	$name = plugin_mastodon_prepare_emoticons_for_mastodon(isset($comment ['name']) ? trim((string) $comment ['name']) : '');
	$urlReservedLength = plugin_mastodon_instance_url_reserved_length(plugin_mastodon_get_options());
	$content = isset($comment ['content']) ? trim((string) $comment ['content']) : '';
	$commentId = isset($comment ['id']) ? trim((string) $comment ['id']) : '';
	$body = plugin_mastodon_flatpress_to_mastodon($content);
	$text = $body;

	if ($name !== '' && $name !== 'Anonymous' && $name !== 'Mastodon') {
		$format = plugin_mastodon_lang_string('comment_by_format', 'Comment by %s:');
		if (strpos($format, '%s') !== false) {
			$text = sprintf($format, $name) . "\n\n" . $body;
		} else {
			$text = rtrim($format) . ' ' . $name . "\n\n" . $body;
		}
	}

	$text = trim((string) plugin_mastodon_limit_status_text($text, $charLimit, $urlReservedLength));
	$link = !empty($comment ['public_url']) ? plugin_mastodon_public_url_for_mastodon((string) $comment ['public_url']) : plugin_mastodon_public_url_for_mastodon(plugin_mastodon_public_comment_url($entryId, $entry, $commentId));
	if ($link !== '') {
		$available = $charLimit - 1 - plugin_mastodon_status_text_length($link, $urlReservedLength);
		if ($available >= 32) {
			$text = trim((string) plugin_mastodon_limit_status_text($text, $available, $urlReservedLength));
			$text = $text === '' ? $link : $text . "\n" . $link;
			return trim((string) $text);
		}
	}

	return $text;
}

/**
 * Build the FlatPress BBCode footer that links an imported entry back to its
 * source Mastodon status. The link points to the single status/toot, not to the
 * author's profile, and is opened in a new tab via FlatPress' BBCode URL
 * attributes.
 *
 * @param string $url Mastodon Status.url from the remote payload.
 * @return string
 */
function plugin_mastodon_imported_status_footer_bbcode($url) {
	$url = trim(plugin_mastodon_extract_url_token((string) $url));
	if ($url === '') {
		return '';
	}

	return '[url=' . $url . ' target=_blank rel="nofollow noopener noreferrer"]Mastodon[/url]';
}

/**
 * Build a readable account label for a Mastodon status author.
 * @param array<string, mixed> $status
 * @return string
 */
function plugin_mastodon_remote_status_author_label($status) {
	$displayName = '';
	if (!empty($status ['account'] ['display_name'])) {
		$displayName = plugin_mastodon_html_entity_decode(strip_tags((string) $status ['account'] ['display_name']));
		$displayName = trim((string) preg_replace('/\s+/u', ' ', $displayName));
	}
	$acct = '';
	if (!empty($status ['account'] ['acct'])) {
		$acct = trim((string) $status ['account'] ['acct']);
		if ($acct !== '' && strpos($acct, '@') !== 0) {
			$acct = '@' . $acct;
		}
	}
	if ($displayName !== '' && $acct !== '') {
		if (strcasecmp(ltrim($displayName, '@'), ltrim($acct, '@')) === 0) {
			return $acct;
		}
		return $displayName . ' (' . $acct . ')';
	}
	if ($displayName !== '') {
		return $displayName;
	}
	if ($acct !== '') {
		return $acct;
	}
	return 'Mastodon';
}

/**
 * Remove one leading BBCode quote block from imported comment text.
 * @param string $content
 * @return string
 */
function plugin_mastodon_strip_leading_quote_block($content) {
	$content = ltrim((string) $content);
	if ($content === '' || stripos($content, '[quote]') !== 0) {
		return trim((string) $content);
	}
	if (!preg_match_all('/\[(\/?)quote\]/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
		return trim((string) $content);
	}
	$depth = 0;
	foreach ($matches [0] as $match) {
		$token = strtolower((string) $match [0]);
		$offset = (int) $match [1];
		if ($token === '[quote]') {
			$depth++;
		} else {
			$depth--;
			if ($depth === 0) {
				$after = substr($content, $offset + strlen((string) $match [0]));
				return trim((string) $after);
			}
		}
	}
	return trim((string) $content);
}
?>
