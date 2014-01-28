<?php
	
	include 'defaults.php';
	include INCLUDES_DIR . 'includes.php';
	include SMARTY_DIR . 'SmartyValidate.class.php';
	
	define('MOD_INDEX', 1);


	if (!file_exists(CONFIG_FILE))
		utils_redirect('setup.php');



	/* local function defines follow */
	
	function index_permatitle($val, $sep) {
		global $fpdb;
		$q =& $fpdb->getQuery();
		list($id, $e) = @$q->peekEntry();
		if ($e)
			return "{$e['subject']} {$sep} $val ";
		else return $val;
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
		
		global $fpdb, $theme, $fp_params;
		
		$params['id'] = $fp_params['entry'];
		$params['fullparse']=true;
		$fpdb->query($params);

		if (@$theme['hassingle'])
			$module='single.tpl';	
		
		add_filter('wp_title', 'index_permatitle', 10, 2);
		
		if (isset($fp_params['comments'])) {
		
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
	
		global $fp_params;
	
		if (isset($fp_params['d']) && $fp_params['d'])
			$params['d'] = $fp_params['d'];
	
		if (isset($fp_params['m']) && $fp_params['m']) 
			$params['m'] = $fp_params['m'];
	
		if (isset($fp_params['y']) && $fp_params['y'])
			$params['y'] = $fp_params['y'];
	
		if (isset($fp_params['start']) && $fp_params['start'])
			$params['start'] = intval($fp_params['start']);
	
		if (isset($fp_params['count']) && $fp_params['count'])
			$params['count'] = intval($fp_params['count']);
		if (isset($fp_params['category']) && is_numeric($fp_params['category']))
			$params['category'] = intval($fp_params['category']);
			
		if (isset($fp_params['cat']) && is_numeric($fp_params['cat']))
			$params['category'] = intval($fp_params['cat']);
		
		if (isset($fp_params['not']) && is_numeric($fp_params['not']))
			$params['exclude'] = intval($fp_params['not']);
		
		
		if (isset($fp_params['random'])) {
			if (empty($fp_params['random']))
				$params['random'] = 1;
			elseif (is_numeric($fp_params['random'])) 
				$params['random'] = intval($fp_params['random']);
		}
		
		if ((isset($fp_params['paged'])) && is_numeric($fp_params['paged']) && $fp_params['paged']>0)
			$params['page']=$fp_params['paged'];
		else 
			$params['page'] = 1;

	}
	
	function index_main() {
	
		global $fpdb, $smarty, $fp_config, $fp_params;
		$params = array();
		$module = 'index.tpl' ;
		$can404 = true;
		
		
		if (!empty($fp_params['entry'])) {
		
			index_singlepost($params, $module);
			
		} elseif (
			
			($explicit_req = $page = @$fp_params['page']) ||
			(empty($fp_params) && $page = @$fp_config['general']['startpage'])
			
			) {
			
				index_staticpage($page, $explicit_req, $params, $module);
				return $module;
		
		} elseif (!empty($_GET['q'])) {
			include('search.php');
			$module = search_main();
		} else {
		
			if (!empty($fp_params['feed'])){

					$can404=false;
				
					switch($fp_params['feed']) {
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

		if (!$e && $can404)
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
