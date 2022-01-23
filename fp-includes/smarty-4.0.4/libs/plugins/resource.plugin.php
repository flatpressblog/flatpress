<?php
/*
 * Smarty plugin
 * ------------------------------------------------------------- 
 * File:     resource.plugin.php
 * Type:     plugin tpls
 * Name:     plugin
 * Purpose:  convenient way to call stored tpls in PLUGINS_DIR
 * Use:	plugin:PLUGINNAME/PLUGINFILE realpath=> PLUGINS_DIR/plugin.PLUGINNAME/PLUGINFILE
 * -------------------------------------------------------------
 */
 

function smarty_resource_plugin_parsename($tpl_name) {
	$path = null;
	
	$f=explode('/',$tpl_name);
	$path = ABS_PATH . PLUGINS_DIR . "{$f[0]}/tpls/{$f[1]}.tpl";
	
	return $path;
		
}

function smarty_resource_plugin_source($tpl_name, &$tpl_source, &$smarty)
{
	$fname = smarty_resource_plugin_parsename($tpl_name);
	if ($tpl_source = io_load_file($fname)) {
		return true;
	} else {
		return false;
	}
}

function smarty_resource_plugin_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{
    $fname = smarty_resource_plugin_parsename($tpl_name);
    if (file_exists($fname)) {
        $tpl_timestamp = filemtime($fname);
        return true;
    } else {
        return false;
    }
}

function smarty_resource_plugin_secure($tpl_name, &$smarty)
{
    return true;
}

function smarty_resource_plugin_trusted($tpl_name, &$smarty)
{
    
}
?> 
