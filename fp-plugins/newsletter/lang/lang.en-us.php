<?php
$lang ['plugin'] ['newsletter'] = array(
	// Widget
	'subject' => 'Newsletter',
	'input_email_placeholder' => 'Your e-mail address',
	'accept_privacy_policy' => 'I accept the privacy policy',
	'privacy_link_text' => 'go to the privacy policy',
	'button' => 'Subscribe',
	'csrf_error' => 'Invalid CSRF token.',

	// Double Opt-In
	'confirm_subject' => 'Please confirm your newsletter subscription',
	'confirm_greeting' => 'Thank you for subscribing to our monthly newsletter.',
	'confirm_link_text' => 'Click here to confirm your subscription',
	'confirm_ignore' => 'If you have not requested this email, please ignore it.',

	// E-Mail-Content
	'last_entries' => 'Last entries',
	'no_entries' => 'No entries',
	'last_comments' => 'Latest comments',
	'no_comments' => 'No comments',
	'unsubscribe' => 'Unsubscribe newsletter',
	'privacy_policy' => 'Privacy policy',
	'legal_notice' => 'Legal notice'
);

// Admin panel: Newsletter subscribers
$lang ['admin'] ['plugin'] ['submenu'] ['newsletter'] = 'Newsletter';
$lang ['admin'] ['plugin'] ['newsletter'] = array(
	'head' => 'Newsletter management',
	'desc_subscribers' => 'Here you can see all the e-mail addresses of the newsletter subscribers and when the subscribers have accepted the privacy policy. ' . //
		'You can also delete subscribers.',
	'admin_subscribers_list' => 'Subscriber list',
	'email_address' => 'E-mail address',
	'subscribe_date' => 'Date',
	'subscribe_time' => 'Time',
	'newsletter_no_subscribers' => 'No subscribers available',
	'delete_subscriber' => 'Delete',
	'delete_confirm' => 'Do you really want to delete this address?',
	'desc_batch' => 'Here you can specify how many subscribers a newsletter is sent to per day. '. //
		'Ask your email provider how many emails can be sent per day. ' . //
		'The newsletter is automatically sent to all subscribers at the beginning of the month. ' . //
		'If no automatic dispatch is currently running, you can also trigger the newsletter dispatch immediately. ' . //
		'If immediate dispatch has not been completed by the 28th of the month, all subscribers will not automatically receive the regular newsletter until the month after next.',
	'icon_sent_title' => 'Already delivered in this shipment',
	'icon_sent_alt' => 'Delivered',
	'icon_queued_title' => 'Scheduled for next batch',
	'icon_queued_alt' => 'Scheduled',
	'send_all_button' => 'Send newsletter to all subscribers now',
	'send_all_confirm' => 'Would you like to send the newsletter to all subscribers now?',
	'send_type_monthly' => 'Monthly dispatch.',
	'send_type_manual'  => 'Manual dispatch.',
	'sub_remaining' => 'Still to be sent:',
	'batch_size_label' => 'Number of emails per batch',
	'save_button' => 'Save'
);

$lang ['plugin'] ['newsletter'] ['errors'] = array (
	-2 => 'The LastEntries plugin must be active for you to be able to use this plugin.'
);

$lang ['admin'] ['plugin'] ['newsletter'] ['msgs'] = array(
	1 => 'Newsletter is sent to all subscribers.',
	-2 => 'This plugin requires the LastEntries plugin integrated in FlatPress. Please activate it beforehand in the plugin area!',
	2 => 'Settings have been saved.'
);
?>
