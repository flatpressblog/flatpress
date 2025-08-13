<?php
declare(strict_types=1);

/**
 * PHPStan stubs for Smarty 5 – no runtime effect!
 * Covers both worlds:
 *  - \Smarty\Smarty (new, namespaced class)
 *  - Smarty (old, global short form in docblocks)
 * Plus a minimal template API.
 */
namespace Smarty {
	class Smarty {
		public const COMPILECHECK_OFF = 0;
		public const COMPILECHECK_ON  = 1;
		public const COMPILECHECK_CACHEMISS = 2;

		public const PLUGIN_FUNCTION = 'function';
		public const PLUGIN_BLOCK = 'block';
		public const PLUGIN_MODIFIER = 'modifier';
		public const PLUGIN_MODIFIERCOMPILER = 'modifiercompiler';
		public const PLUGIN_COMPILER = 'compiler';

		public function setCompileDir($dir): void {}
		public function setCacheDir($dir): void {}
		public function setCaching($flag): void {}
		public function setDebugging($flag): void {}

		public function setCompileCheck(int $mode): void {}
		public function setForceCompile(bool $flag): void {}

		/** @param object $resource */
		public function registerResource(string $name, $resource): void {}
		/** @param callable $callback */
		public function registerPlugin(string $type, string $name, $callback): void {}
		/** @param callable $callback */
		public function registerFilter(string $type, $callback): void {}

		public function assign($var, $value = null): void {}
		public function display($tpl): void {}
		public function fetch($tpl): string { return ''; }
		public function clearAllCache(): void {}
		public function clearCompiledTemplate(): void {}
	}

	class Template {
		public function getSmarty(): Smarty { return new Smarty(); }
		/** @return mixed */
		public function getTemplateVars(?string $name = null) { return null; }
		public function renderSubTemplate($tpl, ...$args): string { return ''; }
	}
}

namespace {
	/**
	 * Old short forms that can be found in Docblocks/Properties.
	 * (Global classes, separate from \Smarty\Smarty.)
	 */
	class Smarty extends \Smarty\Smarty {}
	class Smarty_Internal_Template extends \Smarty\Template {}
}

