<?php
/*
Plugin Name: Archives
Version: 1.0
Plugin URI: http://flatpress.sf.net
Description: Adds an Archive widget element 
Author: NoWhereMan
Author URI: http://flatpress.sf.net
*/

class plugin_archive_monthlist extends fs_filelister {
		
		var $_directory = CONTENT_DIR;
		var $_list = array();
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
					$this->_list[$this->_year] = "<li class=\"archive-year\"> <a href=\"$lnk\">20$file</a>";
					return 1;
				} elseif (is_dir($f)) {
					$this->_months[] = $file; 
					return 0;
				}
			}
		}
		
		function _exitingDir() {
			
			$y = $this->_year;
			
			if ($mos =& $this->_months) {
				sort($mos);
				$list = '';
				foreach($mos as $mth) {
					$lnk = get_month_link($y, $mth);
					$list = "<li class=\"archive-month\"><a href=\"$lnk\">". 
						strftime( '%B', mktime(0, 0, 0, $mth, 1, 0 ))
							.' </a></li>' . $list;
				}
				$list = '<ul>' . $list . '</ul>';
			}
			
			$mos = array();
			
			// we close year's li
			$this->_list[$y] .= $list . '</li>'; 
		}
		
		function getList() {
			krsort($this->_list);
			return implode($this->_list);
		}
		
		
}

function plugin_archives_widget() {

	lang_load('plugin:archives');
	global $lang;
	
	$a =& new plugin_archive_monthlist;
	
	return array(
		'subject' => $lang['plugin']['archives']['subject'],
		
		'content' => ($list = $a->getList()) ? 
						'<ul>' . $list . '</ul>' 
						: 
						"<p>{$lang['plugin']['archives']['no_posts']}</p>" 
					);		
}

register_widget('archives', 'Archives', 'plugin_archives_widget');

?>