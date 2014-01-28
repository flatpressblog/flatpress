<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {html_submit} function plugin
 *
 * File:       function.html_submit.php<br>
 * Type:       function<br>
 * Name:       html_submit<br>
 * Date:       25.Jul.2006<br>
 * Purpose:    Create a simple submit button but also saves its id/name into the $_SESSION
 * 		(used by flatpress) <br>
 * Input:<br>
 *           - name       (optional) - string default "submit"
 *	     - id	  (optional) - string default blank
 *           - value      (optional) - string (default "Submit")
 *	     - extra	  (optional) - string (default blank): allows to add extra 
 *					parameters as in "class=\"myclass\" onclick=\"dosomething()\""
 *           
 * @author     NoWhereMan <real_nowhereman at users dot sf dot net>
 * @version    1.0
 * @param array
 * @param Smarty
 * @return string
 */
 
 
function smarty_block_html_form($params, $content, &$smarty)
{
 	
 	if (!isset($params['name']) && defined('ADMIN_PANEL')) {
 		$params['name'] = 'admin_' . ADMIN_PANEL . '_' . ADMIN_PANEL_ACTION;
 		
 	}
 	
 	$str = '<form '; 
 	if (!isset($params['method']))
 		$params['method']="post";
 	/* temporary */
 	if (!isset($params['action']))
		$params['action']=BLOG_BASEURL . "admin.php?p=". ADMIN_PANEL.'&amp;action=' .ADMIN_PANEL_ACTION;
		
	if (!isset($params['enctype']))
		$params['enctype']="application/x-www-form-urlencoded"; // multipart/form-data

	
	foreach ($params as $key => $val) {
		$str .= "{$key}=\"" . ($val) . "\" " ;
	}
	
	$str .= ">\n";
	
	ob_start();
	wp_nonce_field($params['name']);
	$nonce = ob_get_contents();
	ob_end_clean();
	
	return 
		$str .
		$nonce . 
		$content .
	 	'</form>';

}

?>
