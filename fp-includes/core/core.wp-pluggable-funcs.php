<?php

	/* These functions can be replaced via plugins.  They are loaded after
	 plugins are loaded. */


		function _get_nextprev_link($nextprev) {
	
		global $fpdb;	
		$q =& $fpdb->getQuery();
		
		list($caption, $id) = call_user_func(array(&$q, 'get'.$nextprev));
		
		if (!$id) 
			return null;
		
		if ($q->single) {
			$link = "?entry={$id}";
		} else {
			if ($_SERVER['QUERY_STRING']){

				if ( strpos($_SERVER['QUERY_STRING'], 'paged')!==false ){
					$link = '?'.preg_replace(
								'{paged=[0-9]+}', 
								"paged={$id}", 
								$_SERVER['QUERY_STRING']
							);
				} else {
					$link = '?' . $_SERVER['QUERY_STRING'] . "&paged={$id}";
				}
					$link = str_replace('&', '&amp;', $link);
							
			} else {
				$link = "?paged={$id}";
			}
		}
		
		return array($caption, BLOG_BASEURL . $link);
	
	}
	
	if (!function_exists('get_nextpage_link')) :
	function get_nextpage_link() {
		
		global $fpdb;	
		$q =& $fpdb->getQuery();
	
		$a = _get_nextprev_link('NextPage');
		
		
		if ($q->single) {
			$a[0] .= ' &raquo; ';
		}
		
		return $a;
		
	}
	endif;
	
	if (!function_exists('get_prevpage_link')) :
	function get_prevpage_link() {
		
		global $fpdb;	
		$q =& $fpdb->getQuery();
		
		$a = _get_nextprev_link('PrevPage');
		
		if ($q->single) {
			$a[0] = ' &laquo; ' . $a[0];
		}
		
		return $a;
	}
	endif;
	



	function wp_filter_kses($str) {
		return $str;
	}

	//----------------------------------------------------------------------------
	// WordPress pluggable functions
	//----------------------------------------------------------------------------
	
	
	/*
	get_currentuserinfo()
		Grabs the information of the current logged in user, if there is one. Essentially a
		wrapper for get_userdata(), but it also stores information in global variables.
	get_userdata($userid)
		Pulls user information for the specified user from the database.
	get_userdatabylogin($user_login)
		Pulls user information for the specified user from the database.
	wp_mail($to, $subject, $message, $headers = '')
		A convenient wrapper for PHP's mail function.
	wp_login($username, $password, $already_md5 = false)
		Returns true if the specified username and password correspond to a registered
		user.
	auth_redirect()
		If a user is not logged in, he or she will be redirected to WordPress' login page before
		being allowed to access content on the page from which this function was called.
		Upon sucessfully logging in, the user is sent back to the page in question.
	wp_redirect($location)
		Redirects a browser to the absolute URI specified by the $location parameter.
	wp_setcookie($username, $password, $already_md5 = false, $home =
		'', $siteurl = '')
		Sets the WordPress cookies for a logged in user. See WordPress Cookies.
	wp_clearcookie()
		Clears the cookies for a logged in user. See WordPress Cookies.
	wp_notify_postauthor($comment_id, $comment_type='')
		Emails the author of the comment's post the content of the comment specified.
	wp_notify_moderator($comment_id)
		Informs the administrative email account that the comment specified needs to be
		moderated. See General Options SubPanel.
	*/



if ( !function_exists('get_currentuserinfo') ) :
function get_currentuserinfo() {
/*	global $user_login, $userdata, $user_level, $user_ID, $user_nickname, $user_email, $user_url, $user_pass_md5, $user_identity;
	// *** retrieving user's data from cookies and db - no spoofing

	if (isset($_COOKIE['wordpressuser_' . COOKIEHASH])) 
		$user_login = $_COOKIE['wordpressuser_' . COOKIEHASH];
	$userdata = get_userdatabylogin($user_login);
	$user_level = $userdata->user_level;
	$user_ID = $userdata->ID;
	$user_nickname = $userdata->user_nickname;
	$user_email = $userdata->user_email;
	$user_url = $userdata->user_url;
	$user_pass_md5 = md5($userdata->user_pass);

	$idmode = $userdata->user_idmode;
	if ($idmode == 'nickname')  $user_identity = $userdata->user_nickname;
	if ($idmode == 'login')     $user_identity = $userdata->user_login;
	if ($idmode == 'firstname') $user_identity = $userdata->user_firstname;
	if ($idmode == 'lastname')  $user_identity = $userdata->user_lastname;
	if ($idmode == 'namefl')    $user_identity = $userdata->user_firstname.' '.$userdata->user_lastname;
	if ($idmode == 'namelf')    $user_identity = $userdata->user_lastname.' '.$userdata->user_firstname;
	if (!$idmode) $user_identity = $userdata->user_nickname;
*/
}
endif;



