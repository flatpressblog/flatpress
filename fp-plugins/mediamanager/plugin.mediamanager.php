<?php
/*
Plugin Name: Media Manager
Version: 0.4
Plugin URI: http://kirgroup.com/blog/
Description: Manage uloaded files ad photo galleries
Author: Fabrix.xm
Author URI: http://kirgroup.com/fabrixxm/
*/

// config
define('ITEMSPERPAGE', 10);


//
function mediamanager_updateUseCountArr(&$files,$fupd){
	$params = array();
	$params['start']=0;
	$params['count']=-1;
	$params['fullparse'] = true;
	$q = new FPDB_Query($params, null);
	while ($q->hasMore()) {
		list($id, $e) = $q->getEntry();
		if (isset($e['content'])){
			foreach($fupd as $id){
				if (is_null($files[$id]['usecount'])) $files[$id]['usecount']=0;
				if ($files[$id]['type']=='gallery'){
					$searchterm="[gallery=images/".$files[$id]['name'];
				} else {
					$searchterm=$files[$id]['type']."/".$files[$id]['name'];
				}
				if (strpos($e['content'], $searchterm) !== false) $files[$id]['usecount']++;
			}
		}
	}

	$usecount=array();
	foreach($files as $info){
		$usecount[$info['name']]=$info['usecount'];
	}
	plugin_addoption('mediamanager', 'usecount', $usecount);
	plugin_saveoptions('mediamanager');
}


if (class_exists('AdminPanelAction')){


    include(plugin_getdir('mediamanager') .'/panels/panel.mediamanager.file.php');

    
}



/* invalidate count on entry save and delete */
function mediamanager_invalidatecount($arg){
	plugin_addoption('mediamanager', 'usecount', array());
	plugin_saveoptions('mediamanager');
	return $arg;
}
add_filter('delete_post', 'mediamanager_invalidatecount', 1);
add_filter('content_save_pre', 'mediamanager_invalidatecount', 1);


?>
