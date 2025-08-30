<?php
/**
 * Smarty prefilter:
 * Injects "{views id=$id assign=views}" into theme templates that still use "{$views}".
 * Runs at compile time. Safe with plugin disabled if the Core fallback for {views} is present.
 */
function smarty_prefilter_postviews_assign($tpl_source, $template) {
	// Only if "{$views}" exists
	if (strpos($tpl_source, '{$views}') === false) {
		return $tpl_source;
	}
	// Skip if an explicit assignment already exists
	if (strpos($tpl_source, '{views') !== false && strpos($tpl_source, 'assign=views') !== false) {
		return $tpl_source;
	}

	// Limit to theme/template files to avoid admin/plugin resources
	$ok = true;
	if (is_object($template) && isset($template->source) && is_object($template->source)) {
		$type = isset($template->source->type) ? $template->source->type : null;
		$file = isset($template->source->filepath) ? $template->source->filepath : (isset($template->source->name) ? $template->source->name : '');
		if ($type && $type !== 'file') {
			$ok = false;
		}
		$path = str_replace('\\', '/', (string)$file);
		if ($path && !preg_match('~/(themes|templates)/~', $path)) {
			$ok = false;
		}
	}
	if (!$ok) {
		return $tpl_source;
	}

	// Prepend silent assignment; {views assign=...} returns ''
	return '{views id=$id assign=views}' . "\n" . $tpl_source;
}
?>
