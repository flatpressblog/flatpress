<?php
/*
 * Plugin Name: BBCode
 * Version: 1.9.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Allows using <a href="http://www.phpbb.com/phpBB/faq.php?mode=bbcode">BBCode</a> markup. Part of the standard distribution.
 */
require (plugin_getdir('bbcode') . '/inc/stringparser_bbcode.class.php');
require (plugin_getdir('bbcode') . '/panels/admin.plugin.panel.bbcode.php');

/**
 * Setups the plugin.
 */
function plugin_bbcode_startup() {
	// defintions part
	// load options
	$bbconf = plugin_getoptions('bbcode');
	// get defaults if not configured
	define('BBCODE_ALLOW_HTML', isset($bbconf ['escape-html']) ? $bbconf ['escape-html'] : true);
	define('BBCODE_ENABLE_COMMENTS', isset($bbconf ['comments']) ? $bbconf ['comments'] : false);
	define('BBCODE_USE_EDITOR', isset($bbconf ['editor']) ? $bbconf ['editor'] : true);
	define('BBCODE_MASK_ATTACHS', isset($bbconf ['maskattachs']) ? $bbconf ['maskattachs'] : true);
	define('BBCODE_URL_MAXLEN', isset($bbconf ['url-maxlen']) ? $bbconf ['url-maxlen'] : 40);
	if (!file_exists('getfile.php')) { // FKM: file not in the repo?
		define('BBCODE_USE_WRAPPER', false);
	} else {
		$funcs = explode(',', ini_get('disable_functions'));
		if (in_array('readfile', $funcs)) {
			define('BBCODE_USE_WRAPPER', false);
		} else {
			define('BBCODE_USE_WRAPPER', true);
		}
	}
	if (BBCODE_MASK_ATTACHS) {
		define('BBCODE_USE_FILEWRAPPER', true);
	} else {
		define('BBCODE_USE_FILEWRAPPER', false);
	}

	// filter part
	// add_filter('comment_text', 'plugin_bbcode_comment');
	add_filter('title_save_pre', 'wp_specialchars', 1);
	if (!BBCODE_ALLOW_HTML) {
		add_filter('content_save_pre', 'wp_specialchars', 1);
	}
	add_filter('pre_comment_author_name', 'wp_specialchars');
	add_filter('pre_comment_content', 'wp_specialchars');
	add_filter('the_content', 'BBCode', 1);
	add_filter('the_excerpt', 'BBCode', 1);
	add_filter('the_content', 'plugin_bbcode_undoHtml', 30);
	if (BBCODE_USE_EDITOR) {
		// initialize the toolbar
		add_filter('editor_toolbar', 'plugin_bbcode_toolbar');
		plugin_bbcode_init_toolbar();
	}
	if (BBCODE_ENABLE_COMMENTS) {
		add_filter('comment_text', 'plugin_bbcode_comment', 1);
		// converts BBCode [url] and [list] tags to HTML
		add_filter('comment_text', 'bbcode2html', 10);
	}
}
plugin_bbcode_startup();
// FKM: RSS-feed returns bbcode if add_action(), see #225
//add_action('wp_head', 'plugin_bbcode_startup');

/**
 * Adds the plugin's CSS and JS to the HTML head.
 */
function plugin_bbcode_head() {
	$plugindir = plugin_geturl('bbcode');
	$random_hex = RANDOM_HEX;

	echo '
		<!-- bbcode plugin -->
		<link rel="stylesheet" type="text/css" href="' . $plugindir . 'res/bbcode.css">
		<script nonce="' . $random_hex . '" src="' . $plugindir . 'res/editor.js"></script>
		<!-- end of bbcode plugin -->';
}
add_action('wp_head', 'plugin_bbcode_head');

/**
 * Remaps the URL so that there's no hint to your attachs/ directory.
 *
 * @param string $d
 * @return boolean
 */
