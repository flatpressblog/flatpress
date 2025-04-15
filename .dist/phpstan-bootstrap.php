<?php

// Dummy class
if (!class_exists('Smarty_Internal_TemplateCompilerBase')) {
	class Smarty_Internal_TemplateCompilerBase {
		public function getPlugin(string $name, string $type) {
			return 'dummy_plugin_function';
		}

		public function compileTag(string $tag, array $args = [], array $params = []) {
			return 'compiled_tag_output';
		}
	}
}

if (!class_exists('Smarty')) {
	class Smarty {
		const FILTER_VARIABLE = 'variable';
		public $default_modifiers = [];
		public $registered_filters = [];
		public $autoload_filters = [];
		public $escape_html = false;
		public static $_CHARSET = 'UTF-8';
	}
}
