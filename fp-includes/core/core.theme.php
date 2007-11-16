<?php
	
	function theme_loadsettings() {
		
		global $fp_config, $theme, $FLATPRESS;
		
		$theme = array(
			// name of the theme
			'name' 			=> 'theme',
			// author of the theme
			'author' 		=> 'anonymous',
			// theme website
			'www' 			=> 'http://flatpress.nowhereland.it',
			// fp version
			'version'		=> -1,
			// default style (must be in res/ dir
			
			'style' => array(
			
				'style_def' 		=> 'style.css',
				// default style for admin panel (usually it's the same of the theme)
				'style_admin' 		=> 'style.css',
			), 
			
			// if false a default css is used to style some elements of the panel
			// if true, we'll suppose these elements are already styled in your own css's
			'admin_custom_interf'	=> false
		);
	
		
		if (!defined('THE_THEME'))
			define('THE_THEME', $fp_config['general']['theme']);
			
		
		// backward compatibility:
		$conf1 = THEMES_DIR . THE_THEME . '/theme_conf.php';
		
		// new naming convention. Yeah, I know, just an underscore
		// instead of the dot, so? It is more "consistent" :D
		$conf2 = THEMES_DIR . THE_THEME . '/theme.conf.php';
		
		if (file_exists($conf2)) {
			include($conf2);
		} elseif (file_exists($conf1)) {
			include($conf1);
		}
		
		
		if (!defined('THEME_LEGACY_MODE')) {
			if ($theme['version'] < 0.702) {
				define('THEME_LEGACY_MODE', true);
				theme_register_default_widgetsets();
			} else {
				define('THEME_LEGACY_MODE', false);
 
				if (isset($theme['default_style'])) {
				
					if (!isset($fp_config['general']['style']))
						$fp_config['general']['style'] = $theme['default_style'];


					include(THEMES_DIR . THE_THEME . "/{$fp_config['general']['style']}/style.conf.php");

					$theme['style'] = $style; 

				} else {
					
					
					$theme['style'] = array(
						
						'style_def' 	=> $theme['style_def']? $theme['style_def'] : 'style.css',
						'style_admin'	=> $theme['style_admin']? $theme['style_admin'] : 'style.css',

					);


				}
				
			}

			// no widgets registered, load default set	
			if (!get_registered_widgets())
				theme_register_default_widgetsets();

		}
				
		return $theme;
		
	}
	
	function theme_register_default_widgetsets() {
		register_widgetset('left');
		register_widgetset('right');
		register_widgetset('top');
		register_widgetset('bottom');
	}

	function theme_getdir($id = THE_THEME) {
		return THEMES_DIR . ($id) . '/';
	}
	
	function theme_exists($id) {
		$f = theme_getdir($id); 
		if (file_exists($f))
			return $f;
		
		return '';
	}
	
	function theme_style_exists($id, $themeid=THE_THEME) {
		if ($f = theme_exists($themeid)) {
			$fs = $f . ($id) . '/';
			if (file_exists($fs))
				return $fs;
		}
		return '';
			
	}
	
	function theme_geturl($id = THE_THEME) {
		return BLOG_BASEURL . THEMES_DIR . $id . '/';
	}
	
	function theme_style_geturl($style, $id=THE_THEME) {
		return theme_geturl($id) . $style . '/';
	}

 
	function theme_list() {
		$dir = THEMES_DIR;
		$dh  = opendir($dir);
		$i = 0;
		while (false !== ($filename = readdir($dh))) {
			if ( ($filename != '.') && ($filename != '..') ) {
				$files[$i++] = $filename;
			}
		}
		sort($files);
		return $files;
	}
	
     
	function theme_wp_head() {
		global $fp_config;
		
		echo "\n<!-- FP STD HEADER -->\n";
		
		// echo "\n<link media=\"screen\" href=\"interface/templates/form.css\" type=\"text/css\" rel=\"stylesheet\" />";
		
		echo "\n<meta name=\"generator\" content=\"FlatPress ". system_ver() ."\" />\n";
		//echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"Get RSS 2.0 Feed\" href=\"{$fp_config['general']['www']}rss.php\" />\n";
		echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"Get RSS 2.0 Feed\" href=\"".
			theme_feed_link('rss2') 
		."\" />\n";
		echo "<link rel=\"alternate\" type=\"application/atom+xml\" title=\"Get Atom 1.0 Feed\" href=\"".
			theme_feed_link('atom') 
		."\" />\n";
	
	
		echo "<!-- EOF FP STD HEADER -->\n";
	}
	
	function theme_head_stylesheet() {
		
		global $fp_config, $theme;
		
		echo "\n<!-- FP STD STYLESHEET -->\n";
		
		echo '<link media="screen" href="';
		echo BLOG_BASEURL . THEMES_DIR . THE_THEME;
		
		
		$css = defined('MOD_ADMIN_PANEL')?
			 $theme['style']['style_admin'] : $theme['style']['style_def'];
		
		$substyle = '/'. (isset($fp_config['general']['style'])? $fp_config['general']['style'].'/' : '');
		
		echo $substyle . 'res/'. $css .'" type="text/css" rel="stylesheet" />';
		
		echo "\n<!-- FP STD STYLESHEET -->\n";
		

	}

	function admin_head_action() {
		global $theme;
		if (!$theme['admin_custom_interface'])
			echo '<link media="screen" href="'.BLOG_BASEURL.'admin/res/admin.css" type="text/css" rel="stylesheet" />';
	}
		
	add_filter('admin_head', 'admin_head_action');



	
	add_action('wp_head', 'theme_wp_head');
	add_action('wp_head', 'theme_head_stylesheet');


	
	
		function get_wp_head() {
			do_action('wp_head');
			if (class_exists('AdminPanel'))
				do_action('admin_head');
		}
		
		$smarty->register_function('header', 'get_wp_head');
		
	
	function theme_wp_footer() {
		global $fp_config;
		echo $fp_config['general']['footer'];
	}
	
	add_action('wp_footer', 'theme_wp_footer');	
	
		function get_wp_footer() {
			do_action('wp_footer');
		}
		
		$smarty->register_function('footer', 'get_wp_footer');
	
	
	function theme_charset() {
		global $fp_config;
		header('Content-Type: text/html; charset='. $fp_config['general']['charset']);
		
	}
			
	add_action('init', 'theme_charset');
	
	function theme_init(&$smarty) { /* &$mode */
		
		global $fp_config;
		global $lang;
		global $theme;
		
		// avoid compiled tpl collision (i.e. change theme without this and cry)
		$smarty->compile_id = md5($fp_config['general']['theme']);
		$smarty->template_dir = THEMES_DIR . $fp_config['general']['theme'] . '/';
		
		$loggedin = user_loggedin();
		
		$flatpress = $fp_config['general'];
		// retained for compatibility 
		// todo: ugly, clean this up
		// smarty has constant facilities included ^_^
		//$flatpress['FP_INTERFACE']	= FP_INTERFACE;
		//$flatpress['BLOGURL']	= BLOG_BASEURL;
		
		
		$flatpress['loggedin']	= $loggedin;
		
		if ($loggedin)
			$flatpress['user'] = user_get();
		
		// useful shorthand for themes
		// e.g. {$flatpress.themeurl}imgs/myimage.png
		
		if (isset($fp_config['general']['style'])) {
			$themeurl = theme_style_geturl($fp_config['general']['style']);
		} else {
			$themeurl = theme_geturl();
		}
		
		$flatpress['themeurl'] = $themeurl;
		
		$flatpress_upper = array_change_key_case($flatpress, CASE_UPPER);
		
		$flatpress = array_merge($flatpress, $flatpress_upper);
					
		$smarty->assign('flatpress', $flatpress);
		
		$smarty->assign('lang', $lang);
		
		$smarty->assign('blogtitle', $fp_config['general']['title']);

		$smarty->assign('pagetitle', 
				apply_filters('wp_title', "", '&raquo;'));
				
		$smarty->assign_by_ref('fp_config', $fp_config);
				
		
		$smarty->register_modifier('tag', 'theme_apply_filters_wrapper');
		$smarty->register_modifier('link', 'theme_apply_filters_link_wrapper');
		$smarty->register_modifier('filed', 'theme_entry_categories');
		
		
		if (!isset($_GET['feed']) || empty($_GET['feed'])) {
			$smarty->register_modifier('date_format_daily', 'theme_smarty_modifier_date_format_daily');
			$smarty->register_modifier('date_format', 'theme_smarty_modifier_date_format');
		}
		
		$smarty->register_modifier('date_rfc3339', 'theme_smarty_modifier_date_rfc3339');
		
		$smarty->register_function('action', 'theme_smarty_function_action');
		
		
	}
	
	
		function smarty_block_page($params, $content) {
			return $content;
		}
		
		$smarty->register_block('page', 'smarty_block_page');
	
	function theme_apply_filters_wrapper($var, $hook) {
		$args = func_get_args();
		$tmp = $args[0];
		$args[0] = $args[1];
		$args[1] = $tmp;
		return call_user_func_array('apply_filters', $args);
	}
	
	
	function theme_apply_filters_link_wrapper($var, $hook) {
		return apply_filters($hook, '', $var);
	}
	
	
	
	function theme_smarty_function_action($params, &$smarty) {
		if (isset($params['hook']))
			do_action($params['hook']);
			
	}
	
	function theme_smarty_modifier_date_format(	$string,
											$format = null,
											$default_date = ''
											) {										
		global $lang;
		
		if ($string != '') {
			$timestamp = $string; // smarty_make_timestamp($string);
		} elseif ($default_date != '') {
			$timestamp = $default_date; // smarty_make_timestamp($default_date);
		} else {
			return;
		}
		
		if (is_null($format)) {
			global $fp_config;
			$format = $fp_config['locale']['timeformat'];
		}
		
		
			// D l day
			
			if ( strpos($format, '%a') !== false ) {
				$i = strftime('%u', $timestamp);
				$format = str_replace('%a', $lang['date']['weekday_abbr'][$i], $format);
			}
			
			if ( strpos($format, '%A') !== false  ) {
				$i = strftime('%u', $timestamp);
				$format = str_replace('%A', $lang['date']['weekday'][$i], $format);
			}
			
			
			// F M month
			
			if ( strpos($format, '%b') !== false  ) {
				$i = intval(strftime('%m', $timestamp))-1;
				$format = str_replace('%b', $lang['date']['month_abbr'][$i], $format);
			}
			
			
			if ( strpos($format, '%B') !== false  ) {
				$i = intval(strftime('%m', $timestamp))-1;
				$format = str_replace('%B', $lang['date']['month'][$i], $format);
			}
			
				if (DIRECTORY_SEPARATOR == '\\') {
				$_win_from = array('%D',       '%h', '%n', '%r',          '%R',    '%t', '%T');
				$_win_to   = array('%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S');
					if (strpos($format, '%e') !== false) {
						$_win_from[] = '%e';
						$_win_to[]   = sprintf('%\' 2d', date('j', $timestamp));
					}
					if (strpos($format, '%l') !== false) {
						$_win_from[] = '%l';
						$_win_to[]   = sprintf('%\' 2d', date('h', $timestamp));
					}
					$format = str_replace($_win_from, $_win_to, $format);
				}
				
			return strftime($format, $timestamp);
			
		
    } 
	
	
	function theme_smarty_modifier_date_format_daily(
												$string,
												$format = null,
												$default_date = ''
												) {
	
	global $THEME_CURRENT_DAY, $lang, $fp_config;
	
	if (is_null($format))
		$format = $fp_config['locale']['dateformat'];

	$current_day = theme_smarty_modifier_date_format($string, $format, $default_date);
    
    if (!isset($THEME_CURRENT_DAY) || $THEME_CURRENT_DAY != $current_day) {
		$THEME_CURRENT_DAY = $current_day;
		
		return $current_day;
		
	}
	
	return '';
    
	}
	
	
	/**
	* Get date in RFC3339
	* For example used in XML/Atom
	*
	* @param integer $timestamp
	* @return string date in RFC3339
	* @author Boris Korobkov
	* @see http://tools.ietf.org/html/rfc3339
	*
	* http://it.php.net/manual/en/function.date.php#75757
	*
	*/
		
	function theme_smarty_modifier_date_rfc3339($timestamp='') {
		
		if (!$timestamp) {
        	$timestamp = time();
    	}
    	
    	$date = date('Y-m-d\TH:i:s', $timestamp);

	    $matches = array();
	    if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches)) {
	        $date .= $matches[1].$matches[2].':'.$matches[3];
	    } else {
	        $date .= 'Z';
	    }
	    return $date;

	}


	
	
	
	// {{{ permalink, commentlink, staticlink:  filters
	
	add_filter('feed_link', 'theme_def_feed_link', 0, 2);
	function theme_def_feed_link($str, $type) {
		return BLOG_BASEURL . "?feed={$type}";
	}
	function theme_feed_link ($feed='rss2') {
		return apply_filters('feed_link', '', $feed);
	}
	
	add_filter('post_comments_feed_link', 'theme_def_feed_comments_link', 0, 3);
	function theme_def_feed_comments_link($str, $feed, $id) {
		return BLOG_BASEURL . "?entry=$id&amp;comments&amp;feed={$feed}";
	}
	function theme_comments_feed_link ($feed='rss2', $id) {
		return apply_filters('post_comments_feed_link', '', $feed, $id);
	}
	
	 
	add_filter('post_link', 'theme_def_permalink', 0, 2);
	function theme_def_permalink($str, $id) {
		return BLOG_BASEURL . "?entry=$id";
	}
	function get_permalink ($id) {
		return apply_filters('post_link', '', $id);
	}
	
	add_filter('comments_link', 'theme_def_commentlink', 0, 2);
	function theme_def_commentlink($str, $id) {
		return BLOG_BASEURL . "?entry=$id&amp;comments";
	}
	function get_comments_link ($id) {
		return apply_filters('comments_link', '', $id);
	}
	

	add_filter('page_link', 'theme_def_staticlink', 0, 2);
	function theme_def_staticlink($str, $id) {
		return BLOG_BASEURL . "?page=$id";
	}
	function theme_staticlink ($id) {
		return apply_filters('page_link', '', $id);
	}
	
	add_filter('category_link', 'theme_def_catlink', 0, 2);
	function theme_def_catlink($str, $catid) {
		return BLOG_BASEURL . "?cat=$catid";
	}
	function get_category_link($catid) {
		return apply_filters('category_link', '', $catid);
	}
	
	
	function get_year_link($year) {
		return wp_specialchars(
			apply_filters(
				'year_link', 
				BLOG_BASEURL . '?y='. str_pad($year, 2, '0', STR_PAD_LEFT), 
				$year)
		);
	}
	
	function get_month_link($year, $month) {
		return wp_specialchars(
			apply_filters(
				'month_link',  
				BLOG_BASEURL . '?y='. str_pad($year, 2, '0', STR_PAD_LEFT) . 
				'&m=' . str_pad($month, 2, '0', STR_PAD_LEFT),
				$year, 
				$month)
			);
	}
	
	function get_day_link($year, $month, $day) {
		return wp_specialchars(
			apply_filters(
				'month_link',  
				BLOG_BASEURL	. '?y='. str_pad($year, 2, '0', STR_PAD_LEFT) 
								. '&m=' . str_pad($month, 2, '0', STR_PAD_LEFT) 
								. '&d=' . str_pad($day, 2, '0', STR_PAD_LEFT),
				$year, 
				$month)
		);
	}


	
	// }}}
	
	
	function theme_entry_commentcount($count) {
		global $lang;
		switch ($count) {  
			case 0: 	return $comments = $lang['main']['nocomments'];
			case 1:		return $comments = $lang['main']['comment'];
			default:	return $comments = $count . ' ' . $lang['main']['comments'];
		}
		
	}
	add_filter('comments_number', 'theme_entry_commentcount');
	
	
	function theme_entry_categories($cats, $link = true, $separator=', ') {
		if (!$cats) {
			return;
		} else {
			$filed=array();
			if ($tmp1 = entry_categories_get('defs')) {
				
				foreach ($tmp1 as $k=>$c) {
					if(array_intersect(array($k),$cats)) {  
						$filed[] = $link? "<a href=\"" . get_category_link($k) ."\">$c</a>" : $c;
					}
				}
			}
			if ($filed) {
				return implode($separator, $filed);
			}
		}
	}
	
	/*
		this is called only in legacy mode
		
	*/
	
	
	// {{{ ENTRY
	function &theme_entry_filters(&$contentarr, $id=null) {
		
		$contentarr['subject']=apply_filters('the_title', $contentarr['subject']);
	
		$contentarr['content'] = apply_filters('the_content', $contentarr['content']);
		
		if (isset($contentarr['comments'])) {
			$contentarr['commentcount'] = $contentarr['comments'];
			$contentarr['comments'] = apply_filters('comments_number', $contentarr['commentcount']);
		}
		
		$contentarr['permalink']=get_permalink($id);
		
		$contentarr['commentlink']=get_comments_link($id);
		return $contentarr;
	}
	
	//{{{ COMMENTS	
	function &theme_comments_filters(&$contentarr, $key) {
		
		$contentarr['name']=apply_filters('comment_author_name', $contentarr['name']);
		if (isset($contentarr['email'])) {
			$contentarr['email']=apply_filters('comment_author_email', $contentarr['email']);
			$contentarr['mailto'] = 'mailto:' . $contentarr['email'];
		}
		if (!isset($contentarr['url'])) $contentarr['url'] = '#'; 
		$contentarr['timestamp']=$contentarr['date'];
		$contentarr['content']=apply_filters('comment_text', $contentarr['content']);
		
		return $contentarr;

	}
		
	
?>