function bbcode_remap_url(&$d) {
	// nothing to remap if given string is empty
	if (empty($d)) {
		return;
	}
	// complete the URL if it begins with www. but does not contain a protocol
	if (strpos($d, 'www.') === 0) {
		$d = 'https://' . $d;
	}
	// NWM: "attachs/" is interpreted as a keyword, and it is translated to the actual path of ATTACHS_DIR
	// CHANGE! we use the getfile.php script to mask the actual path of the attachs dir!
	// FKM: We now use a get.php script to hide the attachs directory
	// DMKE: I got an idea about an integer-id based download/media manager... work-in-progress
	if (strpos($d, ':') === false) {
		// if is relative url
		// absolute path, relative to this server
		if ($d [0] == '/') {
			/*
			 * BLOG_BASEURL contains a trailing slash in the end. If
			 * $d begins with a slash, we first strip it otherwise
			 * the string would look like
			 * http://mysite.com/flatpress//path/you/entered
			 * ^^ ugly double slash :P
			 */
			$d = BLOG_BASEURL . substr($d, 1);
		}
		if (substr($d, 0, 8) == 'attachs/') {
			$d = BBCODE_USE_FILEWRAPPER ? 'get.php?f=' . urlencode(basename($d)) : substr_replace($d, ATTACHS_DIR, 0, 8);
			return true;
		}
		if (substr($d, 0, 8) == 'attachs/') {
			$d = BBCODE_USE_WRAPPER ? 'getfile.php?f=' . basename($d) . '&amp;dl=true' : substr_replace($d, ATTACHS_DIR, 0, 8);
			return true;
		}
		if (substr($d, 0, 7) == 'images/') {
			$d = substr_replace($d, IMAGES_DIR, 0, 7);
			$d = BBCODE_USE_WRAPPER ? 'getfile.php?f=' . basename($d) : $d;
		}
		return true;
	}
	return false;
}

/**
 * Function to link documents.
 *
 * @param string $action
 * @param array $attributes
 * @param string $content
 * @param mixed $params
 *        	Not used
 * @param mixed $node_object
 *        	Not used
 * @return string
 */
function do_bbcode_url($action, $attributes, $content, $params, $node_object) {
	global $lang;
	lang_load('plugin:bbcode');
	if ($action == 'validate') {
		return true;
	}
	// the code was specified as follows: [url]http://.../[/url]
	if (!isset($attributes ['default'])) {
		// cut url if longer than > BBCODE_URL_MAXLEN
		$url = $content;
		if (($l = strlen($url)) > BBCODE_URL_MAXLEN) {
			$t = (int) (BBCODE_URL_MAXLEN / 2);
			$content = substr($url, 0, $t) . ' &hellip; ' . substr($url, $l - $t);
		}
	} else {
		// else the code was specified as follows: [url=http://.../]Text[/url]
		$url = $attributes ['default'];
	}
	$local = bbcode_remap_url($url);
	$the_url = $local ? (BLOG_BASEURL . $url) : $url;
	// DMKE: uh?
	$content = $content;
	$rel = isset($attributes ['rel']) ? ' rel="' . $attributes ['rel'] . '"' : '';
	$target = isset($attributes['target']) ? ' target="'.$attributes['target'] . '"' : '';
	$extern = !$local ? ' class="externlink" title="' . $lang ['plugin'] ['bbcode'] ['go_to'] . ' ' . $the_url . '"' : '';
	return '<a' . $extern . ' href="' . $the_url . '"' . $rel . $target .'>' . $content . '</a>';
}

/**
 * Function to include images.
 *
 * @param string $action
 * @param array $attributes
 * @param string $content
 * @param mixed $params
 *        	Not used
 * @param mixed $node_object
 *        	Not used
 * @return string
 */
