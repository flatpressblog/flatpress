<?php

/**
 * Resoure plugin that conveniently allows to include templates from the plugin templates folder via {include file="plugin:example.tpl"}
 *
 * @author FlatPress
 * @see https://www.smarty.net/docs/en/plugins.resources.tpl
 */
class Smarty_Resource_Plugin extends Smarty_Resource_Custom {

	/**
	 *
	 * {@inheritdoc}
	 * @see Smarty_Resource_Custom::fetch()
	 */
	protected function fetch($name, &$source, &$mtime) {
		$filePath = $this->getFilePath($name);

		if ($source = io_load_file($filePath)) {
			$mtime = filemtime($filePath);
		} else {
			$source = null;
			$mtime = null;
		}
	}

	private function getFilePath($templateName) {
		$path = null;

		$f = explode('/', $templateName);
		$path = ABS_PATH . PLUGINS_DIR . "{$f[0]}/tpls/{$f[1]}.tpl";

		return $path;
	}

}

// /*
//  * Smarty plugin
//  * -------------------------------------------------------------
//  * File: resource.plugin.php
//  * Type: plugin tpls
//  * Name: plugin
//  * Purpose: convenient way to call stored tpls in PLUGINS_DIR
//  * Use: plugin:PLUGINNAME/PLUGINFILE realpath=> PLUGINS_DIR/plugin.PLUGINNAME/PLUGINFILE
//  * -------------------------------------------------------------
//  */
// function smarty_resource_plugin_parsename($tpl_name) {
// 	$path = null;

// 	$f = explode('/', $tpl_name);
// 	$path = ABS_PATH . PLUGINS_DIR . "{$f[0]}/tpls/{$f[1]}.tpl";

// 	return $path;
// }

// function smarty_resource_plugin_source($tpl_name, &$tpl_source, &$smarty) {
// 	$fname = smarty_resource_plugin_parsename($tpl_name);
// 	if ($tpl_source = io_load_file($fname)) {
// 		return true;
// 	} else {
// 		return false;
// 	}
// }

// function smarty_resource_plugin_timestamp($tpl_name, &$tpl_timestamp, &$smarty) {
// 	$fname = smarty_resource_plugin_parsename($tpl_name);
// 	if (file_exists($fname)) {
// 		$tpl_timestamp = filemtime($fname);
// 		return true;
// 	} else {
// 		return false;
// 	}
// }
