<?php
/**
 * Plugin Name: Archives
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Adds an Archive widget element. Part of the standard distribution.
 * Version: 1.1.1
 */
class plugin_archives_monthlist extends fs_filelister {

	var $_directory = CONTENT_DIR;

	var $_list = array();

	var $_htmllist = array();

	var $_months = array();

	var $_year = '';

	function _checkFile($directory, $file) {
		$f = $directory . "/" . $file;

		if (ctype_digit($file)) {
			if ($this->_directory === $directory) {
				// add year to the list (do not closes li, because
				// we may have nested elements)
				$this->_year = $file;
				$lnk = get_year_link($file);
				$this->_htmllist[$this->_year] = "<li class=\"archive-year archive-y20" . $file . "\"> <span role=\"button\" class=\"togglelink toggleplus\" aria-expanded=\"false\" title=\"Expand\">&#9656; </span> <a href=\"" . $lnk . "\">20" . $file . "</a>";
				return 1;
			} elseif (is_dir($f)) {
				$this->_months [] = $file;
				return 0;
			}
		}
	}

	function _exitingDir($directory = null, $file = null) {
		$y = $this->_year;

		if ($mos = & $this->_months) {
			sort($mos);
			$list = '';
			$linearlist = array();
			foreach ($mos as $mth) {
				$lnk = get_month_link($y, $mth);
				$the_month = theme_date_format(mktime(0, 0, 0, $mth, 1, 0), '%B');
				$list = "<li class=\"archive-month archive-m" . $mth . "\"><a href=\"" . $lnk . "\">" . $the_month . ' </a></li>' . $list;
				$linearlist[$the_month . " 20" . $this->_year] = $lnk;
			}
			$list = '<ul>' . $list . '</ul>';
		}

		$mos = array();

		// we close year's li
		$this->_list [$y] = $linearlist;
		$this->_htmllist [$y] .= $list . '</li>';
	}

	function getList() {
		krsort($this->_list);
		return $this->_list;
	}

	function getHtmlList() {
		krsort($this->_htmllist);
		return implode($this->_htmllist);
	}

}

// BOF Caching helpers
function plugin_archives_cache_ns() {
	static $ns = null;
	if ($ns !== null) {
		return $ns;
	}
	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
	if (!$apcu_on) {
		return $ns = '';
	}
	$v = apcu_fetch('fp:archives:v');
	if (!$v) {
		$v = 1;
		@apcu_store('fp:archives:v', $v);
	}
	return $ns = ':v' . (int)$v;
}

// Call this from entry/comment save/delete to invalidate caches.
function plugin_archives_cache_bump() {
	if (!(function_exists('is_apcu_on') && is_apcu_on())) {
		return;
	}
	$ok = false;
	apcu_inc('fp:archives:v', 1, $ok);
	if (!$ok) {
		@apcu_store('fp:archives:v', 1);
	}
}
add_filter('comment_save', 'plugin_archives_cache_bump');
add_filter('comment_deleted', 'plugin_archives_cache_bump');

function plugin_archives_mtime_sig() {
	$root = rtrim(CONTENT_DIR, '/');
	$rt = (int) @filemtime($root);
	$maxY = 0; $maxM = 0;
	if (@is_dir($root) && ($h = @opendir($root))) {
		while (false !== ($y = readdir($h))) {
			if ($y === '.' || $y === '..') {
				continue;
			}
			if (!(ctype_digit($y) && strlen($y) === 4)) {
				continue;
			}
			$ydir = $root . '/' . $y;
			if (!@is_dir($ydir)) {
				continue;
			}
			$ty = @filemtime($ydir);
			if ($ty && $ty > $maxY) {
				$maxY = $ty;
			}
			if (($hh = @opendir($ydir))) {
				while (false !== ($m = readdir($hh))) {
					if ($m === '.' || $m === '..') {
						continue;
					}
					if (!(ctype_digit($m) && strlen($m) === 2 && $m >= '01' && $m <= '12')) {
						continue;
					}
					$mdir = $ydir . '/' . $m;
					if (!@is_dir($mdir)) {
						continue;
					}
					$tm = @filemtime($mdir);
					if ($tm && $tm > $maxM) {
						$maxM = $tm;
					}
				}
				@closedir($hh);
			}
		}
		@closedir($h);
	}
	return $rt . ':' . $maxY . ':' . $maxM;
}

