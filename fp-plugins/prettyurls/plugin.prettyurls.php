<?php
/*
Plugin Name: PrettyURLs
Plugin URI: http://flatpress.nowherland.it/
Description: Url prettifier (powered by htaccess)
Author: NoWhereMan  (E.Vacchi)
Version: 3.0
Author URI: http://www.nowhereland.it
*/

/**
 * Place where the index is stored
 */
define('PRETTYURLS_TITLES', true);
define('PRETTYURLS_PATHINFO', !file_exists(ABS_PATH . '.htaccess'));
define('PRETTYURLS_CACHE', CACHE_DIR . '%%prettyurls-index.tmp');
define('PRETTYURLS_CATS', CACHE_DIR . '%%prettyurls-cats.tmp');

/**
 * File existance check
 */

# memo
# register_plugin_setup('plugin_id', 'setup_func');

function plugin_prettyurls_setup() {
	
	if (file_exists(ABS_PATH . '.htaccess'))
		return 1;
	
	if (!is_writable(ABS_PATH)) {
	 	return -2;
	}
	
	return 1;
}

class Plugin_PrettyURLs {

	var $index = array();
	var $status = 0;
	var $date_handled = false;
	var $categories = null;
	var $fp_params;
	
	
	function categories($force=true) {
		
		if ($this->categories) 
			return; 
		
		if ($force || !file_exists(PRETTYURLS_CATS)) {
			$d = entry_categories_get('defs');
			$list = array();
			foreach ($d as $k=>$v) {
				$list[$k] = sanitize_title($v);
			}
			io_write_file(PRETTYURLS_CATS, serialize($list));
		} else {
			$f = io_load_file(PRETTYURLS_CATS);
			$list = unserialize($f);
		}
		$this->categories = $list;
	}
	
	
	function md5($id, $title) {
		$date = date_from_id($id);
		return md5($date['y'].$date['m'].$date['d'].$title);
	}

	
	
	function permalink($str, $id) {
		global $fpdb, $post;

		if (PRETTYURLS_TITLES)
			$title = sanitize_title($post['subject']);
		else 
			$title = $id;
		$date = date_from_id($id);
					// yeah, hackish, I know...
	
		return $this->baseurl  . "20{$date['y']}/{$date['m']}/{$date['d']}/$title/";
	}
	
	function commentlink($str, $id) {
		$link = $this->permalink($str, $id);
		return $link . "comments/";
	}
	
	function feedlink($str, $type) {
		return $this->baseurl  . "feed/{$type}/";
		
	}
	
	function commentsfeedlink($str, $type, $id) {
		$link = $this->commentlink($str, $id);
		return $link . "feed/{$type}/"; 
	}


	function staticlink($str, $id) {
		return $this->baseurl  .  "$id/";
	}
	

	function categorylink($str, $catid) {
		if (PRETTYURLS_TITLES) {
			if (@$this->categories[$catid])
				return $this->baseurl  . "category/{$this->categories[$catid]}/";
			else return $str;
		} else {
			return $this->baseurl  . "category/{$catid}/";
		}
	}
	
	
	function yearlink($str, $y) {
		return $this->baseurl  . "20$y/";
	}
	
	function monthlink($str, $y, $m) {
		return $this->yearlink($str, $y) . "$m/";
	}
	
	function daylink($str, $y, $m, $d) {
		return $this->monthlink($str, $y, $m) . "$d/";
	}
	
	
	function cache_create() {
		
		$this->index = array();

		/*
		$o =& entry_init();
		
		$entries = $o->getList();
		 */

		$o = new FPDB_Query(array('start'=>0,'count'=>-1,'fullparse'=>false), null);
		
		
		#foreach ($entries as $id => $contents) {
		while ($o->hasMore()) {
			list($id, $contents) = $o->getEntry();
			$date = date_from_id($id);
			echo $contents['subject'], "\n";
			$md5 = md5(sanitize_title($contents['subject']));
			$this->index[$date['y']][$date['m']][$date['d']][$md5] = $id;		
		}
		#}
		
		$this->cache_save();
		io_write_file(PRETTYURLS_CACHE, 'dummy');
		
	}
	

	
	
	function handle_categories($matches) {
	
	
		if (!$this->categories) 
			return;
			
		if (PRETTYURLS_TITLES) {	
			if ($c = array_search($matches[1], $this->categories))
				$this->fp_params['cat'] = $c;
			else return $matches[0];
		} else {
			$this->fp_params['cat'] = $matches[1];
		}
		
	}
	
