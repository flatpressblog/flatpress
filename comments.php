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
					
			global $fpdb, $fp_params;
			
				
			// hackish solution to get title before fullparse starts dunno, I don't like it
								
			$q =& $fpdb->getQuery();
			
			list($id, $entry) = @$q->peekEntry();
			if (!$entry)
				return $module;
			
			
			if (!empty($fp_params['feed'])){
			
					switch($fp_params['feed']) {
					
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
			global $fp_params;
			echo "\n<link rel=\"alternate\" type=\"application/rss+xml\" title=\"Get Comments RSS 2.0 Feed\" href=\"".
					theme_comments_feed_link('rss2', $fp_params['entry']) 
				."\" />";
			echo "\n<link rel=\"alternate\" type=\"application/atom+xml\" title=\"Get Comments Atom 1.0 Feed\" href=\"".
					theme_comments_feed_link('atom', $fp_params['entry']) 
				."\" />\n";
		}
		add_action('wp_head', 'comment_feed');

		function comment_pagetitle($val, $sep) {
			global $fpdb, $lang;
			$q =& $fpdb->getQuery();
			list($id, $e) = @$q->peekEntry();
			if ($e)
				return "{$e['subject']} : {$lang['main']['comments']} {$sep} $val ";
			else return $val;
		}
		remove_filter('wp_title', 'index_permatitle');
		add_filter('wp_title', 'comment_pagetitle', 10, 2);

		function comment_validate() {
		
			global $smarty, $lang;
			
			$lerr =& $lang['comments']['error'];
			
			$r = true;
			
	/*			$lang['comments']['error'] = array(
		'name'		=> 'You must enter a name',
		'email'		=> 'You must enter a valid email',
		'www'		=> 'You must enter a valid URL',
		'comment'	=> 'You must enter a comment',
	);*/
	

			$content= isset($_POST['content'])? trim(stripslashes($_POST['content'])) : null;
			
			$errors = array();
			
			$loggedin = false;

			if (user_loggedin()) {
				$user = user_get();
				$loggedin = $arr['loggedin']=true;
				$email 	= $user['email'];
				$url 	= $user['www'];
				$name 	= $user['userid'];
			} else {

				$name 	= trim(htmlspecialchars(@$_POST['name']));
				$email 	= isset($_POST['email'])? trim(htmlspecialchars($_POST['email'])) : null;
				$url 	= isset($_POST['url'])? trim(stripslashes(htmlspecialchars($_POST['url']))) : null;
				
				/*
				 * check name
				 *
				 */
				
				if (!$name) {
					$errors['name'] = $lerr['name'];
				}
					
				
				/*
				 * check email
				 *
				 */
					
				if ($email)	{
					$_is_valid = !(preg_match('!@.*@|\.\.|\,|\;!', $email) ||
					!preg_match('!^.+\@(\[?)[a-zA-Z0-9\.\-]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$!', $email));
					
					if (!$_is_valid) {
						$errors['email'] = $lerr['email'];
					}
					
				}
				
				/*
				 * check url
				 *
				 */
					
				if ($url)	{
					if (!preg_match('!^http(s)?://[\w-]+\.[\w-]+(\S+)?$!i', $url)) {
	    			// || preg_match('!^http(s)?://localhost!', $value);
						$errors['url'] = $lerr['www'];
					}
				}
				
		
			}
			
			if (!$content) {
				$errors['content'] = $lerr['comment'];
			}
			

			if ($errors) {
				$smarty->assign('error', $errors);
				return false;
			}
			
			$arr['version'] = system_ver();
			$arr['name'] = $name;


			if (!$loggedin)
			setcookie('comment_author_' . COOKIEHASH, 
				$arr['name'], time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);

			
			
			if ($email) {
				($arr['email'] = $email);
				if (!$loggedin)
					setcookie('comment_author_email_' . COOKIEHASH, 
							$arr['email'], time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);

			}
			if ($url) {
				($arr['url'] = ( $url )) ;
				if (!$loggedin)
					setcookie('comment_author_url_' . COOKIEHASH, 
							$arr['url'], time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);


			}
			$arr['content'] = $content;

			if ($v = utils_ipget()) {	
				$arr['ip-address'] = $v;
			}
			
			if ($loggedin || apply_filters('comment_validate', true, $arr))
				return $arr;
			else return false;
					
			
		}
		
		function commentform() {
		
			global $smarty, $lang, $fpdb, $fp_params;
			
			$comment_formid = 'fp-comments';
			$smarty->assign('comment_formid', $comment_formid);


			if(!empty($_POST)) {    
			
				# utils_nocache_headers();
				
				// add http to url
				if (!empty($_POST['url']) && strpos($_POST['url'], 'http://')===false) 
					$_POST['url'] = 'http://'.$_POST['url'];
				
				
				// custom hook here!!
				if($arr=comment_validate()) {
					
					global $fp_config;
	
					$id = comment_save($fp_params['entry'], $arr);
					
					do_action('comment_post', $fp_params['entry'], array($id, $arr));
					
					$q = new FPDB_Query(array('id'=>$fp_params['entry'],'fullparse'=>false), null);
					list($entryid, $e) = $q->getEntry();
						
				
					if ($fp_config['general']['notify'] && !user_loggedin()) {
					
						global $post;
					
						$comm_mail = isset($arr['email'])? "<{$arr['email']}>" : '';
						$from_mail = $fp_config['general']['email'];
					
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
