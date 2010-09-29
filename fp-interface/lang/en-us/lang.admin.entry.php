<?php


	$lang['admin']['entry']['submenu'] = 
	array (
		'list'		=> 'Manage Entries',
		'write'		=> 'Write Entry',
		'cats'		=> 'Manage Categories'
	);


	/* default action */
	
	$lang['admin']['entry']['list'] = 
	array(
		'head'		=> 'Manage Entries',
		'descr'		=> 'Please select an entry to edit or <a href="admin.php?p=entry&amp;action=write">add new</a>'.
					'<br /><a href="admin.php?p=entry&amp;action=cats">Edit the categories</a>',
		'filter'	=> 'Filter: ',
		'nofilter'	=> 'Show all',
		'filterbtn'	=> 'Apply filter',
		'sel'		=> 'Sel', // checkbox
		'date'		=> 'Date',
		'title'		=> 'Title',
		'author'	=> 'Author',
		'comms'		=> '#Comms', // comments
		'action'	=> 'Action',
		'act_del'	=> 'Delete',
		'act_view'	=> 'View',
		'act_edit'	=> 'Edit'
	);
	
	/* write action */
	$lang['admin']['entry']['write'] = 
	array(
		'head'		=> 'Write Entry',
		'descr'		=> 'Edit the form to write the entry',
		'uploader'	=> 'Uploader',
		'fieldset1'	=> 'Edit',
		'subject'	=> 'Subject (*):',
		'content'	=> 'Content (*):',
		'fieldset2'	=> 'Submit',
		'submit'	=> 'Publish',
		'preview'	=> 'Preview',
		'savecontinue'	=> 'Save&amp;Continue',
		'archive'	=> 'Archive',
		'nocategories'	=> 'No categories set. <a href="admin.php?p=entry&amp;action=cats">Create your own '. 
					'categories</a> from the main entry panel. '.
					'<a href="#save">Save</a> your entry first.',
		'saveopts'	=> 'Saving options',
		'success'	=> 'Your entry was published succesfully',
		'otheropts'	=> 'Other options',
		'commmsg'	=> 'Manage comments for this entry',
		'delmsg'	=> 'Delete this entry',
		//'back'		=> 'Back discarding changes',
	);
	

	$lang['admin']['entry']['list']['msgs'] = array(
		1	=> 'Entry has been saved successfully',
		-1	=> 'An error occurred while trying to save 
					the entry',
		2	=> 'Entry has been deleted successfully',
		-2	=>	 'An error occurred while trying to delete 
					the entry',
	);

	
	$lang['admin']['entry']['write']['error'] = array(
		'subject'	=> 'You can\'t send a blank subject',
		'content'	=> 'You can\'t post a blank entry',
	);
	
	$lang['admin']['entry']['write']['msgs'] = array(
		1	=> 'Entry has been saved successfully',
		-1	=> 'An error occurred: your entry could not be saved successfully',
		-2	=> 'An error occurred: your entry has not been saved; index might have become corrupt',
		-3	=> 'An error occurred: your entry has been saved as draft',
		-4	=> 'An error occurred: your entry has been saved as draft; index might have become corrupt',
		'draft'=> 'You are editing a <strong>draft</strong> entry'
	);

	
	/* comments */
	
	$lang['admin']['entry']['commentlist'] = 
	array(
		'head'		=> "Comments for entry ", 
		'descr'		=> 'Select a comment to delete',
		'sel'		=> 'Sel',
		'content'	=> 'Content',
		'date'		=> 'Date',
		'author'	=> 'Author',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'actions'	=> 'Actions',
		'act_edit'	=> 'Edit',
		'act_del'	=> 'Delete',
		'act_del_confirm' => 'Do you really want to delete this comment?',
		'nocomments'	=> 'This entry have not been commented, yet.',
		
	
	);

	$lang['admin']['entry']['commentlist']['msgs'] =
	array(
		1	=> 'Comment has been deleted successfully',
		-1	=> 'An error occurred while trying to delete 
					the comment',
		
	);

	$lang['admin']['entry']['commedit'] = 
	array(
		'head'		=> "Edit comment for entry", 
		'content'	=> 'Content',
		'date'		=> 'Date',
		'author'	=> 'Author',
		'www'		=> 'Web Site',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'loggedin'	=> 'Registered user',
		'submit'	=> 'Save'
		
	
	);

	$lang['admin']['entry']['commedit']['msgs'] =
	array(
		1	=> 'Comment has been edited',
		-1	=> 'An error occurred while trying to edit the comment',
	);
	
	/* delete action */
	
	$lang['admin']['entry']['delete'] = 
	array(
		'head'		=> 'Delete Entry', 
		'descr'		=> 'You\'re about to delete the following entry:',
		'preview'	=> 'Preview',
		'confirm'	=> 'Are you sure you want to proceed?',
		'fset'		=> 'Delete',
		'ok'		=> 'Yes, delete this entry',
		'cancel'	=> 'No, take me back to the panel',
		'err'		=> 'The specified entry does not exist',
	
	);
	
	/* category mgmt */
	
	$lang['admin']['entry']['cats'] =
	array(
		'head'		=> 'Edit categories',
		'descr'		=> '<p>Use the form below to add and edit your categories. </p><p>Each category item should be in this format "category name: <em>id_number</em>". Indent items with dashes to create hierarchies.</p>
		
	<p>Example:</p>
	<pre>
General :1
News :2
--Announcements :3
--Events :4
----Misc :5
Technology :6
	</pre>',
		'clear'		=> 'Delete all categories data',
	
		'fset1'		=> 'Editor',
		'fset2'		=> 'Apply Changes',
		'submit'	=> 'Save'
	);
	
	$lang['admin']['entry']['cats']['msgs'] = array(
		
		1	=> 'Categories saved',
		-1	=> 'An error occurred while trying to save categories',
		2	=> 'Categories cleared',
		-2	=> 'An error occurred while trying to clear categories',
		-3 	=> 'Category IDs must be strictly positive (0 is not allowed)'

	);
	
	
		
?>
