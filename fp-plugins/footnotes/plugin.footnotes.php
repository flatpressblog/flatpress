<?php
/*
 * Plugin Name: FootNotes
 * Version: 1.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Enables footnotes in your entries. Part of the standard distribution.
 */
define('FOOTNOTES_START', '[footnotes]');

class footnotes_class {

	var $refs = false;

	var $id = 'noid';

	function __construct($id) {
		if ($id)
			$this->id = $id;
	}

	function note($n, $s) {
		$id = $this->id;
		$this->refs = true;

		return '<li>' . trim($s) . " <a id=\"$id-fn-{$n}\" href=\"#$id-rel-{$n}\" " . "title=\"Back {$n}\">^top</a>" . '</li>';
	}

	function footnotes($matches) {
		$str = '<div class="footnotes"><h4>Footnotes</h4><ol>';

		$lines = preg_split('|\[([0-9]+)\]|', $matches [1], -1, PREG_SPLIT_DELIM_CAPTURE);

		// first array element is always empty - remove
		array_shift($lines);

		// get each footnote's number ($n) and content ($s) from the array ...
		while (($n = array_shift($lines)) && ($s = array_shift($lines))) {
			// ... and add it to the result string
			$str .= $this->note($n, $s);
		}

		$str .= '</ol></div>';
		return $str;
	}

	function references($matches) {
		$n = $matches [1];

		$id = $this->id;

		$href_rel = "{$id}-rel-{$n}";
		$href_note = "{$id}-fn-{$n}";

		return "<sup><a id=\"$href_rel\" href=\"#$href_note\" title=\"note {$n}\">{$n}</a></sup>";
	}

	function headings($matches) {
		$i = 7 - strlen($matches [1]);
		return "<h$i>{$matches[2]}</h$i>";
	}

}

function plugin_footnotes_filter($text) {
	global $smarty;

	$footnotes_obj = new footnotes_class($id = $smarty->get_template_vars('id'));

	// *STRONG* emphasis
	$text = preg_replace('|(?<!\S)\*(?=\S) (?! \*) (.+?) (?<=\S) \*(?!>\w)|xs', '<strong>$1</strong>', $text);
	// _emphasis_ (italic)
	$text = preg_replace('|(?<!\S)\_(?=\S) (?! \_) (.+?) (?<=\S) \_(?!>\w)|xs', '<em>$1</em>', $text);

	// heading
	/*
	 * $text = preg_replace_callback(
	 * '(?<!\w)=(?=\S) (?! =) (.+?) (?<=\S) =(?!>\w)|xs'
	 * //'/\n(#{1,6})([^#]+)# * /',
	 * array(&$footnotes_obj, 'headings'),
	 * $text);
	 *
	 */

	$text = preg_replace_callback('|\n' . preg_quote(FOOTNOTES_START) . '\s*(.*)|sm', array(
		&$footnotes_obj,
		'footnotes'
	), $text);

	// no [footnotes] references at the bottom of the page: stops
	if (!$footnotes_obj->refs)
		return $text;

	$text = preg_replace_callback('|\[([0-9]+)\]|', array(
		&$footnotes_obj,
		'references'
	), $text);

	return $text;
}

add_filter('the_content', 'plugin_footnotes_filter', 0);
?>