function do_bbcode_img($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		return true;
	}
	if (!isset($attributes ['default'])) {
		return '[No valid img specified]';
	}
	$absolutepath = $actualpath = $attributes ['default'];
	// NWM: "images/" is interpreted as a keyword, and it is translated to the actual path of IMAGES_DIR
	$image_is_local = bbcode_remap_url($actualpath);
	$float = ' class="center" ';
	$popup_start = '';
	$popup_end = '';

	$alt = $title = basename($actualpath);
	$useimageinfo = true; // use IPTC info

	if (isset($attributes ['alt'])) {
		$alt = wp_specialchars($attributes ['alt']);
		$useimageinfo = false;
	}

	if (isset($attributes ['title'])) {
		$title = wp_specialchars($attributes ['title']);
		$useimageinfo = false;
	}

	$img_size = array();
	// let's disable socket functions for remote files
	// slow remote servers may otherwise lockup the system
	if ($image_is_local) {
		$img_info = array();
		$img_size = @getimagesize($actualpath, $img_info);
		$absolutepath = BLOG_BASEURL . $actualpath;

		if ($useimageinfo && function_exists('iptcparse')) {
			if ($img_size ['mime'] == 'image/jpeg') {
				// tiffs won't be supported

				if (is_array($img_info)) {
					if (isset($img_info ["APP13"])) {
						$iptc = iptcparse($img_info ["APP13"]);
						$title = @$iptc ["2#005"] [0] ? wp_specialchars($iptc ["2#005"] [0]) : $title;
						$alt = isset($iptc ["2#120"] [0]) ? wp_specialchars($iptc ["2#120"] [0], 1) : $title;
					}
				}
			}
		}
	}
	$orig_w = $width = isset($img_size [0]) ? $img_size [0] : 0;
	$orig_h = $height = isset($img_size [1]) ? $img_size [1] : 0;
	$thumbpath = null;
	// default: resize to 0, which means leaving it as it is, as width and hight will be ignored ;)
	$scalefact = 0;
	/*
	 * scale attribute has priority over width and height if scale is
	 * set popup is set to true automatically, unless it is explicitly
	 * set to false
	 */
	if (isset($attributes ['scale'])) {
		if (substr($attributes ['scale'], -1, 1) == '%') {
			// Format: NN%. We ignore %
			$val = substr($attributes ['scale'], 0, -1);
		} else {
			$val = $attributes ['scale'];
		}
		$scalefact = $val / 100.0;
		$width = (int) ($scalefact * $width);
		$height = (int) ($scalefact * $height);
	} elseif (isset($attributes ['width']) && isset($attributes ['height'])) {
		// if both width and height are set, we assume proportions are ok
		$width = (int) $attributes ['width'];
		$height = (int) $attributes ['height'];
	} elseif (isset($attributes ['width'])) {
		// if only width is set we calc proportions
		$scalefact = $orig_w ? ($attributes ['width'] / $orig_w) : 0;
		$width = (int) $attributes ['width'];
		$height = (int) ($scalefact * $orig_h);
	} elseif (isset($attributes ['height'])) {
		// if only height is set we calc proportions
		$scalefact = $orig_w ? ($attributes ['height'] / $orig_h) : 0;
		$height = (int) $attributes ['height'];
		$width = (int) ($scalefact * $orig_w);
	}
	if ($height < $orig_h) {
		$attributes ['popup'] = true;
	}
	if ($height != $orig_h) {
		// bbcode_img_scale_filter($actualpath, $img_props, $newsize)
		$thumbpath = apply_filters('bbcode_img_scale', $actualpath, $img_size, array(
			$width,
			$height
		));
	}

	// Calculating the "loading" attribute of the image.
	// For details, see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img#attr-loading
	// -> "lazy" is default (see https://developer.mozilla.org/en-US/docs/Web/Performance/Lazy_loading)
	$loadingValue = 'lazy';
	// Use img attribute value if explicitly set
	if (isset($attributes ['loading'])) {
		$loadingValue = $attributes ['loading'];
	}
	$loading = ' loading="' . $loadingValue . '"';

	// JS for popup
	if (isset($attributes ['popup']) && ($attributes ['popup'])) {
		$pop_width = $orig_w ? $orig_w : 800;
		$pop_height = $orig_h ? $orig_h : 600;
		$popup = ' onclick="Popup=window.open("' . $absolutepath . '","Popup","toolbar=no,location=no,status=no,"' . '"menubar=no,scrollbars=yes,resizable=yes,width=' . $pop_width . ',height=' . $pop_height . '"); return false;"';

		// Plugin hook, here lightbox attachs
		$popup = apply_filters('bbcode_img_popup', $popup, $absolutepath);
		$popup_start = $attributes ['popup'] == 'true' ? '<a title="' . $title . '" href="' . $absolutepath . '"' . $popup . '>' : '';
		$popup_end = $attributes ['popup'] == 'true' ? '</a>' : '';
	}
	$img_width = $width ? ' width="' . $width . '"' : '';
	$img_height = $height ? ' height="' . $height . '"' : '';
	if (isset($attributes ['float'])) {
		$float = ($attributes ['float'] == 'left' || $attributes ['float'] == 'right') ? ' class="float' . $attributes ['float'] . '"' : ' class="center"';
	}
	$src = $thumbpath ? (BLOG_BASEURL . $thumbpath) : $absolutepath;
	$pop = $popup_start ? '' : ' title="' . $title . '" ';

	// Finally: Put together the whole img tag with all its attributes and return it
	return $popup_start . '<img src="' . $src . '" alt="' . $alt . '" ' . $pop . $float . $img_width . $img_height . $loading . ' />' . $popup_end;
}

