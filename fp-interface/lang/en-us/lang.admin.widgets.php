<?php

	$lang['admin']['widgets']['submenu']['default'] = 'Manage Widgets';
	$lang['admin']['widgets']['submenu']['raw'] 	= 'Manage Widgets (raw)';

	/* default action */
	
	$lang['admin']['widgets']['default'] = array(
		'head'		=> 'Manage Widgets (<em>experimental</em>)',
		
		'descr'		=> 	'A <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:widgets" title="What is a Widget?">'.
						'Widget</a> is a dynamic component that can display data and interact with the user.
						While <strong>Themes</strong> are meant to change how your blog looks like, Widgets 
						<strong>extend</strong> looks and functionalities.</p>

						<p>Widgets can be dragged to special areas of your theme called the 
						<strong>WidgetSets</strong>. The number and the name of the  WidgetSets may vary with the 
						theme you choose.</p>

						<p>FlatPress comes with several widgets: there are widgets to help with the login, to 
						display a search box, etc.</p>
						
						<p>Each Widget is defined by a <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a Widget?">plugin</a>.',
						
		'availwdgs'	=> 'Available Widgets',
		'trashcan'	=> 'Drop here to delete',
		
		'themewdgs' => 'Widgetsets for this theme',
		'themewdgsdescr' => 'The theme you have currently selected let you have the following widgetsets',
		'oldwdgs'	=> 'Other widgetsets',
		'oldwdgsdescr' =>'The following widgetsets seems not to belong to any of the '.
						'widgetsets listed above. They might be remainders from another theme.',
		
		'submit'	=> 'Save Changes',

	);
	
	$lang['admin']['widgets']['default']['stdsets'] = array(
		'top'		=> 'Top bar',
		'bottom'	=> 'Bottom bar',
		'left'		=> 'Left bar',
		'right'		=> 'Right bar',
	);
	
	$lang['admin']['widgets']['default']['msgs'] = array(
		1	=> 'Config saved',
		-1	=> 'An error occurred while trying to save, please try again',
	);


	
	/* "raw" panel */	
	
	$lang['admin']['widgets']['raw'] = array(
		'head'		=> 'Manage Widgets (<em>raw editor</em>)',
		'descr'		=> 'A <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a Widget?">'.
						'Widget</a> is a visual element of a <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a plugin?">'.
						'Plugin</a> that you can put in some special areas (the <em>widgetsets</em>) on your blog pages. </p>'.
						'<p>This is the <strong>raw</strong> editor; some advanced users or people who '.
						'can\'t have JavaScript might prefer it.',
						
		'fset1'		=> 'Editor',
		'fset2'		=> 'Apply Changes',
		'submit'	=> 'Apply',

	);
	
	
	$lang['admin']['widgets']['raw']['msgs'] = array(
		1	=> 'Config saved',
		-1	=> 'An error occurred while trying to save. This may happen for several reasons: maybe your file contains syntax errors.',
	);

		

	/* system errors */
		
	$lang['admin']['widgets']['errors'] = array(
		'generic'	=> 'The widget called <strong>%s</strong> is not registered, and will be skipped. '.
 				'Is the plugin enabled in the <a href="admin.php?p=plugin">plugin panel</a>?'

	);
	
?>