if ( !function_exists('get_userdata') ) :
function get_userdata($userid) {
/*	global $wpdb, $cache_userdata;
	$userid = (int) $userid;
	if ( empty($cache_userdata[$userid]) && $userid != 0) {
		$cache_userdata[$userid] = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID = $userid");
		$cache_userdata[$cache_userdata[$userid]->user_login] =& $cache_userdata[$userid];
	} 

	return $cache_userdata[$userid];
*/
}
endif;



if ( !function_exists('get_userdatabylogin') ) :
function get_userdatabylogin($user_login) {
/*	global $cache_userdata, $wpdb;
	if ( !empty($user_login) && empty($cache_userdata[$user_login]) ) {
		$user = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE user_login = '$user_login'"); // todo: get rid of this intermediate var 
		$cache_userdata[$user->ID] = $user;
		$cache_userdata[$user_login] =& $cache_userdata[$user->ID];
	} else {
		$user = $cache_userdata[$user_login];
	}
	return $user;
*/
}
endif;



if ( !function_exists('wp_mail') ) :
function wp_mail($to, $subject, $message, $headers = '') {
	if( $headers == '' ) {
		$headers = "MIME-Version: 1.0\n" .
			"From: " . get_settings('admin_email') . "\n" . 
			"Content-Type: text/plain; charset=\"" . get_settings('blog_charset') . "\"\n";
	}

	return @mail($to, $subject, $message, $headers);
}
endif;



if ( !function_exists('wp_login') ) :
function wp_login($username, $password, $already_md5 = false) {
/*	global $wpdb, $error;

	if ( !$username )
		return false;

	if ( !$password ) {
		$error = __('<strong>Error</strong>: The password field is empty.');
		return false;
	}

	$login = $wpdb->get_row("SELECT ID, user_login, user_pass FROM $wpdb->users WHERE user_login = '$username'");

	if (!$login) {
		$error = __('<strong>Error</strong>: Wrong username.');
		return false;
	} else {
		// If the password is already_md5, it has been double hashed.
		// Otherwise, it is plain text.
		if ( ($already_md5 && $login->user_login == $username && md5($login->user_pass) == $password) || ($login->user_login == $username && $login->user_pass == md5($password)) ) {
			return true;
		} else {
			$error = __('<strong>Error</strong>: Incorrect password.');
			$pwd = '';
			return false;
		}
	}
*/
}
endif;

if ( !function_exists('auth_redirect') ) :
function auth_redirect() {
	// Checks if a user is logged in, if not redirects them to the login page
/*	if ( (!empty($_COOKIE['wordpressuser_' . COOKIEHASH]) && 
				!wp_login($_COOKIE['wordpressuser_' . COOKIEHASH], $_COOKIE['wordpresspass_' . COOKIEHASH], true)) ||
			 (empty($_COOKIE['wordpressuser_' . COOKIEHASH])) ) {
		header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
	
		header('Location: ' . get_settings('siteurl') . '/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
		exit();
	}
*/
}
endif;

// Cookie safe redirect.  Works around IIS Set-Cookie bug.
// http://support.microsoft.com/kb/q176113/
if ( !function_exists('wp_redirect') ) :
function wp_redirect($location, $status = 302) {
	global $is_IIS;

	$location = apply_filters('wp_redirect', $location, $status);

	if ( !$location ) // allows the wp_redirect filter to cancel a redirect
		return false; 

	$location = preg_replace('|[^a-z0-9-~+_.?#=&;,/:%]|i', '', $location);
	# $location = wp_kses_no_null($location);
	
	$location = preg_replace('/\0+/', '', $location);
    $location = preg_replace('/(\\\\0)+/', '', $location);


	$strip = array('%0d', '%0a');
	$location = str_replace($strip, '', $location);

	if ( $is_IIS ) {
		header("Refresh: 0;url=$location");
	} else {
		if ( php_sapi_name() != 'cgi-fcgi' )
			utils_status_header($status); // This causes problems on IIS and some FastCGI setups
		header("Location: $location");
	}
}
endif;


