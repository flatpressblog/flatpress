<?php

/**
 * Resource plugin that conveniently allows to include templates from the shared templates folder via {include file="shared:example.tpl"}
 *
 * @author FlatPress
 * @see https://www.smarty.net/docs/en/plugins.resources.tpl
 */
class Smarty_Resource_Shared extends Smarty_Resource_Custom {

	/**
	 * Fetch template source and modification time.
	 *
	 * @param string $name   Template name
	 * @param string|null &$source Template source returned by reference (null if not found)
	 * @param int|null    &$mtime  Modification time returned by reference (null if not found)
	 *
	 * @phpstan-param-out string|null $source
	 * @phpstan-param-out int|null    $mtime
	 *
	 * {@inheritdoc}
	 * @see Smarty_Resource_Custom::fetch()
	 */
	protected function fetch($name, &$source, &$mtime) {
		if ($source = io_load_file(SHARED_TPLS . $name)) {
			$filemtime = filemtime(SHARED_TPLS . $name);
			$mtime = $filemtime !== false ? $filemtime : null;
		} else {
			$source = null;
			$mtime = null;
		}
	}

}
?>