/**
 * Function for email links
 *
 * @param string $action
 * @param array $attr
 * @param string $content
 * @param mixed $params
 *        	Not used
 * @param mixed $node_object
 *        	Not used
 * @return string
 */
function do_bbcode_mail($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		// not used for now
		return true;
	}

	// obfuscation mode: decimal/hexadecimal ASCII randomly mixed
	$mode = 3;

	// the code was specified as follows: [mail]user@example.org[/mail]
	if (!isset($attributes ['default'])) {
		return "<a href=\"" . obfuscateEmailAddress("mailto:" . $content, $mode) . "\" class=\"maillink\">" . obfuscateEmailAddress($content, $mode) . "</a>";
	} else {
		// else the code was specified as follows: [mail=user@example.org]link text[/url]
		return "<a href=\"" . obfuscateEmailAddress("mailto:" . $attributes ['default'], $mode) . "\" class=\"maillink\">" . $content . "</a>";
	}
}

/**
 * Function for embedding videos
 *
 * @param string $action
 * @param array $attr
 * @param string $content
 * @param mixed $params
 *        	Not used
 * @param mixed $node_object
 *        	Not used
 * @return string
 */
function do_bbcode_video($action, $attr, $content, $params, $node_object) {

	global $lang;
	lang_load('plugin:bbcode');

	if ($action == 'validate') {
		return true;
	}

	$vurl = parse_url($attr ['default']);
	if (isset($attr ['type'])) {
		$type = $attr ['type'];
	} else {
		// no host: must be local file
		if (!array_key_exists('host', $vurl)) {
			$type = 'html5';
		} else {
			// is it http://www.MYSITE.com or http://MYSITE.com ?
			$web = explode('.', $vurl ['host']);
			array_pop($web);
			$type = isset($web [1]) ? $web [1] : $web [0];
		}
	}

	// Check the [video] element's attributes width, height and float
	$width = isset($attr ['width']) ? $attr ['width'] : '560';
	$height = isset($attr ['height']) ? $attr ['height'] : '315';
	$floatClass = isset($attr ['float']) ? $attr ['float'] : 'nofloat';

	$query = array();
	if (array_key_exists('query', $vurl)) {
		$query = utils_kexplode($vurl ['query'], '=&');
	}
	$output = null;

	// Set the video source from YouTube and Vimeo to data-scr if the GDPR-video-embed plugin is active
	if (function_exists('plugin_gdprvideoembed_head')) {
		$src = 'data-src';
	} else {
		$src = 'src';
	}

	// We recognize different video providers by the given video URL.
	switch ($type) {
		// YouTube
		case 'youtube':
			$output = '<div class="responsive_bbcode_video"><iframe class="bbcode_video bbcode_video_youtube ' . $floatClass . '" ' . $src . '="https://www.youtube-nocookie.com/embed/' . $query ['v'] . '" width="' . $width . '" height="' . $height . '" allow="accelerometer; autoplay; fullscreen; encrypted-media; gyroscope; picture-in-picture"></iframe></div>';
			break;
		// Vimeo
		case 'vimeo':
			$vid = isset($query ['sec']) ? $query ['sec'] : str_replace('/', '', $vurl ['path']);
			$output = '<div class="responsive_bbcode_video"><iframe class="bbcode_video bbcode_video_vimeo ' . $floatClass . '" ' . $src . '="https://player.vimeo.com/video/' . $vid . '?dnt=1?color=' . $vid . '&title=0&byline=0&portrait=0" width="' . $width . '" height="' . $height . '" allow="autoplay; fullscreen"></iframe></div>';
			break;
		// Facebook
		case 'facebook':
			$vid = isset($query ['sec']) ? $query ['sec'] : str_replace('/video/', '', $vurl ['path']);
			$output = '
				<div class="responsive_bbcode_fb-video">' . //
					'<iframe class="bbcode_video bbcode_video_facebook ' . $floatClass . '" ' . //
						'src="https://www.facebook.com/plugins/video.php?height=' . $height . '&href=https://www.facebook.com' . $vid . '&show_text=false&width=' . $width . '&t=0" ' . //
						'width="' . $width . '" ' . //
						'height="' . $height . '" ' . //
						'style="border: none; overflow: hidden" ' . //
						'allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share; fullscreen">' . //
					'</iframe>' . //
				'</div>';
			break;
		// Any video file that can be played with HTML5 <video> element
		case 'html5':
		default:
			// get the video path from the default attribute
			$videoPath = $attr ['default'];
			// if it's local ("attachs/video.mp4") ...
			$video_is_local = bbcode_remap_url($videoPath);
			if ($video_is_local) {
				// ... we need to prepend it with the blog base URL
				$videoPath = BLOG_BASEURL . $videoPath;
			}
			$output = '<div class="responsive_bbcode_video"><video class="bbcode_video bbcode_video_html5 ' . $floatClass . '" width="' . $width . '" height="' . $height . '" controls><source src="' . $videoPath . '">Your browser does not support the video tag</video></div>';
			break;
			$output = null;
	}

	if (isset($output)) {
		return $output;
	}
	return '[unsupported video]';
}