if ( !function_exists('wp_setcookie') ) :
function wp_setcookie($username, $password, $already_md5 = false, $home = '', $siteurl = '') {
	if ( !$already_md5 )
		$password = md5( md5($password) ); // Double hash the password in the cookie.

	if ( empty($home) )
		$cookiepath = COOKIEPATH;
	else
		$cookiepath = preg_replace('|https?://[^/]+|i', '', $home . '/' );

	if ( empty($siteurl) ) {
		$sitecookiepath = SITECOOKIEPATH;
		$cookiehash = COOKIEHASH;
	} else {
		$sitecookiepath = preg_replace('|https?://[^/]+|i', '', $siteurl . '/' );
		$cookiehash = md5($siteurl);
	}

	setcookie('wordpressuser_'. $cookiehash, $username, time() + 31536000, $cookiepath);
	setcookie('wordpresspass_'. $cookiehash, $password, time() + 31536000, $cookiepath);

	if ( $cookiepath != $sitecookiepath ) {
		setcookie('wordpressuser_'. $cookiehash, $username, time() + 31536000, $sitecookiepath);
		setcookie('wordpresspass_'. $cookiehash, $password, time() + 31536000, $sitecookiepath);
	}
}
endif;

if ( !function_exists('wp_clearcookie') ) :
function wp_clearcookie() {
	setcookie('wordpressuser_' . COOKIEHASH, ' ', time() - 31536000, COOKIEPATH);
	setcookie('wordpresspass_' . COOKIEHASH, ' ', time() - 31536000, COOKIEPATH);
	setcookie('wordpressuser_' . COOKIEHASH, ' ', time() - 31536000, SITECOOKIEPATH);
	setcookie('wordpresspass_' . COOKIEHASH, ' ', time() - 31536000, SITECOOKIEPATH);
}
endif;



if ( !function_exists('check_admin_referer') ) :
function check_admin_referer($action = -1) {
	$adminurl = BLOG_BASEURL . 'admin.php';
	$referer = strtolower(wp_get_referer());
	if ( !wp_verify_nonce(@$_REQUEST['_wpnonce'], $action) &&
		!(-1 == $action && strstr($referer, $adminurl)) ) {
		wp_nonce_ays($action);
		die();
	}
	do_action('check_admin_referer', $action);
}
endif;


if ( !function_exists('wp_verify_nonce') ) :
function wp_verify_nonce($nonce, $action = -1) {

	$user = user_get();
	$uid = $user['userid'];

	$i = ceil(time() / 43200);

	//Allow for expanding range, but only do one check if we can
	if( substr(wp_hash($i . $action . $uid), -12, 10) == $nonce || substr(wp_hash(($i - 1) . $action . $uid), -12, 10) == $nonce )
		return true;
	return false;
}
endif;

if ( !function_exists('wp_create_nonce') ) :
function wp_create_nonce($action = -1) {
	$user = user_get();
	$uid = $user['userid'];

	$i = ceil(time() / 43200);
	
	return substr(wp_hash($i . $action . $uid), -12, 10);
}
endif;

if ( !function_exists('wp_salt') ) :
function wp_salt() {
	global $fp_config;
	static $salt = null;
	if (!$salt) {
		@include(HASHSALT_FILE);
		if (!$fp_hashsalt)
			trigger_error('Cannot load hash salt: reinstall FlatPress', E_USER_ERROR);
			
		$salt = $fp_hashsalt;
	}
	return $salt;
}
endif;

if ( !function_exists('wp_hash') ) :
function wp_hash($data) {
	$salt = wp_salt();
	return md5($data . $salt);
}
endif;


