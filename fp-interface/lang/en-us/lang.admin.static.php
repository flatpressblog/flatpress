<?php

	$lang['admin']['static']['submenu'] = array(
		'list'		=> 'Manage Statics',
		'write'		=> 'Write Static'
	);

	
	/* main panel */
		
	$lang['admin']['static']['list'] = array(
	
		'head'		=> 'Static pages',
		'descr'		=> 'Please select a page to edit or <a href="admin.php?p=static&amp;action=write">add new</a>.',
	
		'sel'		=> 'Sel', // checkbox
		'date'		=> 'Date',
		'name'		=> 'Page',
		'title'		=> 'Title',
		'author'	=> 'Author',
		
		'action'	=> 'Action',
		'act_view'	=> 'View',
		'act_del'	=> 'Delete',
		'act_edit'	=> 'Edit'		
	);
	
	$lang['admin']['static']['list']['msgs'] = array(
		1	=> 'Page has been saved successfully',
		-1	=> 'An error occurred while trying to save 
					the page',
		2	=> 'Page has been deleted successfully',
		-2	=>	 'An error occurred while trying to delete 
					the page',
	);

	/* write panel */

	$lang['admin']['static']['write'] = 
	array(
		'head'		=> 'Publish Static Page',
		'descr'		=> 'Edit the form to publish the page',
		'fieldset1'	=> 'Edit',
		'subject'	=> 'Subject (*):',
		'content'	=> 'Content (*):',
		'fieldset2'	=> 'Submit',
		'pagename'	=> 'Page Name (*):',
		'submit'	=> 'Publish',
		'preview'	=> 'Preview',

		'delfset'	=> 'Delete',
		'deletemsg'	=> 'Delete this page',
		'del'		=> 'Delete',
		'success'	=> 'Your page was published succesfully',
		'otheropts'	=> 'Other options',
	);
	
	$lang['admin']['static']['write']['error'] = array(
		'subject'	=> 'You can\'t send a blank subject',
		'content'	=> 'You can\'t post a blank entry',
		'id'		=> 'You must send a valid id'
	);
	
	
	/* delete action */	
	$lang['admin']['static']['delete'] = array(
		'head'		=> "Delete Page", 
		'descr'		=> 'You\'re about to delete the following page:',
		'preview'	=> 'Preview',
		'confirm'	=> 'Are you sure you want to proceed?',
		'fset'		=> 'Delete',
		'ok'		=> 'Yes, delete this page',
		'cancel'	=> 'No, take me back to the panel',
		'err'		=> 'The specified page does not exists',
	
	);
	
	
		
?>
