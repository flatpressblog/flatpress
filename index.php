<?php
	
	include 'defaults.php';
	include INCLUDES_DIR . 'includes.php';
	include SMARTY_DIR . 'SmartyValidate.class.php';
	
	define('MOD_INDEX', 1);

	/* local function defines follow */
	
	function index_permatitle($val, $sep) {
		global $fpdb;
		$q =& $fpdb->getQuery();
		list($id, $e) = $q->peekEntry();
		return "$val {$sep} {$e['subject']}";
	}

	function index_gentitle($val, $sep) {
		global $title;
		return "$val $sep $title";
	}

	function index_404error() {
		global $smarty, $lang;
		@utils_status_header(404);
		$module='default.tpl';
	
		$smarty->assign('subject', $lang['404error']['subject']);
		$smarty->assign('content', $lang['404error']['content']);


		$smarty->assign('rawcontent', true);

		return $module;
	}
	
	
	function index_singlepost(&$params, &$module) {
		
		global $fpdb;
		
		$params['id'] = $_GET['entry'];
		$params['fullparse']=true;
		$fpdb->query($params);
		
		add_filter('wp_title', 'index_permatitle', 10, 2);
		
		if (isset($_GET['comments'])) {
		
			$module = 'comments.tpl';
			$params['comments'] = true;
			
			include('comments.php');
		
		}
		
	}
	
	function index_staticpage($page, $explicit_req,  &$params, &$module) {
	
		global $smarty, $title;

		if (static_exists($page)) {
			$arr = static_parse($page);
			$title = $arr['subject'];

			if ($explicit_req)
				add_filter('wp_title', 'index_gentitle', 1, 2);
			
			$smarty->assign('static_id', $page);
			$smarty->assign('static_page',$arr);
			
			return $module='static.tpl';
		} 
	
		if (user_loggedin())
			utils_redirect('admin.php?p=static&action=write&page='.$page);
		else
			$module = index_404error();

		return $module;
			
	}
	
	function index_showposts(&$params, &$module) {
	
		if (isset($_GET['d']) && $_GET['d'])
			$params['d'] = $_GET['d'];
	
		if (isset($_GET['m']) && $_GET['m']) 
			$params['m'] = $_GET['m'];
	
		if (isset($_GET['y']) && $_GET['y'])
			$params['y'] = $_GET['y'];
	
		if (isset($_GET['start']) && $_GET['start'])
			$params['start'] = intval($_GET['start']);
	
		if (isset($_GET['count']) && $_GET['count'])
			$params['count'] = intval($_GET['count']);
		if (isset($_GET['category']) && is_numeric($_GET['category']))
			$params['category'] = intval($_GET['category']);
			
		if (isset($_GET['cat']) && is_numeric($_GET['cat']))
			$params['category'] = intval($_GET['cat']);
		
		if (isset($_GET['random'])) {
			if (empty($_GET['random']))
				$params['random'] = 1;
			elseif (is_numeric($_GET['random'])) 
				$params['random'] = intval($_GET['random']);
		}
		
		if ((isset($_GET['paged'])) && is_numeric($_GET['paged']) && $_GET['paged']>0)
			$params['page']=$_GET['paged'];
		else 
			$params['page'] = 1;

	}
	
	function index_main() {
	
		global $fpdb, $smarty, $fp_config;

		$params = array();
		$module = 'index.tpl' ;
		
		
		if (!empty($_GET['entry'])) {
		
			index_singlepost($params, $module);
			
		} elseif (
			
			($explicit_req = $page = @$_GET['page']) ||
			(empty($_GET) && $page = @$fp_config['general']['startpage'])
			
			) {
			
				index_staticpage($page, $explicit_req, $params, $module);
		
		} elseif (!empty($_GET['q'])) {
			include('search.php');
			$module = search_main();
		} else {
		
			if (!empty($_GET['feed'])){
				
					switch($_GET['feed']) {
						case 'atom':
							header('Content-type: application/atom+xml');
							$module = SHARED_TPLS . 'atom.tpl';
							break;
						case 'rss2':
						default:
							header('Content-type: application/rss+xml');
							$module = SHARED_TPLS . 'rss.tpl';
					}
					
					
			}
		
			index_showposts($params, $module);
		
		}
		
		$params['fullparse'] = true;
		
		$fpdb->query($params);

		$q =& $fpdb->getQuery();
		list($id, $e) = $q->peekEntry();

		/* no entry found : 404 */

		if (!$e)
			$module = index_404error();
		
		return $module;
						
	}

	function index_display() {
		global $smarty;
		
		
		$module = index_main();
		
		theme_init($smarty);
		
		$smarty->display($module);
			
		unset($smarty);
			
		do_action('shutdown');
			
	}

	
	system_init();
	index_display();
	

	
?>
