<?php
/**
 * Plugin Name: ReadMore
 * Version: 1.0.4
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Chops lengthy entries in the overview and appends a "read more" link. Part of the standard distribution.
 */

/**
 * $MODE specifies when you want to chop your entry
 *
 * 'auto' will chop your entry at the value
 * specified in $CHOP_AT
 *
 * 'manual' will chop your entry only when a [more] tag is found in
 * the content
 *
 * 'semiauto' will chop your entry at the [more] tag. If no such a tag
 * is found, the entry is chopped at the value specified in $CHOP_AT
 *
 * 'sentence' will chop your entry after $CHOP_AT sentences
 *
 * WARNING! 'auto' and 'semiauto' modes need improvements! unclosed tags
 * at the chop point will probably result in validation errors!
 * If you're willing to improve it (using a quick but efficient algorithm
 * feel free and then let us know :) )
 *
 * we recommend using $MODE = 'manual' (SPB legacy behaviour :) )
 */

/**
 * Return a validated ReadMore mode.
 *
 * The optional argument is primarily useful to callers that need to inspect
 * ReadMore semantics without redefining PLUGIN_READMORE_MODE.
 *
 * @param string|null $mode
 * @return string
 */
function plugin_readmore_get_mode($mode = null) {
	if ($mode === null) {
		$mode = defined('PLUGIN_READMORE_MODE') ? (string) constant('PLUGIN_READMORE_MODE') : 'manual';
	} else {
		$mode = (string) $mode;
	}

	if (!in_array($mode, array('auto', 'manual', 'semiauto', 'sentence'), true)) {
		return 'manual';
	}

	return $mode;
}

/**
 * Compute the part of an already-filtered entry that remains visible in a
 * multi-entry stream.
 *
 * This contains the chopping algorithm used by plugin_readmore_main() but
 * deliberately does not build links and does not inspect the current query.
 * Other plugins can therefore reuse the exact ReadMore boundary without
 * copying its mode-specific logic.
 *
 * @param string $string Content as ReadMore receives it (normally BBCode-rendered)
 * @param string|null $mode Optional explicit mode
 * @param int|null $chopAt Optional explicit threshold
 * @return array{content:string,chopped:bool,suffix_prefix:string,mode:string}
 */
function plugin_readmore_get_stream_excerpt($string, $mode = null, $chopAt = null) {
	$string = (string) $string;
	$mode = plugin_readmore_get_mode($mode);
	$chopAt = $chopAt === null ? 4 : max(1, (int) $chopAt);

	$result = array(
		'content' => $string,
		'chopped' => false,
		'suffix_prefix' => '',
		'mode' => $mode
	);

	// Preserve the historical order: semiauto first behaves like auto and
	// only reaches the manual [more] branch when the auto condition did not chop.
	if ($mode === 'auto' || $mode === 'semiauto') {
		if (strlen($string) > $chopAt) {
			$result ['content'] = substr($string, 0, $chopAt);
			$result ['chopped'] = true;
			$result ['suffix_prefix'] = '&hellip; ';
			return $result;
		}
	}

	if ($mode === 'manual' || $mode === 'semiauto') {
		$p = strpos($string, '[more]');
		if ($p !== false) {
			$result ['content'] = substr($string, 0, $p);
			$result ['chopped'] = true;
			return $result;
		}
	} elseif ($mode === 'sentence') {
		$matches = array();
		$v = preg_match_all('|[.!?]\s|', $string, $matches, PREG_OFFSET_CAPTURE);
		if ($v && count($matches [0]) > $chopAt) {
			$result ['content'] = substr($string, 0, $matches [0] [$chopAt - 1] [1]);
			$result ['chopped'] = true;
			$result ['suffix_prefix'] = '. ';
		}
	}

	return $result;
}

function plugin_readmore_main($string) {
	global $fp_params;

	$lang = lang_load('plugin:readmore');
	$readmoreString = $lang ['plugin'] ['readmore'] ['readmore'];

	global $fpdb;
	$q = & $fpdb->getQuery();

	if (($q && !$q->single) && !isset($_GET ['page'])) {
		list ($id) = $q->getLastEntry();

		$excerpt = plugin_readmore_get_stream_excerpt($string);
		if (!empty($excerpt ['chopped'])) {
			return $excerpt ['content'] . $excerpt ['suffix_prefix'] . "<span class=\"readmore\"><a href=\"" . get_comments_link($id) . "#readmore-" . $id . "\">" . $readmoreString . "</a></span>";
		}
	}

	$entryId = isset($fp_params ['entry']) ? (string) $fp_params ['entry'] : '';
	if (($q && $q->single) || $entryId !== '') {
		$anchor = $entryId !== '' ? '<a id="readmore-' . $entryId . '"></a>' : '<a id="readmore"></a>';
		$string = str_replace('[more]', $anchor, $string);
	}

	return $string;
}

add_filter('the_content', 'plugin_readmore_main', 1);
?>
