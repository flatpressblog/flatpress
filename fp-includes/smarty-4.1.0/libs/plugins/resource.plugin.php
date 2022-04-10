<?php

/**
 * Resoure plugin that conveniently allows to include templates from the plugin templates folder.
 * Usage: <code>{plugin:PLUGINNAME/PLUGINFILE}</code> will load from <i>PLUGINS_DIR/plugin.PLUGINNAME/PLUGINFILE</i>
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