	/*
		named matches are not supported here
	*/
	
	function handle_date($matches) {
	
		$this->fp_params['y'] = $matches[1];
		if (isset($matches[3])) $this->fp_params['m'] = $matches[3] ;
		if (isset($matches[5])) $this->fp_params['d'] = $matches[5];
		
		$this->date_handled = true;
		
	}
	
	
	function handle_static($matches) {
		$this->fp_params['page'] = $matches[1];
		$this->status = 2;	
	}
	
	function handle_entry($matches) {
		
		if (PRETTYURLS_TITLES) {	
		
			#isset($this->index[
			if ($this->cache_get($this->fp_params['y'],$this->fp_params['m'], $this->fp_params['d'], md5($matches[1]))) {
				$this->fp_params['entry'] = $this->index[$this->fp_params['y']][$this->fp_params['m']][$this->fp_params['d']][md5($matches[1])];
			} else {
				// a bit hackish: we make up a fake url when there is no match, 
				// so that at the higher level the system will 404... 
				$this->fp_params['entry'] = 'a';
			}
		} else {
			$this->fp_params['entry'] = $matches[1];
		}

			
	}
	
	function handle_page($matches) {
		$this->fp_params['paged'] = $matches[1];
		$this->status = 2;
	}
	
	
	function handle_comment($matches) {
		$this->fp_params['comments'] = true;
	}
	
	function handle_feed($matches) {
	
		$this->fp_params['feed'] = isset($matches[2])? $matches[2]:'rss2';
	}


	function get_url() {
		$baseurl = BLOG_BASEURL;
		$opt = plugin_getoptions('prettyurls', 'mode');
		$url = substr($_SERVER['REQUEST_URI'], strlen(BLOG_ROOT)-1);

		switch($opt) {
			case null:
			case 0:
				$opt = file_exists(ABS_PATH . '.htaccess') ? 3 : 1;	
			case 1:
				$baseurl .= 'index.php/'; 
				$url = $_SERVER['PATH_INFO'];
				break;
			case 2:
				$baseurl .= '?u=/'; 
				$url = @$_GET['u'];
			/* case 3: do nothing, it's BLOG_BASEURL */
		}

		$this->baseurl = $baseurl;
		$this->mode = $opt;

		return $url;	

	}


	
	/*
	 * here is where the real work is done.
	 *
	 * First we load the cache if exists;
	 *
	 * We check then if the GET request contains a 'title'
	 * if so, we'll need date and time to construct the md5 sum
	 * with which we index the cache array
	 *
	 * If that entry exists, we set $_GET['entry'] to that ID,
	 * so that FlatPress can find it where it is expected
	 *
	 */

	
	function cache_init() {
	
		global $fp_params;
		
		$this->fp_params =& $fp_params;
		$url = $this->get_url();
	
		if (PRETTYURLS_TITLES) {
			#if ($f = io_load_file(PRETTYURLS_CACHE))
			$this->index = array(); #unserialize($f);
			
			if (!file_exists(PRETTYURLS_CACHE))
				$this->cache_create();


			$this->categories(false);
		}

		if (!defined('MOD_INDEX'))
			return;
		
		

//		# this is not working if you reach flatpress via symlink
//		# unless you don't edit manually defaults.php
//		if (strpos($_SERVER['REQUEST_URI'], BLOG_ROOT)!==false) {
//			$url = $_SERVER['REQUEST_URI'];
//			$del = BLOG_ROOT;
//			if (strpos($url, 'index.php')!==false)
//				$del = $del . 'index.php/';
//			$url = substr($url, strlen($del)-1);
//		}

		// removes querystrings
		if (false !== $i = strpos($url, '?'))
			$url = substr($url, 0, $i);

		// removes anchors
		if (false !== $i = strpos($url, '#'))
			$url = substr($url, 0, $i);

	
		if (strrpos($url, '/') != (strlen($url)-1)) {
			$url .= '/';
		}


		if ($url=='/')
			return;

	
		
		//date
		$url = preg_replace_callback(
			'!^/[0-9]{2}(?P<y>[0-9]{2})(/(?P<m>[0-9]{2})(/(?P<d>[0-9]{2}))?)?!', 
			array(&$this, 'handle_date'), 
			$url
			);
			
		
		if (!$this->date_handled){
			// static page
			$url = preg_replace_callback('|^/([a-zA-Z0-9_-]+)/$|', array(&$this, 'handle_static'), $url);
			if ($this->status == 2)
				return $this->check_url($url);
		}
		
		
		$url = preg_replace_callback('{category/([^/]+)/}', array(&$this, 'handle_categories'), $url);
		
		
		$url = preg_replace_callback('|page/([0-9]+)/$|', array(&$this, 'handle_page'), $url);		
		if ($this->status == 2)
			return $this->check_url($url);	
		
		if ($this->date_handled){
			$url = preg_replace_callback('|^/([^/]+)|', array(&$this, 'handle_entry'), $url);
			// if status = 2
				/*
					utils_error(404);
				*/
			
			$url = preg_replace_callback('|^/comments|', array(&$this, 'handle_comment'), $url);
		}
		
		
		$url = preg_replace_callback('|^/feed(/([^/]*))?|', array(&$this, 'handle_feed'), $url);
		
		$this->check_url($url);
		
	}

