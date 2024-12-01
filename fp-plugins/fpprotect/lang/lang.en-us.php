<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'FlatPress Protect settings',
	'desc1' => 'Here you can change security-relevant options for your FlatPress blog. ' . //
		'The best protection for your visitors and your FlatPress blog is when all options are deactivated.',

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

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Retain metadata and original image quality in uploaded images.',
	'allowImageMetadataDsc' => 'After images have been uploaded with the uploader, the metadata is retained. This includes camera information and geo-coordinates, for example.',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'Allow FlatPress to use the non-anonymized IP address of the visitor.',
	'allowVisitorIpDsc' => 'FlatPress will then save the non-anonymized IP address in comments, among other things. ' . //
		'If you use the Akismet Antispam service, Akismet will also receive the non-anonymized IP address.',

	'submit' => 'Save settings',
		'msgs' => array(
		1 => 'Settings saved successfully.',
		-1 => 'Error when saving the settings.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Warning: Content-Security-Policy -> This policy contains "unsafe-inline", which is dangerous in the script-src-policy.',
	'warning_allowVisitorIp' => 'Warning: Use of non-anonymized IP addresses of the visitor -> Don\'t forget to inform the <a href="static.php?page=privacy-policy" title="edit static page">visitors of your FlatPress blog</a> about this!'
);
?>
