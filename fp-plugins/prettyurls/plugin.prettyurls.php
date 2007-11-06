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
		
		$title = sanitize_title($post['subject']);
		$date = date_from_id($id);
					// yeah, hackish, I know...
	
		return BLOG_BASEURL . "20{$date['y']}/{$date['m']}/{$date['d']}/$title/";
	}
	
	function commentlink($str, $id) {
		$link = $this->permalink($str, $id);
		return $link . "comments/";
	}
	
	function feedlink($str, $type) {
		return BLOG_BASEURL . "feed/{$type}/";
		
	}
	
	function commentsfeedlink($str, $type, $id) {
		$link = $this->commentlink($str, $id);
		return $link . "feed/{$type}/"; 
	}


	function staticlink($str, $id) {
		return BLOG_BASEURL .  "$id/";
	}
	

	function categorylink($str, $catid) {
		return BLOG_BASEURL . "category/{$this->categories[$catid]}/";
	}
	
	
	function yearlink($str, $y) {
		return BLOG_BASEURL . "20$y/";
	}
	
	function monthlink($str, $y, $m) {
		return $this->yearlink($str, $y) . "$m/";
	}
	
	function daylink($str, $y, $m, $d) {
		return $this->daylink($str, $y, $m) . "$d/";
	}
	
	
	function cache_create() {
		
		$this->index = array();
		
		$o =& entry_init();
		
		$entries = $o->getList();
		
		foreach ($entries as $id => $contents) {
			$date = date_from_id($id);
			$md5 = md5(sanitize_title($contents['subject']));
			$this->index[$date['y']][$date['m']][$date['d']][$md5] = $id;		
		}
		
		$this->cache_save();
		
	}
	

	
	
	function handle_categories($matches) {
	
	
		if (!$this->categories) 
			return;
			
	
		if ($c = array_search($matches[1], $this->categories))
			$_GET['category'] = $c;
		
	}
	
	/*
		named matches are not supported here
	*/
	
	function handle_date($matches) {
	
		$_GET['y'] = $matches[1];
		if (isset($matches[3])) $_GET['m'] = $matches[3] ;
		if (isset($matches[5])) $_GET['d'] = $matches[5];
		
		$this->date_handled = true;
		
	}
	
	
	function handle_static($matches) {
		$_GET['page'] = $matches[1];
		$this->status = 2;	
	}
	
	function handle_entry($matches) {
		
		
		if (isset($this->index[$_GET['y']][$_GET['m']][$_GET['d']][md5($matches[1])]))
				$_GET['entry'] = $this->index[$_GET['y']][$_GET['m']][$_GET['d']][md5($matches[1])];
			
	}
	
	function handle_page($matches) {
		$_GET['paged'] = $matches[1];
		$this->status = 2;
	}
	
	
	function handle_comment($matches) {
		$_GET['comments'] = true;
	}
	
	function handle_feed($matches) {
	
		$_GET['feed'] = isset($matches[2])? $matches[2]:'rss2';
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
	
	
		if ($f = io_load_file(PRETTYURLS_CACHE))
			$this->index = unserialize($f);
			
		if (!$this->index)
			$this->cache_create();
			
		$this->categories(false);
		
		if (!defined('MOD_INDEX'))
			return;
		

		
		if (strpos($_SERVER['REQUEST_URI'], BLOG_ROOT)!==false) {
			$url = $_SERVER['REQUEST_URI'];
			$del = BLOG_ROOT;
			if (strpos($url, 'index.php')!==false)
				$del = $del . 'index.php/';
			$url = substr($url, strlen($del)-1);
		}
		
		if ($url=='/')
			return;

		
		$matches = array();
		
		// removes querystrings
		if ($i = strpos($url, '?'))
			$url = substr($url, 0, $i);
		
		if (strrpos($url, '/') != (strlen($url)-1)) {
			$url .= '/';
		}
		
		
		
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
				return;
		}
		
		
		$url = preg_replace_callback('{category/([^/]+)}', array(&$this, 'handle_categories'), $url);
		
		
		$url = preg_replace_callback('|page/([0-9]+)/$|', array(&$this, 'handle_page'), $url);		
		if ($this->status == 2)
			return;	
		
		if ($this->date_handled){
			$url = preg_replace_callback('|^/([^/]+)|', array(&$this, 'handle_entry'), $url);
			// if status = 2
				/*
					utils_error(404);
				*/
			
			$url = preg_replace_callback('|^/comments|', array(&$this, 'handle_comment'), $url);
		}
		
		
		$url = preg_replace_callback('|^/feed(/([^/]*))?|', array(&$this, 'handle_feed'), $url);
		
			
		
	}


	function cache_delete_elem($id, $date) {
	
		# is this a title change?
		if (isset($this->index[ $date['y'] ] [ $date['m'] ][ $date['d'] ]))
			$hash = array_search($id, $this->index[ $date['y'] ] [ $date['m'] ][ $date['d'] ]);
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
	
	}
	
	function cache_add($id, &$arr) {
		
		$date = date_from_id($id);
		$title = sanitize_title($arr['subject']);
		
		$this->cache_delete_elem($id, $date);
		
		$this->index[ $date['y'] ] [ $date['m'] ][ $date['d'] ][ md5($title) ] = $id;
		
		$this->cache_save();
	
		
		return true;
		
	}
	
	function cache_delete($id) {
		$date = date_from_id($id);
		$this->cache_delete_elem($id, $date);
		$this->cache_save();
	
	}

	function cache_save() {
		if ($this->index)
			return io_write_file(PRETTYURLS_CACHE, serialize($this->index));
		
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
			$title = sanitize_title($caption);
			$url = BLOG_BASEURL . "20{$date['y']}/{$date['m']}/{$date['d']}/$title/";
			
			if ($v>0) 
				$caption = $caption . ' &raquo '; 
			else
				$caption = ' &laquo ' . $caption;
			
			return array($caption,$url);
			
		}
		
		
		// else, we build a complete url
		
		$l = BLOG_BASEURL;
		
		
		if (isset($_GET['category']) && is_numeric($_GET['category'])) 
			$c = $this->categories[$_GET['category']];
		elseif (isset($_GET['cat']) && is_numeric($_GET['cat'])) 
			$c = 'category-' . $_GET['cat'] .'/';
		
		$l .= $c;
		
		if (isset($_GET['y']) && $_GET['y']) {
			$l .= '20'. $_GET['y'] . '/';
			
			if (isset($_GET['m']) && $_GET['m']) {
				$l .= $_GET['m']  . '/';
			
				if (isset($_GET['d']) && $_GET['d'])
					$l .= $_GET['d']  . '/';
		
			}
			
		}
		
		$page = 1;
		
		if (isset($_GET['paged']) && $_GET['paged']>1) $page = $_GET['paged'];
	
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
	add_action('update_categories',  array(&$plugin_prettyurls, 'categories'));
	add_filter('year_link', 	array(&$plugin_prettyurls,'yearlink'), 0, 2);
	add_filter('month_link', 	array(&$plugin_prettyurls,'monthlink'), 0, 3);
	add_filter('day_link', 	array(&$plugin_prettyurls,'daylink'), 0, 4);
	
	
	
	add_filter('publish_post', 	array(&$plugin_prettyurls, 'cache_add'), 5, 2);
	add_filter('page_link', 	array(&$plugin_prettyurls, 'staticlink'), 0, 2);
	add_filter('init', 			array(&$plugin_prettyurls, 'cache_init'));
	// add_filter('cache_save', 	array(&$plugin_prettyurls, 'cache_save'));
	add_filter('delete_post', 	array(&$plugin_prettyurls, 'cache_delete'));

if (class_exists('AdminPanelAction')){

	class admin_plugin_prettyurls extends AdminPanelAction { 
		
		var $langres = 'plugin:prettyurls';
		
		function setup() {
			$this->smarty->assign('admin_resource', "plugin:prettyurls/admin.plugin.prettyurls");
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
				( (file_exists($f) && !is_writable($f)) && !is_writable(ABS_PATH) )
			);	
			$this->smarty->assign('htaccess', $txt);		
		}
		
		
		
		function onsubmit() {
			global $fp_config;
			
			if (!empty($_POST['htaccess']) && io_write_file(ABS_PATH.'.htaccess', $_POST['htaccess'])){
				$this->smarty->assign('success', 1);
			} else {
			 	$this->smarty->assign('success', -1);
			}
			
			return 2;
		}
		
	}

	admin_addpanelaction('plugin', 'prettyurls', true);

}

?>
