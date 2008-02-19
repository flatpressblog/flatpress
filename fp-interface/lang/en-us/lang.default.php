<?php

	$lang = array();

	$lang['main'] = array(
		
		'nextpage'		=> 'Next Page &raquo;',
		'prevpage'		=> '&laquo; Previous Page',
		'entry'      	=> 'Entry',
		'static'     	=> 'Static page',
		'comment'    	=> 'Comment',
		'preview'    	=> 'Edit/Preview',
		
		'filed_under'	=> 'Filed under ',	
		
		'add_entry'  	=> 'Add Entry',
		'add_comment'  	=> 'Add Comment',
		'add_static'  	=> 'Add Static Page',
		
		'btn_edit'     	=> 'Edit',
		'btn_delete'   	=> 'Delete',
		
		'nocomments'	=> 'Add a comment',
		'comment'	=> '1 comment',
		'comments'	=> 'comments',
		
	);
	
	$lang['search'] = array(
		
		'head'	=> 'Search',
		'fset1'	=> 'Insert search criteria',
		'keywords'	=> 'Phrase',
		'onlytitles'	=> 'Only titles',
		'fulltext'	=> 'Full-text',
		
		'fset2'	=> 'Date',
		'datedescr'	=> 'You can bind your search to a specific date. You may select an year, an year and a month, or a full date. '.
					'Leave blank to search the entire database.',
		
		'fset3' 	=> 'Search in categories',
		'catdescr'	=> 'Don\'t select any to search all',
		
		'fset4'	=> 'Start Searching',
		'submit'	=> 'Search',
		
		'headres'	=> 'Search Results',
		'descrres'	=> 'Searching for <strong>%s</strong> returned the following results:',
		'descrnores'=> 'Searching for <strong>%s</strong> returned no results.',
		
		'moreopts'	=> 'More options',
		
		
		'searchag'	=> 'Search again',
		
	);
	
	$lang['search']['error'] = array(
	
		'keywords'	=> 'You must specify at least one keyword'
	
	);
	
	
	
	
	
	$lang['entry'] = array();
	$lang['entry']['flags'] = array();
	
	$lang['entry']['flags']['long'] = array(
		'draft' => '<strong>Draft entry</strong>: hidden, awaiting publication',
		//'static' => '<strong>Static entry</strong>: normally hidden, to reach the entry put ?page=title-of-the-entry in url (experimental)',
		'commslock' => '<strong>Comments locked</strong>: comments disallowed for this entry'
	);
	
	$lang['entry']['flags']['short'] = array(
		'draft' => 'Draft',
		//'static' => 'Static',
		'commslock' => 'Comments locked'
	);

	$lang['404error'] = array(
		'subject'	=> 'Not Found',
		'content'	=> '<p>Sorry, we could not find the page you requested</p>'
	);
		
	// Login
	$lang['login'] = array(
		
		'head'		=> 'Login',
		'fieldset1'	=> 'Insert your user name and password',
		'user'		=> 'Username:',
		'pass'		=> 'Password:',
		'fieldset2'	=> 'Do login',
		'submit'	=> 'Login',
		'forgot'	=> 'Password lost'
	);
		
	$lang['login']['success'] = array(
		'success'	=> 'You are now logged in.',
		'logout'	=> 'You are now logged out.',
		'redirect'	=> 'You will be redirected in 5 seconds.',
		'opt1'		=> 'Back to index',
		'opt2'		=> 'Go to Control Panel',
		'opt3'		=> 'Add new entry'
	);
	
	$lang['login']['error'] = array(
		'user'		=> 'You must enter a username.',
		'pass'		=> 'You must enter a password.',
		'match'		=> 'Password incorrect.'
	);
	
	
	$lang['comments'] = array(
		'head'		=> 'Add comment',
		'descr'		=> 'Fill out the form below to add your own comments',
		'fieldset1'	=> 'User data',
		'name'		=> 'Name (*)',
		'email'		=> 'Email:',
		'www'		=> 'Web:',
		'cookie'	=> 'Remember me',
		'fieldset2'	=> 'Add your comment',
		'comment'	=> 'Comment (*):',
		'fieldset3'	=> 'Send',
		'submit'	=> 'Add',
		'reset'		=> 'Reset',
		'success'	=> 'Your comment was added successfully',
		'nocomments'	=> 'This entry have not been commented yet',
		'commslock'	=> 'Comments have been disabled for this entry',
	);
	
	$lang['comments']['error'] = array(
		'name'		=> 'You must enter a name',
		'email'		=> 'You must enter a valid email',
		'www'		=> 'You must enter a valid URL',
		'comment'	=> 'You must enter a comment',
	);
	
	$lang['date']['month'] = array(
		
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
		
	);

	$lang['date']['month_abbr'] = array(
		
		'Jan',
		'Feb',
		'Mar',
		'Apr',
		'May',
		'Jun',
		'Jul',
		'Aug',
		'Sep',
		'Oct',
		'Nov',
		'Dec'
		
	);

	$lang['date']['weekday'] = array(
		
		'Sunday',
		'Monday',
		'Tuesday',
		'Wednesday',
		'Thursday',
		'Friday',
		'Saturday',
		
	);

	$lang['date']['weekday_abbr'] = array(
		
		'Sun',
		'Mon',
		'Tue',
		'Wed',
		'Thu',
		'Fri',
		'Sat',
		
	);



?>
