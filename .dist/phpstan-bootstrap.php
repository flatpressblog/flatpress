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
		/**
		 * Simulates retrieving a plugin from the Smarty compiler system.
		 *
		 * @param string $name Name of the plugin.
		 * @param string $type Type of the plugin (e.g., 'function', 'modifier').
		 * @return string Dummy return value representing a plugin function.
		 */
		public function getPlugin(string $name, string $type) {
			return 'dummy_plugin_function';
		}

		/**
		 * Simulates compiling a Smarty tag.
		 *
		 * @param string $tag The name of the Smarty tag.
		 * @param array $args Arguments passed to the tag.
		 * @param array $params Additional parameters for compilation.
		 * @return string Dummy output representing compiled tag code.
		 */
		public function compileTag(string $tag, array $args = [], array $params = []) {
			return 'compiled_tag_output';
		}
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
		/**
		 * Constant defining a variable filter type.
		 */
		const FILTER_VARIABLE = 'variable';

		/** @var array Default modifiers applied to variables. */
		public $default_modifiers = [];

		/** @var array Manually registered filter functions. */
		public $registered_filters = [];

		/** @var array Autoloaded filters, grouped by type. */
		public $autoload_filters = [];

		/** @var bool Whether HTML output should be automatically escaped. */
		public $escape_html = false;

		/** @var string Charset used for template output. */
		public static $_CHARSET = 'UTF-8';
	}
}
