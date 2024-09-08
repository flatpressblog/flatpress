<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.toolbar.php
 * Type:     function
 * Name:     editortop
 * Purpose:  outputs a random magic answer
 * Hint:     {toolbar} does not work well in the template with Smarty 4. Is no longer used as of FP 1.3 Andante Beta 1.
 * See:      #184 and #287 May still be required for the responsiveadmin branch.
 * -------------------------------------------------------------
 */
function smarty_function_toolbar($params, &$smarty) {
	do_action('editor_toolbar');
}
?>
