<?php
/*
 * Smarty plugin
 * ------------------------------------------------------------- 
 * File:     resource.shared.php
 * Type:     shared tpls
 * Name:     shared
 * Purpose:  convenient way to call stored tpls in SHARED_TPLS
 * -------------------------------------------------------------
 */
function smarty_resource_shared_source($tpl_name, &$tpl_source, &$smarty)
{
    if ($tpl_source = io_load_file(SHARED_TPLS . $tpl_name)) {
        return true;
    } else {
        return false;
    }
}

function smarty_resource_shared_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{
    if (file_exists(SHARED_TPLS . $tpl_name)) {
        $tpl_timestamp = filemtime(SHARED_TPLS . $tpl_name);
        return true;
    } else {
        return false;
    }
}

function smarty_resource_shared_secure($tpl_name, &$smarty)
{
    return true;
}

function smarty_resource_shared_trusted($tpl_name, &$smarty)
{
    
}
?> 
