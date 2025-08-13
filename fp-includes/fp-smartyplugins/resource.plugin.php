<?php

/**
 * Resoure plugin that conveniently allows to include templates from the plugin templates folder.
 * Usage: <code>{plugin:PLUGINNAME/PLUGINFILE}</code> will load from <i>PLUGINS_DIR/plugin.PLUGINNAME/PLUGINFILE</i>
 *
 * @author FlatPress
 * @see https://www.smarty.net/docs/en/plugins.resources.tpl
 */
class Smarty_Resource_Plugin extends \Smarty\Resource\CustomPlugin {

	/**
	 * Fetches the template source and its modification time.
	 *
	 * @param string $name Template name
	 * @param string|null &$source [output] Template source code, or null if not found
	 * @param int|null &$mtime [output] Last modification timestamp, or null if not found
	 * @return void
	 *
	 * @phpstan-param-out string|null $source
	 * @phpstan-param-out int|null $mtime
	 *
	 * {@inheritdoc}
	 * @see Smarty_Resource_Custom::fetch()
	 */
	protected function fetch($name, &$source, &$mtime) {
		$filePath = $this->getFilePath($name);

		if ($source = io_load_file($filePath)) {
			$fTime = filemtime($filePath);
			$mtime = is_int($fTime) ? $fTime : null;
		} else {
			$source = null;
			$mtime = null;
		}
	}

	private function getFilePath($templateName) {
		$path = null;

		$f = explode('/', $templateName);
		$path = ABS_PATH . PLUGINS_DIR . $f [0] . "/tpls/" . $f [1] . ".tpl";

		return $path;
	}

}
?>
