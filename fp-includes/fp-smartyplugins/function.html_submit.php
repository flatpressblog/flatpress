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
function smarty_function_html_submit($params, &$smarty)
{
 	// $_SESSION[ADMIN_PANEL][ADMIN_PANEL_ACTION] = 
 	// (!isset($params['name'])? $params['name'] = 'submit' : $params['name']);
 	
 	$params['name'] = (isset($params['name'])? $params['name'] : $params['name'] = 'submit' );
 	
 	//admin_addpanelactionevent(ADMIN_PANEL, ADMIN_PANEL_ACTION, $params['name']);
 	    
    $str = '<input type="submit" ';
	
	foreach ($params as $key => $val) {
		$str .= "{$key}=\"" . ($val) . "\" " ;
	}
	
	$str .= " />\n";
	return $str;

}

?>
