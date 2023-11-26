<?php

$lang ['admin'] ['plugin'] ['submenu'] ['bbcode'] = 'BBCode';
$lang ['admin'] ['plugin'] ['bbcode'] = array(
	'head' => 'BBCode Configuration',
	'desc1' => 'This plugin allows using <a href="https://wiki.flatpress.org/'.
		'doc:techfaq#bbcode" class="hint" target="_blank">BBCode</a> markup.',
	
	'options' => 'Options',

	'editing' => 'Editing',
	'allow_html' => 'Inline HTML',
	'allow_html_long' => 'Enable using HTML along with BBCode',
	'toolbar' => 'Toolbar',
	'toolbar_long' => 'Enable the editor toolbar.',

	'other'	=> 'Other options',
	'comments' => 'Comments',
	'comments_long' => 'Allow BBCode in comments',
	'urlmaxlen' => 'URL max length',
	'urlmaxlen_long_pre' => 'Shorten URLs longer than ',
	'urlmaxlen_long_post' => ' characters.',
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
		// note: accesskeys are not internationalized...
		// btw. why not :-D
		'bold' => 'B',
		'boldtitle' => 'Bold',
		'italic' => 'I',
		'italictitle' => 'Italic',
		'underline' => 'U',
		'underlinetitle' => 'Underlined',
		'quote' => 'Quote',
		'quotetitle' => 'Quote',
		'code' => 'Code',
		'codetitle' => 'Code',
		'help' => 'BBCode Help',
		'file' => 'File: ',
		'image' => 'Image: ',
		'selection' => '-- Selection --',
		// currently not used
		'status' => 'Status bar',
		'statusbar' => 'Normal mode. Press &lt;Esc&gt; to switch editing mode.'
	)
);

$lang ['plugin'] ['bbcode'] = array (
		'go_to' => 'Go to',
		// language tag for Facebook Video
		'langtag' => 'en_US'
);

?>
