<?php
/**
 * BBcode Validate Criteria: isValidFileDownloadUrl
 * Ensures the URL does not contain unsafe characters or sequences.
 */

function isValidFileDownloadUrl($value) {
	// Define a regex to disallow script execution and unsafe characters
	$unsafePatterns = '/(<|>|"|\(|\)|javascript:|onerror=|onload=|<script|<\/script|&lt;|&gt;)/i';

	// Check if the value contains unsafe patterns
	if (preg_match($unsafePatterns, $value)) {
		return false;
	}

	// Allow URLs only in specific formats (optional)
	$validUrlPatterns = '/^[a-zA-Z0-9\-._~:\/?#\[\]@!$&\'()*+,;=%]+$/';
	if (!preg_match($validUrlPatterns, $value)) {
		return false;
	}

	return true;
}
?>