/**
 * Function to return code
 *
 * @param string $action
 * @param array $attributes
 * @param string $content
 * @param mixed $params
 *        	Not used
 * @param mixed $node_object
 *        	Not used
 * @return string
 */
function do_bbcode_code($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		return true;
	}
	$temp_str = $content;
	$temp_str = str_replace('<br />', chr(10), $temp_str);
	$temp_str = str_replace(chr(10) . chr(10), chr(10), $temp_str);
	$temp_str = str_replace(chr(32), '&nbsp;', $temp_str);
	if (BBCODE_ALLOW_HTML) {
		$temp_str = wp_specialchars($temp_str);
	}
	$a = '';
	if (function_exists('plugin_syntaxhighlighter_foot')) {
		if (isset($attributes ['default'])) {
			$a = $attributes ['default'];
			$p = explode(':', $a);
			plugin_syntaxhighlighter_add($p [0]);
		}
	}
	if ($a) {
		$a = ' class="' . $a . '"';
	}
	return '<pre' . $a . '>' . $temp_str . '</pre>';
}

/**
 * Function to return html
 *
 * @param string $action
 * @param array $attributes
 * @param string $content
 * @param mixed $params
 *        	Not used
 * @param mixed $node_object
 *        	Not used
 * @return string
 */
function do_bbcode_html($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		return true;
	}
	static $count = 0;
	// NWM: life is hell -___-'
	if (!isset($GLOBALS ['BBCODE_TEMP_HTML'])) {
		$GLOBALS ['BBCODE_TEMP_HTML'] = array();
	}
	$GLOBALS ['BBCODE_TEMP_HTML'] [$count] = $content;
	$str = "<!-- #HTML_BLOCK_{$count}# -->";
	$count++;
	return $str;
}

/**
 * Function to colorize text.
 *
 * @param string $action
 * @param array $attributes
 * @param string $content
 * @param mixed $params
 *        	Not used
 * @param mixed $node_object
 *        	Not used
 * @return string
 */
function do_bbcode_color($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		return true;
	}
	return '<span style="color:' . $attributes ['default'] . '">' . $content . '</span>';
}

/**
 * Function to set font size.
 *
 * @param string $action
 * @param array $attributes
 * @param string $content
 * @param mixed $params
 *        	Not used
 * @param mixed $node_object
 *        	Not used
 * @return string
 */
function do_bbcode_size($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		return true;
	}
	return '<span style="font-size:' . $attributes ['default'] . '">' . $content . '</span>';
}

/**
 * Function to align elements.
 *
 * @param string $action
 * @param array $attributes
 * @param string $content
 * @param mixed $params
 *        	Not used
 * @param mixed $node_object
 *        	Not used
 * @return string
 */