	function check_url($url) {
		if (!empty($url) && $url != '/') {
			$this->fp_params = array('entry'=>'entry000000-000000');
			$url = apply_filters('prettyurls_unhandled_url', $url);
		}
	}

	function cache_delete_elem($id, $date) {
	
		# is this a title change?
		if (false !== ($ids = $this->cache_get( $date['y'] ,  $date['m'] ,  $date['d'] )))
			$hash = array_search($id, $ids);
		else
			return;
		
		if ($hash) {
			unset($this->index[ $date['y'] ] [ $date['m'] ][ $date['d'] ][ $hash ]);
			
			if (empty($this->index[ $date['y'] ] [ $date['m'] ][ $date['d'] ])) {
				unset($this->index[ $date['y'] ] [ $date['m'] ][ $date['d'] ]);
			
				if (empty($this->index[ $date['y'] ] [ $date['m'] ])) {
					unset($this->index[ $date['y'] ] [ $date['m'] ]);
			
					if (empty($this->index[ $date['y'] ])) {
						unset($this->index[ $date['y'] ]);
					}
					
				}
				
			}
			
		}

		$this->cache_save();
	
	}
	
	function cache_add($id, $arr) {
		
		$date = date_from_id($id);
		$title = sanitize_title($arr['subject']);
		
		$this->cache_delete_elem($id, $date);
		
		$this->index[ $date['y'] ] [ $date['m'] ][ $date['d'] ][ md5($title) ] = $id;
		
		$this->cache_save();
	
		
		return true;
		
	}

	function cache_get($y,$m,$d=null,$h=null) {
		if (!isset($this->index[$y][$m])) {
			$s = @io_load_file(PRETTYURLS_CACHE.$y.$m);
			$this->index[$y][$m] = $s? unserialize($s) : false;
		}

		if (is_null($d))
			return $this->index[$y][$m];

		if (is_null($h))
			return isset($this->index[$y][$m][$d])? $this->index[$y][$m][$d] : false;

		if (isset($this->index[$y][$m][$d]))
			return isset($this->index[$y][$m][$d][$h]);
		else
			return false;
	}
	
	function cache_delete($id) {
		$date = date_from_id($id);
		$this->cache_delete_elem($id, $date);
		$this->cache_save();
	
	}

	function cache_save() {
		if ($this->index) {
			foreach ($this->index as $year => $months) {
				foreach ($months as $month => $days)
				io_write_file(PRETTYURLS_CACHE.$year.$month, serialize($days));
			}

		}
		
		return true;
		
	}

