<?php

	if (!defined('MOD_INDEX')) {
		include 'defaults.php';
		include INCLUDES_DIR . 'includes.php';
		
		/* backward compatibility */

		if (!@$_GET['entry']) {
			@utils_redirect();
		} else {
			@utils_status_header(301);
			@utils_redirect(str_replace('&amp;','&', get_comments_link($_GET['entry'])), true);
		}

		
	}

		$module = comment_main($module);

		function comment_main($module) {
					
			global $fpdb;
			
				
			// hackish solution to get title before fullparse starts dunno, I don't like it
								
			$q =& $fpdb->getQuery();
			
			list($id, $entry) = @$q->peekEntry();
			if (!$entry)
				return $module;
			
			
			if (!empty($_GET['feed'])){
			
					switch($_GET['feed']) {
					
						case 'atom':
							header('Content-type: application/atom+xml');
							$module = SHARED_TPLS . 'comment-atom.tpl';
							break;
						case 'rss2':
						default:
							header('Content-type: application/rss+xml');
							$module = SHARED_TPLS . 'comment-rss.tpl';
					}
					
					
			} elseif (!in_array('commslock', $entry['categories'])) {
				
				commentform();
			}
			
			return $module;
			
		}
		
		function comment_feed() {
			echo "\n<link rel=\"alternate\" type=\"application/rss+xml\" title=\"Get Comments RSS 2.0 Feed\" href=\"".
					theme_comments_feed_link('rss2', $_GET['entry']) 
				."\" />";
			echo "\n<link rel=\"alternate\" type=\"application/atom+xml\" title=\"Get Comments Atom 1.0 Feed\" href=\"".
					theme_comments_feed_link('atom', $_GET['entry']) 
				."\" />\n";
		}
		add_action('wp_head', 'comment_feed');

		function comment_validate() {

			
			$arr['version'] = system_ver();
			$arr['name'] = $_POST['name'];

			$loggedin = false;

			if (user_loggedin()) {
				$loggedin = $arr['loggedin']=true;
			}

			if (!$loggedin)
			setcookie('comment_author_' . COOKIEHASH, 
				$arr['name'], time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);

			
			if (!empty($_POST['email'])) {
				($arr['email'] = $_POST['email']);
				if (!$loggedin)
					setcookie('comment_author_email_' . COOKIEHASH, 
							$arr['email'], time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);

			}
			if (!empty($_POST['url'])) {
				($arr['url'] = ( $_POST['url'] )) ;
				if (!$loggedin)
					setcookie('comment_author_url_' . COOKIEHASH, 
							$arr['url'], time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);


			}
			$arr['content'] = $_POST['content'];

			if ($v = utils_ipget()) {	
				$arr['ip-address'] = $v;
			}
			
			if ($loggedin || apply_filters('comment_validate', true, $arr))
				return $arr;
			else return false;
					
			
		}
		
		function commentform() {
		
			global $smarty, $lang, $fpdb;
			
			$comment_formid = 'fp-comments';
			$smarty->assign('comment_formid', $comment_formid);


			if(empty($_POST)) {
			
				if(!SmartyValidate::is_registered_form($comment_formid)) {
				
					// new form, we (re)set the session data
					
					SmartyValidate::connect($smarty, true);
					SmartyValidate::register_form($comment_formid, true);
					
					
					// register our validators
					
					SmartyValidate::register_validator('name', 'name', 'notEmpty', false, false, 'trim,stripslashes', $comment_formid); 
					SmartyValidate::register_validator('email','email', 'isEmail', true, false, 'trim,stripslashes', $comment_formid);
					SmartyValidate::register_validator('www', 'url', 'isURL', true, false, 'trim,stripslashes', $comment_formid);
					SmartyValidate::register_validator('comment', 'content', 'notEmpty', false, false, 'stripslashes', $comment_formid);
				}
				
			} else {    
				utils_nocache_headers();
				// validate after a POST
				SmartyValidate::connect($smarty, true);
				
				// add http to url
				if (!empty($_POST['url']) && strpos($_POST['url'], 'http://')===false) 
					$_POST['url'] = 'http://'.$_POST['url'];
				
				
				// custom hook here!!
				if( SmartyValidate::is_valid($_POST, $comment_formid) && ($arr=comment_validate())) {
					//SmartyValidate::disconnect();
					
					global $fp_config;

					
					$id = comment_save($_GET['entry'], $arr);
					
					do_action('comment_post', $_GET['entry'], array($id, $arr));
					
					$q =& new FPDB_Query(array('id'=>$_GET['entry'],'fullparse'=>false), null);
					list($entryid, $e) = $q->getEntry();
						
				
					if ($fp_config['general']['notify'] && !user_loggedin()) {
					
						global $post;
					
						$comm_mail = isset($arr['email'])? "<{$arr['email']}>" : '';
						$from_mail = $comm_mail? $arr['email'] : $fp_config['general']['email'];
					
						$post = $e; // plugin such as prettyurls might need this...

						$lang = lang_load('comments');

						$mail = str_replace(
							array(
								'%toname%',
								'%fromname%',
								'%frommail%',
								'%entrytitle%',
								'%commentlink%',
								'%content%',
								'%blogtitle%'
							),

							array(
								$fp_config['general']['author'],
								$arr['name'],
								$comm_mail,
								$e['subject'],
								get_comments_link($entryid) . '#'.$id,
								$arr['content'],
								$fp_config['general']['title']
							),

							$lang['comments']['mail']
						);


						@utils_mail($from_mail, "New comment on {$fp_config['general']['title']}", 
							$mail);
							
					}
					
					// if comment is valid, this redirect will clean the postdata
					$location = str_replace(
									'&amp;', '&', 
									get_comments_link($entryid)
								) . '#'.$id; 
					
					utils_redirect($location,true);
					exit();
					
				} else {
					$smarty->assign('values', $_POST);
				}

			}


			// Cookies
			$smarty->assign('cookie', array(
				'name'	=> @$_COOKIE['comment_author_' . COOKIEHASH],
				'email'	=> @$_COOKIE['comment_author_email_' . COOKIEHASH],
				'url'	=> @$_COOKIE['comment_author_url_' . COOKIEHASH]
			));




		}

		
		
		
	
	
?>
