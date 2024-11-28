<?php
/**
 * BBcode Validate Criteria: isValidFileDownloadUrl
 * Ensures the URL does not contain unsafe characters or sequences.
 */

function isValidFileDownloadUrl($value) {
	// Define a regex to disallow script execution and unsafe characters
	$unsafePatterns = '/(<|>|"|\(|\)|javascript:|on\w+\s*=|<script|<\/script|&lt;|&gt;)/i';
	if (preg_match($unsafePatterns, $value)) {
		return false;
	}
	// Allow URLs only in specific formats (optional)
	return preg_match('/^[a-zA-Z0-9\-._~:\/?#\[\]@!$&\'()*+,;=%]+$/u', $value) === 1;
}
?>