if ( ! function_exists('wp_notify_postauthor') ) :
function wp_notify_postauthor($comment_id, $comment_type='') {
/*	global $wpdb;
    
	$comment = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID='$comment_id' LIMIT 1");
	$post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID='$comment->comment_post_ID' LIMIT 1");
	$user = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID='$post->post_author' LIMIT 1");

	if ('' == $user->user_email) return false; // If there's no email to send the comment to

	$comment_author_domain = gethostbyaddr($comment->comment_author_IP);

	$blogname = get_settings('blogname');
	
	if ( empty( $comment_type ) ) $comment_type = 'comment';
	
	if ('comment' == $comment_type) {
		$notify_message  = sprintf( __('New comment on your post #%1$s "%2$s"'), $comment->comment_post_ID, $post->post_title ) . "\r\n";
		$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
		$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
		$notify_message .= sprintf( __('URI    : %s'), $comment->comment_author_url ) . "\r\n";
		$notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $comment->comment_author_IP ) . "\r\n";
		$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
		$notify_message .= __('You can see all comments on this post here: ') . "\r\n";
		$subject = sprintf( __('[%1$s] Comment: "%2$s"'), $blogname, $post->post_title );
	} elseif ('trackback' == $comment_type) {
		$notify_message  = sprintf( __('New trackback on your post #%1$s "%2$s"'), $comment->comment_post_ID, $post->post_title ) . "\r\n";
		$notify_message .= sprintf( __('Website: %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
		$notify_message .= sprintf( __('URI    : %s'), $comment->comment_author_url ) . "\r\n";
		$notify_message .= __('Excerpt: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
		$notify_message .= __('You can see all trackbacks on this post here: ') . "\r\n";
		$subject = sprintf( __('[%1$s] Trackback: "%2$s"'), $blogname, $post->post_title );
	} elseif ('pingback' == $comment_type) {
		$notify_message  = sprintf( __('New pingback on your post #%1$s "%2$s"'), $comment->comment_post_ID, $post->post_title ) . "\r\n";
		$notify_message .= sprintf( __('Website: %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
		$notify_message .= sprintf( __('URI    : %s'), $comment->comment_author_url ) . "\r\n";
		$notify_message .= __('Excerpt: ') . "\r\n" . sprintf( __('[...] %s [...]'), $comment->comment_content ) . "\r\n\r\n";
		$notify_message .= __('You can see all pingbacks on this post here: ') . "\r\n";
		$subject = sprintf( __('[%1$s] Pingback: "%2$s"'), $blogname, $post->post_title );
	}
	$notify_message .= get_permalink($comment->comment_post_ID) . "#comments\r\n\r\n";
	$notify_message .= sprintf( __('To delete this comment, visit: %s'), get_settings('siteurl').'/wp-admin/post.php?action=confirmdeletecomment&p='.$comment->comment_post_ID."&comment=$comment_id" ) . "\r\n";

	if ('' == $comment->comment_author_email || '' == $comment->comment_author) {
		$from = "From: \"$blogname\" <wordpress@" . $_SERVER['SERVER_NAME'] . '>';
	} else {
		$from = 'From: "' . $comment->comment_author . "\" <$comment->comment_author_email>";
	}

	$notify_message = apply_filters('comment_notification_text', $notify_message);
	$subject = apply_filters('comment_notification_subject', $subject);
	$message_headers = apply_filters('comment_notification_headers', $message_headers);

	$message_headers = "MIME-Version: 1.0\n"
		. "$from\n"
		. "Content-Type: text/plain; charset=\"" . get_settings('blog_charset') . "\"\n";

	@wp_mail($user->user_email, $subject, $notify_message, $message_headers);
   
	return true;
*/
}
endif;

/* wp_notify_moderator
   notifies the moderator of the blog (usually the admin)
   about a new comment that waits for approval
   always returns true
 */
if ( !function_exists('wp_notify_moderator') ) :
function wp_notify_moderator($comment_id) {
/*	global $wpdb;

	if( get_settings( "moderation_notify" ) == 0 )
		return true; 
    
	$comment = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID='$comment_id' LIMIT 1");
	$post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID='$comment->comment_post_ID' LIMIT 1");

	$comment_author_domain = gethostbyaddr($comment->comment_author_IP);
	$comments_waiting = $wpdb->get_var("SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'");

	$notify_message  = sprintf( __('A new comment on the post #%1$s "%2$s" is waiting for your approval'), $post->ID, $post->post_title ) . "\r\n";
	$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
	$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
	$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
	$notify_message .= sprintf( __('URI    : %s'), $comment->comment_author_url ) . "\r\n";
	$notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $comment->comment_author_IP ) . "\r\n";
	$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
	$notify_message .= sprintf( __('To approve this comment, visit: %s'),  get_settings('siteurl').'/wp-admin/post.php?action=mailapprovecomment&p='.$comment->comment_post_ID."&comment=$comment_id" ) . "\r\n";
	$notify_message .= sprintf( __('To delete this comment, visit: %s'), get_settings('siteurl').'/wp-admin/post.php?action=confirmdeletecomment&p='.$comment->comment_post_ID."&comment=$comment_id" ) . "\r\n";
	$notify_message .= sprintf( __('Currently %s comments are waiting for approval. Please visit the moderation panel:'), $comments_waiting ) . "\r\n";
	$notify_message .= get_settings('siteurl') . "/wp-admin/moderation.php\r\n";

	$subject = sprintf( __('[%1$s] Please moderate: "%2$s"'), get_settings('blogname'), $post->post_title );
	$admin_email = get_settings("admin_email");

	$notify_message = apply_filters('comment_moderation_text', $notify_message);
	$subject = apply_filters('comment_moderation_subject', $subject);

	@wp_mail($admin_email, $subject, $notify_message);
    
	return true;
*/
}
endif;

?>
