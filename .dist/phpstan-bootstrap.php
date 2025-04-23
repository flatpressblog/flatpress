<?php
/**
 * Simulates the Smarty 4.5.5 template engine for PHPStan, e.g. for dynamic methods/filters
 * This is necessary if you do not want to change the Smarty code
 * and do not want to get errors with dynamic behavior such as plugin lookups.
 *
 * Dummy class acting as a fallback for Smarty_Internal_TemplateCompilerBase.
 *
 * This class is used when the actual Smarty compiler class is not available.
 * It's primarily useful for unit tests, mocking, or ensuring compatibility
 * in environments where Smarty is not installed.
 */
if (!class_exists('Smarty_Internal_TemplateCompilerBase')) {
	class Smarty_Internal_TemplateCompilerBase {
		public function getPlugin(string $name, string $type) { return 'dummy_plugin_function'; }
		public function compileTag(string $tag, array $args = [], array $params = []) { return 'compiled_tag_output'; }
	}
}

/**
 * Dummy replacement for the core Smarty class.
 *
 * Provides basic structure and properties found in the real Smarty class
 * to allow testing or fallback behavior in environments where Smarty is missing.
 */
if (!class_exists('Smarty')) {
	class Smarty {
		const FILTER_VARIABLE = 'variable';
		public $default_modifiers = [];
		public $registered_filters = [];
		public $autoload_filters = [];
		public $escape_html = false;
		public static $_CHARSET = 'UTF-8';

		// Supplemented for FlatPress & plugins
		public function assign($tpl_var, $value = null, $nocache = false) {}
		public function assignByRef($tpl_var, &$value) {}
		public function display($template = null, $cache_id = null, $compile_id = null, $parent = null) {}
		public function registerPlugin($type, $name, $callback, $cacheable = true, $cache_attr = null) {}
		public function getTemplateVars($name = null, $search_parents = true) {}
		public function setTemplateDir($template_dir) {}
		public function addPluginsDir($plugins_dir) {}
		public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false) { return ''; }

		// Typical properties
		public $compile_id;
		public $cache_id;
	}
}

if (!class_exists('Smarty_Resource_Custom')) {
	class Smarty_Resource_Custom {}
}
if (!class_exists('Smarty_Resource_Admin')) {
	class Smarty_Resource_Admin extends Smarty_Resource_Custom {}
}
if (!class_exists('Smarty_Resource_Plugin')) {
	class Smarty_Resource_Plugin extends Smarty_Resource_Custom {}
}
if (!class_exists('Smarty_Resource_Shared')) {
	class Smarty_Resource_Shared extends Smarty_Resource_Custom {}
}