	function nextprevlink($nextprev, $v) {
	
		global $fpdb;	
		$q =& $fpdb->getQuery();
		
		list($caption, $id) = call_user_func(array(&$q, 'get'.$nextprev));
	
		if (!$id)
			return array();
		
		
		if ($q->single) {
			$date = date_from_id($id);
			if (PRETTYURLS_TITLES)
				$title = sanitize_title($caption);
			else $title = $id;
			$url = $this->baseurl  . "20{$date['y']}/{$date['m']}/{$date['d']}/$title/";
			
			if ($v>0) 
				$caption = $caption . ' &raquo; '; 
			else
				$caption = ' &laquo; ' . $caption;
			
			return array($caption,$url);
			
		}
		
		
		// else, we build a complete url
		
		/* todo: clean up this mess... which means cleaning up the mess above. oh, my! */
		
		$l = $this->baseurl ;
		
		
		if ( ( 	is_numeric($cid = @$this->fp_params['category']) ) ||
				is_numeric($cid = @$this->fp_params['cat']) ) 
			$l = $this->categorylink($l, $cid);
		
		if (isset($this->fp_params['y']) && $this->fp_params['y']) {
			$l .= '20'. $this->fp_params['y'] . '/';
			
			if (isset($this->fp_params['m']) && $this->fp_params['m']) {
				$l .= $this->fp_params['m']  . '/';
			
				if (isset($this->fp_params['d']) && $this->fp_params['d'])
					$l .= $this->fp_params['d']  . '/';
		
			}
			
		}
		
		$page = 1;
		
		if (isset($this->fp_params['paged']) && $this->fp_params['paged']>1) $page = $this->fp_params['paged'];
	
		$page += $v;
		
		if ($page > 0) {
			$l .= 'page/' . $page . '/';
		}
		
		
		return array($caption,$l);
		
		
	}
	
	
	
}	

	global $plugin_prettyurls;
	$plugin_prettyurls = new Plugin_PrettyURLs;
	$plugin_prettyurls->categories();
	
	
	if (!defined('MOD_ADMIN_PANEL')){
	
		if (!function_exists('get_nextpage_link')) :
		
		function get_nextpage_link() {
			global $plugin_prettyurls;
			return $plugin_prettyurls->nextprevlink('NextPage', 1);
		}
		function get_prevpage_link() {
			global $plugin_prettyurls;
			return $plugin_prettyurls->nextprevlink('PrevPage',-1);
		}
		
		endif;
		
	}
	
	

	add_filter('post_link', 	array(&$plugin_prettyurls,'permalink'), 0, 2);
	add_filter('comments_link', array(&$plugin_prettyurls, 'commentlink'), 0, 2);
	add_filter('feed_link', 	array(&$plugin_prettyurls, 'feedlink'), 0, 2);
	add_filter('post_comments_feed_link', array(&$plugin_prettyurls, 'commentsfeedlink'), 0, 3);
	add_filter('category_link', array(&$plugin_prettyurls,'categorylink'), 0, 2);
	add_filter('page_link', 	array(&$plugin_prettyurls, 'staticlink'), 0, 2);
	
	// date related functions
	add_filter('year_link', 	array(&$plugin_prettyurls,'yearlink'), 0, 2);
	add_filter('month_link', 	array(&$plugin_prettyurls,'monthlink'), 0, 3);
	add_filter('day_link', 		array(&$plugin_prettyurls,'daylink'), 0, 4);

	if (PRETTYURLS_TITLES) {
		add_filter('publish_post', 	array(&$plugin_prettyurls, 'cache_add'), 5, 2);
		add_filter('delete_post', 	array(&$plugin_prettyurls, 'cache_delete'));
		add_action('update_categories',  array(&$plugin_prettyurls, 'categories'));
	}

	add_filter('init', 			array(&$plugin_prettyurls, 'cache_init'));

if (class_exists('AdminPanelAction')){

	class admin_plugin_prettyurls extends AdminPanelAction { 
		
		var $langres = 'plugin:prettyurls';
		var $_config = array('mode'=>0);
		
		function setup() {
			$this->smarty->assign('admin_resource', "plugin:prettyurls/admin.plugin.prettyurls");
			$this->_config['mode'] = plugin_getoptions('prettyurls', 'mode');
			$this->smarty->assign('pconfig', $this->_config);
			$blogroot = BLOG_ROOT;
			$f = ABS_PATH . '.htaccess';
			$txt = io_load_file($f);
			if (!$txt) {
		
$txt =<<<STR

# Thanks again WP :)

<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase {$blogroot}
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . {$blogroot}index.php [L]
</IfModule>
STR;
			}
			
			
			$this->smarty->assign('cantsave', 
				( !is_writable(ABS_PATH) || (file_exists($f) && !is_writable($f)) )
			);	
			$this->smarty->assign('htaccess', $txt);		
		}
		
		
		
		function onsubmit() {
			global $fp_config;

			if (isset($_POST['saveopt'])) {
				$this->_config['mode'] = (int) $_POST['mode'] ;
				plugin_addoption('prettyurls', 'mode', $this->_config['mode']);
				if( plugin_saveoptions() )
					$this->smarty->assign('success', 2);
				else	$this->smarty->assign('success', -2); 
			}
			
			if (isset($_POST['htaccess-submit'])) {
					if (!empty($_POST['htaccess']) && io_write_file(ABS_PATH.'.htaccess', $_POST['htaccess'])){
						$this->smarty->assign('success', 1);
					} else {
						$this->smarty->assign('success', -1);
					}
			}
			
			return 2;
		}
		
	}

	admin_addpanelaction('plugin', 'prettyurls', true);

}

?>