function plugin_archives_mtime_sig_cached() {
	static $sig = null;
	if ($sig !== null) {
		return $sig;
	}
	return $sig = plugin_archives_mtime_sig();
}

function plugin_archives_cached_list() {
	static $local = null;
	if ($local !== null) {
		return $local;
	}
	global $fp_config;
	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
	$ns = plugin_archives_cache_ns();
	$sig = plugin_archives_mtime_sig_cached();
	$key = 'fp:archives:list' . $ns . ':' . $sig;
	if ($apcu_on) {
		$hit = false;
		$val = apcu_fetch($key, $hit);
		if ($hit && is_array($val)) {
			return $local = $val;
		}
	}
	$obj = new plugin_archives_monthlist();
	$list = $obj->getList();
	$local = $list;
	if ($apcu_on) {
		@apcu_store($key, $list, 900);
	}
	return $list;
}

function plugin_archives_cached_html() {
	static $local = null;
	if ($local !== null) {
		return $local;
	}
	global $fp_config;
	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
	$ns = plugin_archives_cache_ns();
	$sig = plugin_archives_mtime_sig_cached();
	$key = 'fp:archives:html' . $ns . ':' . $sig;
	if ($apcu_on) {
		$hit = false; $val = apcu_fetch($key, $hit);
		if ($hit && is_string($val)) {
			return $local = $val;
		}
	}
	$obj = new plugin_archives_monthlist();
	$html = $obj->getHtmlList();
	$local = $html;
	if ($apcu_on) {
		@apcu_store($key, $html, 900);
	}
	return $html;
}
// EOF Caching helpers

function plugin_archives_head() {
	$random_hex = RANDOM_HEX;
	$pdir = plugin_geturl('archives');
	$css = utils_asset_ver($pdir . 'res/togglearchive.css', SYSTEM_VER);
	$js = utils_asset_ver($pdir . 'res/togglearchive.js', SYSTEM_VER);

	echo '
		<!-- archives -->
		<script nonce="' . $random_hex . '" src="' . $js . '" defer></script>
		<link rel="stylesheet" type="text/css" href="' . $css . '">
	';

	foreach (plugin_archives_cached_list() as $y => $months) {
		foreach ($months as $ttl => $link) {
			echo "
			<link rel=\"archives\" title=\"" . $ttl . "\" href=\"" . $link . "\">";
		}
	}

	echo '
		<!-- end of archives -->' . "\n";
}
add_filter('wp_head', 'plugin_archives_head');

function plugin_archives_footer() {
	$random_hex = RANDOM_HEX;

	echo '
		<!-- archives -->
		<script nonce="' . $random_hex . '">
			/**
			 * Making the archive widget interactive
			 */
			var pluginArchive = \'\';
			function toggleArchive(pdir) {
				pluginArchive = pdir;
				$(document).ready(function () {
					$(\'#widget-archives ul > li.archive-year, #footernav ul > li.archive-year\') .each(function (index) {
						const uniqueId = \'archive-\' + index;
							$(this).find(\'.togglelink\').attr(\'aria-controls\', uniqueId);
						$(this).children(\'ul\').attr(\'id\', uniqueId);
						const toggleEl = $(this).children(\'.togglelink\')[0];
						toggle(toggleEl);
						$(toggleEl).click(function () {
							toggle(this);
							return false;
						});
					});
				});
			}
		</script>
		<!-- end of archives -->
	';
}
add_filter('wp_footer', 'plugin_archives_footer');

function plugin_archives_widget() {
	lang_load('plugin:archives');
	global $lang;

	return array(
		'subject' => $lang ['plugin'] ['archives'] ['subject'],

		'content' => (($list = plugin_archives_cached_html())) ? '<ul>' . $list . '</ul>' : "<p>" . $lang ['plugin'] ['archives'] ['no_posts'] . "</p>"
	);
}
register_widget('archives', 'Archives', 'plugin_archives_widget');
?>
