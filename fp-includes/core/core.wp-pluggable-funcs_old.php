<?php

	/* These functions can be replaced via plugins.  They are loaded after
	 plugins are loaded. */


	function get_settings() {
	
	}

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
	

?>