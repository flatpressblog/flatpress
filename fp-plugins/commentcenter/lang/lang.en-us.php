<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = 'Comment Center';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Header of the panel
	'title' => 'Comment Center',
	'desc1' => 'This panel allows you to manage the comments on your blog.',
	'desc2' => 'Here you can do several things:',

	// Links
	'lpolicies' => 'Manage the policies',
	'lapprove' => 'Show blocked comments',
	'lmanage' => 'Manage comments',
	'lconfig' => 'Configure the plugin',

	// Policies
	'policies' => 'Policies',
	'desc_pol' => 'Here you can edit the policies of comments.',
	'select' => 'Select',
	'criteria' => 'Criteria',
	'behavoir' => 'Behavoir',
	'options' => 'Options',
	'entry' => 'Entry',
	'entries' => 'Entries',
	'categories' => 'Categories',
	'nopolicies' => 'There isn\'t any policy.',
	'all_entries' => 'All Entries',
	'fol_entries' => 'The policy is applied to the following entries:',
	'fol_cats' => 'The policy is applied to entries in the following categories:',
	'older' => 'The policy is applied to entries older than %d day(s).',
	'allow' => 'Allow to comment',
	'block' => 'Block comments',
	'approvation' => 'Comments need to be approved',
	'up' => 'Move up',
	'down' => 'Move down',
	'edit' => 'Edit',
	'delete' => 'Delete',
	'newpol' => 'Add a new policy',
	'del_selected' => 'Delete selected policy(ies)',
	'select_all' => 'Select All',
	'deselect_all' => 'Deselect All',

	// Configuration page
	'configure' => 'Configure the plugin',
	'desc_conf' => 'Here you can modify the options of the plugin.',
	'log_all' => 'Log blocked comments',
	'log_all_long' => 'Check it if you want to log also comments that are blocked.',
	'email_alert' => 'Notify comments via email',
	'email_alert_long' => 'Check it if you want that when there is a comment to approve you want to be ' . 'informed via email.',
	'akismet' => 'Akismet',
	'akismet_use' => 'Enable Akismet check',
	'akismet_key' => 'Akismet Key',
	'akismet_key_long' => 'The Akismet service provide you a key to use it. Insert here.',
	'akismet_url' => 'Blog base URL for Akismet',
	'akismet_url_long' => 'I think for Akismet free service you should use just a domain. ' . 'You can leave blank this field, <code>%s</code> will be used.',
	'save_conf' => 'Save Configuration',

	// Edit policy page
	'apply_to' => 'Apply to',
	'editpol' => 'Edit a policy',
	'createpol' => 'Create a policy',
	'some_entries' => 'Some Entries',
	'properties' => 'Entry with certain properties',
	'se_desc' => 'If you selected the %s option, please insert entries you want to apply to this policy.',
	'se_fill' => 'Please fill the fields with the id of the entries (<code>entryYYMMDD-HHMMSS</code>).',
	'po_title' => 'Properties',
	'po_desc' => 'If you selected the %s option, please fill the properties.',
	'po_comp' => 'The fields aren\'t compulsory but you must fill at least one or the policy ' . 'will apply on all entries.',
	'po_time' => 'Time options',
	'po_older' => 'Apply to entries older than ',
	'days' => 'days.',
	'save_policy' => 'Save Policy',

	// Delete policies page
	'del_policies' => 'Delete Policies',
	'del_descs' => 'You are going to delete this policy: ',
	'del_descm' => 'You are going to delete these policies: ',
	'sure' => 'Are you sure?',
	'del_subs' => 'Yes, please delete it',
	'del_subm' => 'Yes, please delete them',
	'del_cancel' => 'No, take me back to the panel',

	// Approve comments page
	'app_title' => 'Approve comment',
	'app_desc' => 'Here you can approve comments.',
	'app_date' => 'Date',
	'app_content' => 'Comment',
	'app_author' => 'Author',
	'app_email' => 'Email',
	'app_ip' => 'IP',
	'app_actions' => 'Actions',
	'app_publish' => 'Publish',
	'app_delete' => 'Delete',
	'app_nocomms' => 'There isn\'t any comment.',
	'app_pselected' => 'Publish selected comment(s)',
	'app_dselected' => 'Remove selected comment(s)',
	'app_other' => 'Other Comments',
	'app_akismet' => 'Signed as spam',
	'app_spamdesc' => 'These comments were blocked by Akismet.',
	'app_hamsubmit' => 'Submit to Akismet as ham when you publish them.',
	'app_pubnotham' => 'Publish it but dont\'submit as ham',

	// Delete comments page
	'delc_title' => 'Delete Comments',
	'delc_descs' => 'You are going to delete this comment: ',
	'delc_descm' => 'You are going to delete these comments: ',

	// Manage comments page
	'man_searcht' => 'Search an entry',
	'man_searchd' => 'Insert the id of the entry whose you want to manage comments.',
	'man_search' => 'Search',
	'man_commfor' => 'Comments for %s',
	'man_spam' => 'Submit as spam to Akismet',

	// The simple edit
	'simple_pre' => 'Comments for this entry will ',
	'simple_1' => 'be allowed.',
	'simple_0' => 'require your approval.',
	'simple_-1' => 'be blocked.',
	'simple_manage' => 'Manage the comments of this entry.',
	'simple_edit' => 'Edit Policies',

	// Akismet warnings
	'akismet_errors' => array(
		-1 => 'The Akismet key is empty. Please insert it.',
		-2 => 'We couldn\'t call Akismet servers.',
		-3 => 'The Akismet response failed.',
		-4 => 'The Akismet key is not valid.'
	),

	// Messages
	'msgs' => array(
		1 => 'Configuration saved.',
		-1 => 'An error occurred while trying to save the configuration.',

		2 => 'Policy saved.',
		-2 => 'An error occurred while trying to save the policy (maybe your settings are wrong).',

		3 => 'Policy moved.',
		-3 => 'An error occurred while trying to move the policy (or it can\'t be moved).',

		4 => 'Policy(ies) removed.',
		-4 => 'An error occurred while trying to remove the policy(ies) (or you haven\'t selected any policy).',

		5 => 'Comment(s) published.',
		-5 => 'An error occurred while trying to publish the comment(s).',

		6 => 'Comment(s) removed.',
		-6 => 'An error occurred while trying to remove the comment(s) (or you haven\'t selected any comment).',

		7 => 'Comment submitted.',
		-7 => 'An error occurred while trying to submit the comment.'
	),

	// Errors
	'errors' => array(
		'pol_nonex' => 'The policy you want to edit does not exist.',
		'entry_nf' => 'The entry you have selected does not exist.'
	)
);
$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => 'Sorry, we\'re encountering technical difficulties.',
	'lock' => 'Comments for this entry are blocked, sorry.',
	'approvation' => 'The comments has been saved but the Administrator must approve it before showing it.',

	// Mail for comments
	'mail_subj' => 'New comment to approve on %s'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = 'Dear %toname%,

"%fromname%" %frommail% has just posted a comment to the entry entitled "%entrytitle%"
but it need your approval before showing it.

Here is the comment that has just been posted:
***************
%content%
***************

All the best,
%blogtitle%

';
