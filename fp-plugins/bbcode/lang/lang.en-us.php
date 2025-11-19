<?php
$lang ['admin'] ['plugin'] ['submenu'] ['bbcode'] = 'BBCode';
$lang ['admin'] ['plugin'] ['bbcode'] = array(
	'head' => 'BBCode Configuration',
	'desc1' => 'This plugin allows using <a href="https://wiki.flatpress.org/' . //
		'doc:techfaq#bbcode" class="hint" target="_blank">BBCode</a> markup.',

	'options' => 'Options',

	'editing' => 'Editing',
	'allow_html' => 'Inline HTML',
	'allow_html_long' => 'Enable using HTML along with BBCode',
	'toolbar' => 'Toolbar',
	'toolbar_long' => 'Enable the editor toolbar.',

	'other' => 'Other options',
	'comments' => 'Comments',
	'comments_long' => 'Allow BBCode in comments',
	'urlmaxlen' => 'URL max length',
	'urlmaxlen_long_pre' => 'Shorten URLs longer than ',
	'urlmaxlen_long_post' => ' characters.',

	'attachsdir' => 'File downloads',
	'attachsdir_long' => 'Do not display the upload directory (fp-content/attachs/) in the URL.',

	'submit' => 'Save configuration',
	'msgs' => array(
		1 => 'BBCode configuration successful saved.',
		-1 => 'BBCode configuration not saved.'
	),

	'editor' => array(
		'formatting' => 'Formatting',
		'textarea' => 'Textarea: ',
		'expand' => 'Expand',
		'expandtitle' => 'Expand Textarea Height',
		'reduce' => 'Reduce',
		'reducetitle' => 'Reduce Textarea Height',
		'urltitle' => 'URL/ Link',
		'mailtitle' => 'E-mail address',
		'boldtitle' => 'Bold',
		'italictitle' => 'Italic',
		'headlinetitle' => 'Headline',
		'fonttitle' => 'Font',
		'underlinetitle' => 'Underline',
		'crossouttitle' => 'Crossed out',
		'unorderedlisttitle' => 'Unsorted list',
		'orderedlisttitle' => 'Sorted list',
		'quotetitle' => 'Quote',
		'codetitle' => 'Code',
		'htmltitle' => 'Insert as HTML code',
		'help' => 'BBCode Help',
		'file' => 'File: ',
		'image' => 'Image: ',
		'selection' => '-- Selection --'
	)
);

$lang ['plugin'] ['bbcode'] = array (
	'go_to' => 'Go to',

	// Filewrapper get.php
	'error_403' => 'Error 403',
	'not_send' => 'The requested file cannot be sent.',
	'error_404' => 'Error 404',
	'not_found' => 'The requested file could not be found.',
	'file' => 'File',
	'report_error_1' => '',
	'report_error_2' => 'Report error',
	'blog_search_1' => 'search',
	'blog_search_2' => 'in the blog',
	'start_page_1' => 'or back to the',
	'start_page_2' => 'start page'
);
?>
