<?php
	
	$lang['admin']['widgets']['submenu']['blockparser'] = 'BlockParser Widgets';
	
	$lang['admin']['widgets']['blockparser'] = array(
		'head'		=> 'BlockParser Widgets',
		'description'	=> 'BlockParser plugin allows you to create a widget from a static page. </p><p>
		Select one or more static pages from the list to make a corresponding widget available.</p><p>
		Each <a href="?p=static&amp;action=write">new static page</a> you create will be listed here.',

		'id'		=> 'Static page',
		'title'		=> 'Title',
		'action'	=> 'Action',
		'enable'	=> 'Enable',
		'disable'	=> 'Disable',
		'edit'		=> 'Edit',
		
	);
	$lang['admin']['widgets']['blockparser']['msgs'] = array(
		1		=> 'Your new widget is available. Add it to your blog from the <a href="?p=widgets">main panel!</a>',
		-1		=> 'Can\'t create the requested widget',
		2		=> 'You have disabled a widget: don\'t forget to remove any references from the <a href="?p=widgets">main panel</a>!',
		-2		=> 'Can\'t disable the widget'
	);
	
?>
