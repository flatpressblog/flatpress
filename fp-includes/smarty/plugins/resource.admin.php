<?php
/*
 * Smarty plugin
 * ------------------------------------------------------------- 
 * File:     resource.admin.php
 * Type:     admin tpls
 * Name:     admin
 * Purpose:  convenient way to call stored tpls in ADMIN_DIR
 * -------------------------------------------------------------
 */
 

function smarty_resource_admin_parsename($tpl_name) {
	$path = null;
	
	$tpl_name;
	
	$panel = strtok($tpl_name, '/');
	if ($action = strtok('/'))
		$path = ABS_PATH . ADMIN_DIR . "panels/$panel/admin.$panel.$action.tpl";
	if (!$action || !file_exists($path))
		$path = ABS_PATH . ADMIN_DIR . "panels/$panel/admin.$panel.tpl";
	
	return $path;
		
}

function smarty_resource_admin_source($tpl_name, &$tpl_source, &$smarty)
{
	$fname = smarty_resource_admin_parsename($tpl_name);
	if ($tpl_source = io_load_file($fname)) {
		return true;
	} else {
		return false;
	}
}

function smarty_resource_admin_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{
    $fname = smarty_resource_admin_parsename($tpl_name);
    if (file_exists($fname)) {
        $tpl_timestamp = filemtime($fname);
        return true;
    } else {
        return false;
    }
}

function smarty_resource_admin_secure($tpl_name, &$smarty)
{
    return true;
}

function smarty_resource_admin_trusted($tpl_name, &$smarty)
{
    
}
?> 
