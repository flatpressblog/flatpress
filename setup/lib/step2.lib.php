<?php

function setup_create_content() {
	global $lang, $err, $fp_setup_state;
	if (!validate()) {
		return false;
	}
	$sample = $lang ['samplecontent'];
	if (!isset($fp_setup_state['entry_date'])) {
		// Stable across retries: later write failures must not duplicate the sample post.
		if (!setup_checkpoint(['entry_date' => date_time()])) {
			$err [] = $lang ['err'] ['writeerror'];
			return false;
		}
	}
	if (!is_int($fp_setup_state ['entry_date']) && !is_string($fp_setup_state ['entry_date'])) {
		$err [] = $lang ['err'] ['state'];
		return false;
	}
	$entry = entry_save(['subject' => $sample ['entry'] ['subject'], 'content' => $sample ['entry'] ['content'], 'date' => $fp_setup_state ['entry_date'], 'version' => system_ver(), 'author' => 'FlatPress']);
	// entry_save returns a string ID on success and negative integers on failure.
	if (!is_string($entry) || $entry === '') {
		$err [] = $lang ['err'] ['writeerror']; return false;
	}
	foreach (['menu', 'bottommenu', 'about', 'legal-notice', 'privacy-policy'] as $id) {
		if (!static_exists($id) && !static_save(['subject' => $sample [$id] ['subject'], 'content' => $sample [$id] ['content'], 'date' => date_time(), 'version' => system_ver(), 'author' => 'FlatPress'], $id)) {
			$err [] = $lang ['err'] ['writeerror']; return false;
		}
	}
	return true;
}

?>
