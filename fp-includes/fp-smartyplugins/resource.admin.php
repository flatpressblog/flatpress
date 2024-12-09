<?php

/**
 * Resoure plugin that conveniently allows to include templates from the admin templates folder.
 *
 * @author FlatPress
 * @see https://www.smarty.net/docs/en/plugins.resources.tpl
 */
class Smarty_Resource_Admin extends Smarty_Resource_Custom {

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
		$panel = strtok($templateName, '/');
		if ($action = strtok('/')) {
			$path = ABS_PATH . ADMIN_DIR . "panels/" . $panel . "/admin." . $panel . "." . $action . ".tpl";
		}
		if (!$action || !file_exists($path)) {
			$path = ABS_PATH . ADMIN_DIR . "panels/" . $panel . "/admin." . $panel . ".tpl";
		}
		return $path;
	}

}
?>
