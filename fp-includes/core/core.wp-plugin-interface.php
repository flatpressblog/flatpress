<?php

	// plugins.php
	// plugin interface
	
	// This is EXACTLY a copy & paste from wordpress
	
	// Filters: these are the core of WP's plugin architecture
	
	function merge_filters($tag) {
		global $wp_filter;
		if (isset($wp_filter['all'])) {
			foreach ($wp_filter['all'] as $priority => $functions) {
				if (isset($wp_filter[$tag][$priority]))
					$wp_filter[$tag][$priority] = array_merge($wp_filter['all'][$priority], $wp_filter[$tag][$priority]);
				else
					$wp_filter[$tag][$priority] = array_merge($wp_filter['all'][$priority], array());
				$wp_filter[$tag][$priority] = array_unique($wp_filter[$tag][$priority]);
			}
		}
	
		if ( isset($wp_filter[$tag]) )
			ksort( $wp_filter[$tag] );
	}
	
	function apply_filters($tag, $string) {
		global $wp_filter;
		
		$args = array_slice(func_get_args(), 2);
	
		merge_filters($tag);
		
		if (!isset($wp_filter[$tag])) {
			return $string;
		}
		foreach ($wp_filter[$tag] as $priority => $functions) {
			if (!is_null($functions)) {
				foreach($functions as $function) {
	
					$all_args = array_merge(array($string), $args);
					$function_name = $function['function'];
					$accepted_args = $function['accepted_args'];
	
					if($accepted_args == 1) {
						$the_args = array($string);
					} elseif ($accepted_args > 1) {
						$the_args = array_slice($all_args, 0, $accepted_args);
					} elseif($accepted_args == 0) {
						$the_args = NULL;
					} else {
						$the_args = $all_args;
					}
	
					$string = call_user_func_array($function_name, $the_args);
				}
			}
		}
		return $string;
	}
	
	function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
		global $wp_filter;
	
		// check that we don't already have the same filter at the same priority
		if (isset($wp_filter[$tag]["$priority"])) {
			foreach($wp_filter[$tag]["$priority"] as $filter) {
				// uncomment if we want to match function AND accepted_args
				//if ($filter == array($function, $accepted_args)) {
				if ($filter['function'] == $function_to_add) {
					return true;
				}
			}
		}
	
		// So the format is wp_filter['tag']['array of priorities']['array of ['array (functions, accepted_args)]']
		$wp_filter[$tag]["$priority"][] = array('function'=>$function_to_add, 'accepted_args'=>$accepted_args);
		//added by NoWhereMan
		ksort($wp_filter[$tag]["$priority"]);
		return true;
	}
	
	function remove_filter($tag, $function_to_remove, $priority = 10, $accepted_args = 1) {
		global $wp_filter;
		
		$new_function_list = array();
	
		// rebuild the list of filters
		if (isset($wp_filter[$tag]["$priority"])) {
			foreach($wp_filter[$tag]["$priority"] as $filter) {
				if ($filter['function'] != $function_to_remove) {
					$new_function_list[] = $filter;
				}
			}
			$wp_filter[$tag]["$priority"] = $new_function_list;
		}
		return true;
	}
	
	// The *_action functions are just aliases for the *_filter functions, they take special strings instead of generic content
	
	function do_action($tag, $arg = '') {
		global $wp_filter;
		$extra_args = array_slice(func_get_args(), 2);
		if ( is_array($arg) )
			$args = array_merge($arg, $extra_args);
		else
			$args = array_merge(array($arg), $extra_args);
		
		merge_filters($tag);
		
		if (!isset($wp_filter[$tag])) {
			return;
		}
		foreach ($wp_filter[$tag] as $priority => $functions) {
			if (!is_null($functions)) {
				foreach($functions as $function) {
	
					$function_name = $function['function'];
					$accepted_args = $function['accepted_args'];
	
					if($accepted_args == 1) {
						if ( is_array($arg) )
							$the_args = $arg;
						else
							$the_args = array($arg);
					} elseif ($accepted_args > 1) {
						$the_args = array_slice($args, 0, $accepted_args);
					} elseif($accepted_args == 0) {
						$the_args = NULL;
					} else {
						$the_args = $args;
					}
	
					$string = call_user_func_array($function_name, $the_args);
				}
			}
		}
	}
	
	function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
		add_filter($tag, $function_to_add, $priority, $accepted_args);
	}
	
	function remove_action($tag, $function_to_remove, $priority = 10, $accepted_args = 1) {
		remove_filter($tag, $function_to_remove, $priority, $accepted_args);
	}


	//----------------------------------------------------------------------------
	// WordPress hooks
	//----------------------------------------------------------------------------
	 /*
	 Current Hooks For Actions 
	This is a comprehensive list of plugin hooks in the core distribution of WordPress as of version 1.5 beta 1. 
	
	NOTE: the following list is not a comprehensive listing of hooks available in 1.5 final. See Skippy's list (http://codex.wordpress.org/User:Skippy) for a more comprehensive, if less descriptive, listing of actions and filters. 
	
	admin_footer 
		No parameter. Executes at the end of the admin panel inside the body tag. Useful for insertion of additional content. 
	admin_head 
		No parameter. Executes in the <head> section of the admin panel. Useful for insertion of additional content. 
	admin_menu 
		No parameter. Executes after the basic admin panel menu structure is in place. Useful for adding additional menus to the admin panel. 
	comment_closed 
		Receives the comment's post ID as a parameter. Executes when attempting to display the comment form for a post that has closed comments. 
	comment_form 
		Receives the comment's post ID as a parameter. Template tag. Executes after displaying the comment form for a post that allows comments. 
	comment_id_not_found 
		Receives the comment's post ID as a parameter. Executes when attempting to display the comment form for a post that does not exist. 
	comment_post 
		Receives the comment ID as a parameter. Executes when a comment is added through wp-comments.php. 
	delete_comment 
		Receives the comment ID as a parameter. Executes when a comment is deleted. 
	delete_post 
		Receives the post ID as a parameter. Executes whenever a post is deleted. 
	edit_comment 
		Receives the comment ID as a parameter. Executes whenever a comment is edited. 
	edit_form_advanced 
		No parameter. Executes during the display of the admin panel's advanced editing page, just before the <div> is closed that contains the post content textarea. Useful for inserting additional input fields into the advanced editing form. 
	edit_page_form 
		No parameter. Executes inside the <form> tag on the page editing form. Useful for inserting additional input fields in the page editing form. 
	edit_post 
		Receives the post ID as a parameter. Executes every time a post is edited. 
	generate_rewrite_rules 
		No parameter. Executes whenever the rewrite rules are recomputed. To modify the computed rules, use the filter rewrite_rules_array instead. 
	init 
		Executes after WordPress has finished loading but before any headers are sent. Useful for intercepting $_GET or $_POST triggers. 
	pingback_post 
		Receives the comment ID as a parameter. Executes when a comment is added via XMLRPC. 
	private_to_published 
		Receives the post ID as a parameter. Executes when a post is moved from private to published status. 
	publish_phone 
		Receives the post ID as a parameter. Executes when a post is added via wp-mail.php. 
	publish_post 
		Receives the post ID as a parameter. Executes when a post is saved and its status is set to "publish", regardless of its prior setting. NOTE: to add a hook to this action in 1.2, be sure to specify a priority between 0 and 9. The generic_ping hook is buggy and prevents any lesser priority hooks from working. 
	save_post 
		Receives the post ID as a parameter. Executes when a post is saved to the database. 
	shutdown 
		No parameter. Executes when the page output is complete. 
	simple_edit_form 
		No parameter. Executes during the display of the admin panel's simple editing page, just before the <div> is closed that contains the post content textarea. Useful for inserting additional input fields into the simple editing form. 
	switch_theme 
		Receives the name of the current theme as a parameter. Executes when the blog theme is changed. 
	template_redirect 
		No parameter. Executes before the determination of the template file to be used to display the requested page. Useful for providing additional templates based on request criteria. Example (pedagogical, not useful): Redirect all requests to the all.php template file in the current themes' directory. 
	function all_on_one () {
		include(TEMPLATEPATH . '/all.php');
		exit;
	}
	
	add_action('template_redirect', 'all_on_one');
	trackback_post 
		Receives the comment ID as a parameter. Executes when a comment is added via trackback.php. 
	wp_footer 
		No parameter. Template tag. Executes at the end of the <body> tag. Useful for insertion of additional content. 
	wp_head 
		No parameter. Executes in the <head> section. Useful for insertion of additional content. 
	wp_meta 
		No parameter. Executes in the <li>Meta</li> section of the included Theme's sidebar.php's. Useful for insertion of additional content. 
	wp_set_comment_status 
		Receives the comment ID as a parameter. Executes when the comment status changes. 
	*/
	

	
	

?>