function do_bbcode_align($action, $attr, $content, $params, $node_object) {
	return '<div style="text-align:' . $attr ['default'] . '">' . $content . '</div>';
}

/**
 * Function to make a list.
 *
 * @param string $action
 * @param array $attributes
 * @param string $content
 * @param mixed $params
 *        	Not used
 * @param mixed $node_object
 *        	Not used
 * @return string
 */
function do_bbcode_list($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		return true;
	}
	if (isset($attributes ['default']) && $attributes ['default'] == '#') {
		$list = 'ol';
	} else {
		$list = 'ul';
	}
	return "<$list>$content</$list>";
}

/**
 * Initializes the BBCode parser.
 *
 * @return object
 */
function &plugin_bbcode_init() {
	static $bbcode = null;

	// have been here already - get outta here :)
	if (defined('BBCODE_INIT_DONE')) {
		return $bbcode;
	}

	// get the BBCode parser
	$bbcode = new StringParser_BBCode();
	$bbcode->setGlobalCaseSensitive(false); // don't care about case sensitivity: img == IMG == Img
	$bbcode->setMixedAttributeTypes(true);

	/*
	 * Tags that are same in BBCode and HTML ([i]...[/i] => <i>...</i>)
	 */
	$bbcode_tags_simple = array(
		'b' => 'strong',
		'i' => 'em',
		'quote' => 'blockquote',
		'blockquote',
		'strong',
		'em',
		'ins',
		'del',
		'hr',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6'
		// u for underlined: see below
	);
	foreach ($bbcode_tags_simple as $key => $val) {
		if (!is_numeric($key)) {
			$bbtag = $key;
			$htmltag = $val;
		} else {
			$htmltag = $bbtag = $val;
		}
		$bbcode->addCode($bbtag, 'simple_replace', null, array(
			'start_tag' => "<$htmltag>",
			'end_tag' => "</$htmltag>"
		), 'inline', array(
			'listitem',
			'block',
			'inline',
			'link'
		), array());
		$bbcode->setCodeFlag($bbtag, 'closetag', BBCODE_CLOSETAG_MUSTEXIST);
	}

	/*
	 * other tags
	 */
	// underlined text
	$bbcode->addCode('u', 'simple_replace', null, array(
		'start_tag' => '<span style="text-decoration: underline">',
		'end_tag' => '</span>'
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());

	// colored text
	$bbcode->addCode('color', 'callback_replace', 'do_bbcode_color', array(
		'usecontent_param' => array(
			'default'
		)
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->setCodeFlag('color', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);

	// sized text
	$bbcode->addCode('size', 'callback_replace', 'do_bbcode_size', array(
		'usecontent_param' => array(
			'default'
		)
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->setCodeFlag('size', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);

	// code
	$bbcode->addCode('code', 'usecontent', 'do_bbcode_code', array(), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->setCodeFlag('code', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);

	// plain html content
	$bbcode->addCode('html', 'usecontent', 'do_bbcode_html', array(), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->setCodeFlag('html', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);

	// links
	$bbcode->addCode('url', 'callback_replace', 'do_bbcode_url', array(
		'usecontent_param' => array(
			'default',
			'new'
		)
	), 'link', array(
		'listitem',
		'block',
		'inline'
	), array(
		'link'
	));

	// images
	$bbcode->addCode('img', 'callback_replace_single', 'do_bbcode_img', array(
		'usecontent_param' => array(
			'default',
			'float',
			'alt',
			'popup',
			'width',
			'height',
			'title'
		)
	), 'image', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->setCodeFlag('img', 'closetag', 'BBCODE_CLOSETAG_FORBIDDEN');

	// email links
	$bbcode->addCode('mail', // tag name: this will go between square brackets
	'callback_replace', // type of action: we'll use a callback function
	'do_bbcode_mail', // name of the callback function
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
	$bbcode->setCodeFlag('mail', 'closetag', BBCODE_CLOSETAG_MUSTEXIST); // a closing tag must exist [/tag]

	// video
	$bbcode->addCode('video', 'callback_replace_single', 'do_bbcode_video', array(
		'usecontent_param' => array(
			'default',
			'float',
			'width',
			'height'
		)
	), 'image', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->setCodeFlag('video', 'closetag', 'BBCODE_CLOSETAG_FORBIDDEN');

	// unordered and ordered list
	$bbcode->addCode('list', 'callback_replace', 'do_bbcode_list', array(
		'start_tag' => '<ul>',
		'end_tag' => '</ul>'
	), 'list', array(
		'block',
		'listitem'
	), array());
	$bbcode->setCodeFlag('list', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);
	$bbcode->setCodeFlag('list', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
	$bbcode->setCodeFlag('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
	$bbcode->setCodeFlag('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);

	// list items
	$bbcode->addCode('*', 'simple_replace', null, array(
		'start_tag' => '<li>',
		'end_tag' => '</li>'
	), 'listitem', array(
		'list'
	), array());
	$bbcode->setCodeFlag('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
	$bbcode->setCodeFlag('*', 'paragraphs', false);

	// aligned text
	$bbcode->addCode('align', 'callback_replace', 'do_bbcode_align', array(
		'usecontent_param' => array(
			'default'
		)
	), 'block', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());

	// aaaand we're done!
	define('BBCODE_INIT_DONE', true);
	$bbcode = apply_filters('bbcode_init', $bbcode);

	return $bbcode;
}

/**
 * Enter description here...
 *
 * @param string $text
 * @return string
 */
function BBCode($text) {
	$bbcode = &plugin_bbcode_init();
	return $bbcode->parse($text);
}

/**
 * Adds a Toolbar to admin panels write entry.
 *
 * @global $_FP_SMARTY
 */
function plugin_bbcode_init_toolbar() {
	global $_FP_SMARTY;
	$lang = lang_load('plugin:bbcode');
	$selection = $lang ['admin'] ['plugin'] ['bbcode'] ['editor'] ['selection'];
	// get all available images
	$indexer = new fs_filelister(IMAGES_DIR);
	$imageslist = $indexer->getList();
	// sort by name
	sort($imageslist);
	array_unshift($imageslist, $selection);
	$_FP_SMARTY->assign('images_list', $imageslist);
	// get all available attachements
	$indexer = new fs_filelister(ATTACHS_DIR);
	$attachslist = $indexer->getList();
	// sort by name
	sort($attachslist);
	array_unshift($attachslist, $selection);
	$_FP_SMARTY->assign('attachs_list', $attachslist);
}

/**
 * Simplified codes for comments.
 *
 * @param string $text
 * @return string
 */
function plugin_bbcode_comment($text) {
	$bbcode = new StringParser_BBCode();
	// If you set it to false the case-sensitive will be ignored for all codes
	$bbcode->setGlobalCaseSensitive(false);
	$bbcode->setMixedAttributeTypes(true);
	$bbcode->addCode('b', 'simple_replace', null, array(
		'start_tag' => '<strong>',
		'end_tag' => '</strong>'
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->addCode('strong', 'simple_replace', null, array(
		'start_tag' => '<strong>',
		'end_tag' => '</strong>'
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->addCode('i', 'simple_replace', null, array(
		'start_tag' => '<em>',
		'end_tag' => '</em>'
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->addCode('em', 'simple_replace', null, array(
		'start_tag' => '<em>',
		'end_tag' => '</em>'
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->addCode('ins', 'simple_replace', null, array(
		'start_tag' => '<ins>',
		'end_tag' => '</ins>'
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->addCode('u', 'simple_replace', null, array(
		'start_tag' => '<ins>',
		'end_tag' => '</ins>'
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->addCode('del', 'simple_replace', null, array(
		'start_tag' => '<del>',
		'end_tag' => '</del>'
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->addCode('strike', 'simple_replace', null, array(
		'start_tag' => '<del>',
		'end_tag' => '</del>'
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->addCode('blockquote', 'simple_replace', null, array(
		'start_tag' => '<blockquote><p>',
		'end_tag' => '</p></blockquote>'
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->addCode('quote', 'simple_replace', null, array(
		'start_tag' => '<blockquote><p>',
		'end_tag' => '</p></blockquote>'
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->addCode('pre', 'simple_replace', null, array(
		'start_tag' => '<pre>',
		'end_tag' => '</pre>'
	), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	$bbcode->addCode('code', 'usecontent', 'do_bbcode_code', array(), 'inline', array(
		'listitem',
		'block',
		'inline',
		'link'
	), array());
	return $bbcode->parse($text);
}

/**
 * Modifier BBcode to HTML for comments
 */
function bbcode2html($html) {
	$preg = array(
		// [url]
		'/(?<!\\\\)\[url(?::\w+)?\]www\.(.*?)\[\/url(?::\w+)?\]/si' => '<a href="https://www.\\1" target="_blank" class="externlink" rel="external">\\1</a>',
		'/(?<!\\\\)\[url(?::\w+)?\](.*?)\[\/url(?::\w+)?\]/si' => '<a href="\\1" target="_blank" class="externlink" rel="external">\\1</a>',
		'/(?<!\\\\)\[url(?::\w+)?=(.*?)?\](.*?)\[\/url(?::\w+)?\]/si' => '<a href="\\1" target="_blank" class="externlink" rel="external">\\2</a>',

		// [list]
		'/(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[\*(?::\w+)?\](.*?)(?=(?:\s*<br\s*\/?>\s*)?\[\*|(?:\s*<br\s*\/?>\s*)?\[\/?list)/si' => '<li>\\1</li>',
		'/(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[\/list(:(?!u|o)\w+)?\](?:<br\s*\/?>)?/si' => '</ul>',
		'/(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[\/list:o(:\w+)?\](?:<br\s*\/?>)?/si' => '</ol>',
		'/(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[list(:(?!u|o)\w+)?\]\s*(?:<br\s*\/?>)?/si' => '<ul class="list-unordered">',
		'/(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[list(?::o)?(:\w+)?=#\]\s*(?:<br\s*\/?>)?/si' => '<ol class="list-ordered">',
	);
	$html = preg_replace(array_keys($preg), array_values($preg), $html);
	return $html;
}

/**
 * This is for [html] tag postprocess
 *
 * @param unknown_type $match
 * @return unknown
 */
function plugin_bbcode_undoHtmlCallback($match) {
	// builtin function (see core.wp-formatting)
	return clean_pre($match [1]);
}

/**
 * Enter description here...
 *
 * @param string $text
 * @return string
 */
function plugin_bbcode_undoHtml($text) {
	// return preg_replace_callback('|<!-- BEGOFHTML -->(.*)<!-- EOFHTML -->|sU', 'plugin_bbcode_undoHtmlCallback', $text);
	if (isset($GLOBALS ['BBCODE_TEMP_HTML'])) {
		foreach ($GLOBALS ['BBCODE_TEMP_HTML'] as $n => $content) {
			// html_entity_decode($content)
			$content = str_replace('&lt;', '<', $content);
			$content = str_replace('&gt;', '>', $content);
			$text = str_replace("<!-- #HTML_BLOCK_{$n}# -->", $content, $text);
		}
		$GLOBALS ['BBCODE_TEMP_HTML'] = array();
	}
	return $text;
}

// ------------------------------------------------------------------------------
// obfuscate mail adresses
// ------------------------------------------------------------------------------
/**
 * Obfuscates the given email adress with the given mode.
 * Thanks for spam-me-not.php to Rolf Offermanns!
 * Spam-me-not in JavaScript: http://www.zapyon.de
 *
 * @param string $originalString
 *        	the email adress to obfuscate
 * @param int $mode
 *        	the mode (1: decimal ASCII; 2: hexadecimal ASCII; 3: decimal/hexadecimal ASCII randomly mixed)
 * @return string
 */
function obfuscateEmailAddress($originalString, $mode) {
	$encodedString = "";
	$nowCodeString = "";

	$originalLength = strlen($originalString);
	$encodeMode = $mode;

	for($i = 0; $i < $originalLength; $i++) {
		if ($mode == 3) {
			$encodeMode = rand(1, 2);
		}
		switch ($encodeMode) {
			case 1: // Decimal code
				$nowCodeString = "&#" . ord($originalString [$i]) . ";";
				break;
			case 2: // Hexadecimal code
				$nowCodeString = "&#x" . dechex(ord($originalString [$i])) . ";";
				break;
			default:
				return "ERROR: wrong encoding mode.";
		}
		$encodedString .= $nowCodeString;
	}
	return $encodedString;
}
