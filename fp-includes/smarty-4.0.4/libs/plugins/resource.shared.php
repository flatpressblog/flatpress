<?php

/**
 * Resoure plugin that conveniently allows to include templates from the shared templates folder via {include file="shared:example.tpl"}
 *
 * @author FlatPress
 * @see https://www.smarty.net/docs/en/plugins.resources.tpl
 */
class Smarty_Resource_Shared extends Smarty_Resource_Custom {

	/**
	 *
	 * {@inheritdoc}
	 * @see Smarty_Resource_Custom::fetch()
	 */
	protected function fetch($name, &$source, &$mtime) {
		if ($source = io_load_file(SHARED_TPLS . $name)) {
			$mtime = filemtime(SHARED_TPLS . $name);
		} else {
			$source = null;
			$mtime = null;
		}
	}

}