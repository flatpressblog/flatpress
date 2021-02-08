<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File: function.list_categories.php
 * Type: function
 * Name: list_categories
 * Purpose: print out the comment form
 *
 * @param
 *        	string after
 * @param
 *        	string before
 *        	-------------------------------------------------------------
 */
function smarty_function_list_categories($params) // , &$smarty)
{
	$cat_params = array(
		'ild' => '<li>',
		'ird' => "</li>\n",
		'old' => "<ul>\n",
		'ord' => "</ul>\n",
		'name' => isset($params ['name']) ? $params ['name'] : '',
		'selected' => array()
	);

	$cat_params = array_merge($cat_params, $params);

	// makese 'selected' an arr
	$cat_params ['selected'] = array_key_exists('selected', $params) ? (array) $params ['selected'] : array();

	// echo "<pre>" . print_r(entry_categories_get()) . "</pre>";

	if (file_exists(CONTENT_DIR . 'categories.txt')) {
		$cats = trim(io_load_file(CONTENT_DIR . 'categories.txt'));
		$stack = array(
			0
		);
		$arr = array();

		$line36error = explode("\n", $cats);
		$line35error = '<ul>' . do_print_categories_list($line36error, $stack, $arr, $cat_params) . '</ul>';

		return $line35error;
	} else {
		global $lang;

		$content = '<a href="' . BLOG_BASEURL . '">Unfiled</a>';
		if (isset($lang ['admin'] ['entry'] ['publish'] ['nocategories']))
			$content = $lang ['admin'] ['entry'] ['publish'] ['nocategories'];
		return '<ul><li>' . $content . '</li></ul>';
	}

	// <label><input name="cats[{$catId}]"
	// {if (bool)array_intersect(array($catId),$categories) }
	// checked="checked"{/if} type="checkbox" /> {$cat} </label><br />
}

function do_print_categories_list(&$lines, &$indentstack, &$result, $params) {
	global $smarty, $fpdb;

	extract($params);

	if (empty($lines)) {
		$l = count($indentstack) - 1;
		if ($l > 0)
			$arr = array_fill(0, $l, $ord . $ird);
		else
			$arr = array();

		$result = array_merge($result, $arr);
		return '';
	}

	$str = '';
	$v = reset($lines);
	$vt = ltrim($v);

	$indent = utils_countdashes($vt, $text);
	$indent_old = end($indentstack);

	$val = explode(':', $text);
	$vt = $val [0];
	$vid = trim($val [1]);

	$catname = $params ['name'];

	if ($indent > $indent_old) {
		array_push($indentstack, $indent);

		array_pop($result);
		array_push($result, $old);
		// array_push($result, $ild);
		do_print_categories_list($lines, $indentstack, $result, $params);
	} elseif ($indent < $indent_old) {
		array_pop($indentstack);

		array_push($result, $ord);
		array_push($result, $ird);

		do_print_categories_list($lines, $indentstack, $result, $params);
	} else {
		array_push($result, $ild);

		$cat_entry = $params ['selected'];

		if (isset($params ['type']) && ($params ['type'] == 'form' || $params ['type'] == 'check')) {
			$string = '<label><input name="' . $catname . 'cats[' . $vid . ']" ';

			if ((bool) array_intersect(array(
				$vid
			), $cat_entry))
				$string .= 'checked="checked" ';

			$string .= 'type="checkbox" />';
			$before = $string;
		} elseif (isset($params ['type']) && $params ['type'] == 'radio') {
			$string = '<label><input name="' . $catname . 'cats" type="radio" value="' . $vid . '"';
			if ((bool) array_intersect(array(
				$vid
			), $cat_entry))
				$string .= 'checked="checked" ';

			$string .= ' />';
			$before = $string;
		} elseif (isset($params ['type']) && $params ['type'] == 'linked') {
			$before = '<a href="' . get_category_link($vid) . '">';
		}

		array_push($result, $before);

		array_push($result, $vt);

		if (isset($params ['type']) && ($params ['type'] == 'form' || $params ['type'] == 'check' || $params ['type'] == 'radio')) {
			$string = '</label>';
			$after = $string;
		} elseif (isset($params ['type']) && $params ['type'] == 'linked') {
			$after = '</a>';
			if (isset($params ['count']) && $params ['count']) {
				$index = & $fpdb->get_index($vid);
				$count = ($index) ? $index->length() : 0;
				$after = " ($count) " . $after;
			}
		}

		array_push($result, $after);

		array_push($result, $ird);
		array_shift($lines);
		do_print_categories_list($lines, $indentstack, $result, $params);
	}

	return implode($result);
}
