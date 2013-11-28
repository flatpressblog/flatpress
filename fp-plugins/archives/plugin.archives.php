<?php
/*
Plugin Name: Archives
Version: 1.0
Plugin URI: http://flatpress.sf.net
Description: Adds an Archive widget element 
Author: NoWhereMan
Author URI: http://flatpress.sf.net
*/

class plugin_archives_monthlist extends fs_filelister {
		
		var $_directory = CONTENT_DIR;
		var $_list = array();
		var $_htmllist = array();
		var $_months = array();
		var $_year = '';
		
		function _checkFile($directory, $file) {
			$f = "$directory/$file";
			
			if (ctype_digit($file)) {
				if ($this->_directory === $directory) {
					// add year to the list (do not closes li, because
					// we may have nested elements)
					$this->_year = $file;
					$lnk = get_year_link($file);
					$this->_htmllist[$this->_year] = "<li class=\"archive-year archive-y20$file\"> <a href=\"$lnk\">20$file</a>";
					return 1;
				} elseif (is_dir($f)) {
					$this->_months[] = $file; 
					return 0;
				}
			}
		}
		
		function _exitingDir($directory = null, $file=null) {
			
			$y = $this->_year;
			
			if ($mos =& $this->_months) {
				sort($mos);
				$list = '';
				$linearlist = array();
				foreach($mos as $mth) {
					$lnk = get_month_link($y, $mth);
					$the_month = theme_date_format(  mktime(0, 0, 0, $mth, 1, 0 ), '%B');
					$list = "<li class=\"archive-month archive-m$mth\"><a href=\"$lnk\">". 
							$the_month
						.' </a></li>' . $list;
					$linearlist["$the_month 20{$this->_year}"] = $lnk;
				}
				$list = '<ul>' . $list . '</ul>';
			}
			
			$mos = array();
			
			// we close year's li
			$this->_list[$y] = $linearlist;
			$this->_htmllist[$y] .= $list . '</li>'; 
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


function plugin_archives_head() {

	global $PLUGIN_ARCHIVES_MONTHLIST;
	$PLUGIN_ARCHIVES_MONTHLIST = new plugin_archives_monthlist;

	echo "\n<!-- archives -->\n";
	foreach($PLUGIN_ARCHIVES_MONTHLIST->getList() as $y => $months) {
		foreach ($months as $ttl => $link)
			echo "<link rel=\"archives\" title=\"{$ttl}\" href=\"{$link}\" />\n";
	}

	echo "\n<!-- end of archives -->\n";
}
add_filter('wp_head', 'plugin_archives_head');

function plugin_archives_widget() {

	lang_load('plugin:archives');
	global $lang, $PLUGIN_ARCHIVES_MONTHLIST;
	
	
	return array(
		'subject' => $lang['plugin']['archives']['subject'],
		
		'content' => ($list = $PLUGIN_ARCHIVES_MONTHLIST->getHtmlList()) ? 
						'<ul>' . $list . '</ul>' 
						: 
						"<p>{$lang['plugin']['archives']['no_posts']}</p>" 
					);		
}

register_widget('archives', 'Archives', 'plugin_archives_widget');

?>
