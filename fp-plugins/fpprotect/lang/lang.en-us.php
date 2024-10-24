<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'FlatPress Protect settings',
	'desc1' => 'Here you can change security-relevant options for your FlatPress blog.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'Allow unsafe Java scripts (Not recommended)',

	'allowUnsafeInlineDsc' => '<p>Allows the loading of unsafe inline JavaScript code.</p>' . //
		'<p><br>Note to plugin developers: Please add a nonce to your Java script.</p>' . //
		'An example for PHP:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'An example of the Smarty template:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>This ensures that the visitor\'s browser only executes Java scripts that originate from your FlatPress blog.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Allow the creation and editing of the .htaccess file.',
	'allowPrettyURLEditDsc' => 'Allows access to the .htaccess edit field of the PrettyURLs plugin to create or modify the .htaccess file.',

	'submit' => 'Save settings',
		'msgs' => array(
		1 => 'Settings saved successfully.',
		-1 => 'Error when saving the settings.'
	)
);
?>
