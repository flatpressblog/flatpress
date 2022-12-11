<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.comment_form.php
 * Type:     function
 * Name:     commentform
 * Purpose:  print out the comment form
 * -------------------------------------------------------------
 */
function smarty_function_comment_form($params, &$smarty)
{
    return do_action('comment_form');
}
?> 
