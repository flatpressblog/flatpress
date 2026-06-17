<?php
/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.comment_mastodon.php
 * Type:     function
 * Name:     commentform
 * Purpose:  Displays an opt-in for visitor comments
 * -------------------------------------------------------------
 */
function smarty_function_comment_mastodon($params, &$smarty) {
	return do_action('comment_mastodon');
}
?>
