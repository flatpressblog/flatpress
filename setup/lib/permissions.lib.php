<?php
/**
 * Setup-only filesystem policy.
 */
class FPSetupFilesystem {
	public function windows() {
		return PHP_OS_FAMILY === 'Windows';
	}
	public function readable($path) {
		return is_readable($path);
	}
	public function writable($path) {
		return is_writable($path);
	}
	public function executable($path) {
		return is_executable($path);
	}
	public function mode($path) {
		return function_exists('fileperms') ? @fileperms($path) : false;
	}
	public function chmod($path, $mode) {
		return function_exists('chmod') && @chmod($path, $mode);
	}
	public function directory($path) {
		return is_dir($path);
	}
	public function exists($path) {
		return file_exists($path) || is_link($path);
	}
	public function canonical($path) {
		return realpath($path);
	}
	public function link($path) {
		return is_link($path);
	}
	public function absolute($path) {
		$path = str_replace('\\', '/', $path);
		if ($path === '' || ($path[0] !== '/' && !preg_match('~^[A-Za-z]:/~', $path))) {
			$path = rtrim(ABS_PATH, '/\\') . '/' . $path;
		}
		return rtrim($path, '/');
	}
	public function error($key, $path) {
		global $err, $lang;
		$display = str_replace('\\', '/', $path);
		$root = rtrim(str_replace('\\', '/', ABS_PATH), '/') . '/';
		if (strpos($display, $root) === 0) {
			$display = substr($display, strlen($root));
		}
		// Never disclose profile names, absolute filesystem locations or file contents.
		foreach ([USERS_DIR => 'fp-content/users/', CONFIG_DIR => 'fp-content/config/'] as $private => $label) {
			if (strpos($this->absolute($path) . '/', $this->absolute($private) . '/') === 0) {
				$display = $label;
			}
		}
		if (preg_match('~^(?:[A-Za-z]:/|/)~', $display)) {
			$display = basename($display);
		}
		$format = $lang ['err'] [$key] ?? 'Setup cannot access the required path: %s';
		$err [] = sprintf($format, $display);
		return false;
	}
	public function safe($path) {
		$path = $this->absolute($path);
		$roots = [$this->absolute(ABS_PATH), $this->absolute(FP_CONTENT)];
		foreach ($roots as $root) {
			$compare = $this->windows() ? strtolower($path) : $path;
			$base = $this->windows() ? strtolower($root) : $root;
			if ($compare !== $base && strpos($compare, $base . '/') !== 0) {
				continue;
			}
			$relative = substr($path, strlen($root));
			if (preg_match('~(?:^|/)\.\.(?:/|$)|://~', $relative)) {
				return $this->error('permission_link', $path);
			}
			$canonicalRoot = $this->canonical($root);
			if ($root !== $roots [0] && $this->link($root)) {
				return $this->error('permission_link', $path);
			}
			$cursor = $root;
			foreach (explode('/', trim($relative, '/')) as $part) {
				if ($part === '' || $part === '.') {
					continue;
				}
				$cursor .= '/' . $part;
				if ($this->link($cursor)) {
					return $this->error('permission_link', $path);
				}
				if ($this->exists($cursor) && $canonicalRoot !== false) {
					$expected = str_replace('\\', '/', $canonicalRoot) . substr($cursor, strlen($root));
					$canonical = $this->canonical($cursor);
					if ($canonical === false) {
						return $this->error('permission_read', $path);
					}
					$actual = str_replace('\\', '/', $canonical);
					if (($this->windows() ? strtolower($actual) !== strtolower($expected) : $actual !== $expected)) {
						return $this->error('permission_link', $path);
					}
				}
			}
			return true;
		}
		return $this->error('permission_link', $path);
	}
	public function access($path, $write) {
		clearstatcache(true, $path);
		if (!$this->readable($path) || ($this->directory($path) && !$this->windows() && !$this->executable($path))) {
			return $this->error('permission_read', $path);
		}
		return !$write || $this->writable($path) || $this->error('permission_write', $path);
	}
	public function apply($path, $mode, $write) {
		if (!$this->safe($path)) { return false; }
		$old = $this->mode($path);
		/**
		 * Windows ACLs are not represented by Unix mode bits. chmod can only help
		 * with a read-only file; never make readable distribution files writable.
		 */
		$change = $this->windows() ? ($write && !$this->writable($path)) : ($old === false || ($old & 0777) !== $mode);
		/**
		 * Shared hosts may disable chmod or keep ownership outside the PHP worker.
		 * In that case retain the existing/creation mode and require usable access.
		 */
		if ($change && !$this->chmod($path, $mode)) {
			return $this->access($path, $write);
		}
		clearstatcache(true, $path);
		$actual = $this->mode($path);
		if (!$this->windows() && ($actual === false || ($actual & 0777) !== $mode)) {
			return $this->error('permission_chmod', $path);
		}
		if (!$this->access($path, $write)) {
			// A restrictive mode must not lock the PHP worker out on shared hosting.
			if ($change && $old !== false && !$this->chmod($path, $old & 0777)) {
				$this->error('permission_chmod', $path);
			}
			return false;
		}
		return true;
	}
	public function ensure($path, $mode) {
		$path = $this->absolute($path);
		if (!$this->safe($path)) {
			return false;
		}
		if ($this->exists($path)) {
			if (!$this->directory($path)) { return $this->error('permission_type', $path); }
			return $this->apply($path, $mode, true);
		}
		$parent = dirname($path);
		if (!$this->directory($parent) && !$this->ensure($parent, setup_path_mode($parent, true))) {
			return false;
		}
		if (!@mkdir($path, $mode) && !$this->directory($path)) {
			return $this->error('permission_write', $path);
		}
		return $this->apply($path, $mode, true);
	}
	public function tree($path, $write, $apply, $dirMode, $fileMode) {
		if (!$this->safe($path)) {
			return false;
		}
		if (!$this->exists($path)) {
			return true;
		}
		if (!($apply ? $this->apply($path, $this->directory($path) ? $dirMode : $fileMode, $write) : $this->access($path, $write))) {
			return false;
		}
		if (!$this->directory($path)) {
			return is_file($path) || $this->error('permission_type', $path);
		}
		$entries = @scandir($path);
		if ($entries === false) {
			return $this->error('permission_read', $path);
		}
		$ok = true;
		foreach ($entries as $name) {
			if ($name === '.' || $name === '..') {
				continue;
			}
			$ok = $this->tree(rtrim($path, '/\\') . '/' . $name, $write, $apply, $dirMode, $fileMode) && $ok;
		}
		return $ok;
	}
}

function setup_filesystem() {
	static $filesystem;
	if ($filesystem === null) { $filesystem = new FPSetupFilesystem(); }
	return $filesystem;
}

function setup_path_mode($path, $directory) {
	$fs = setup_filesystem();
	$absolute = $fs->absolute($path);
	foreach ([CONFIG_DIR, USERS_DIR] as $root) {
		$root = $fs->absolute($root);
		if ($absolute === $root || strpos($absolute, $root . '/') === 0) {
			return $directory ? CORE_DIR_PERMISSIONS : CORE_FILE_PERMISSIONS;
		}
	}
	return $directory ? DIR_PERMISSIONS : FILE_PERMISSIONS;
}

/**
 * Check only required data trees; never traverse uploaded media or the root.
 * @phpstan-impure Changes permissions and reports errors; access can change between calls.
 */
function setup_data_preflight($apply = false) {
	$fs = setup_filesystem();
	$ok = $fs->safe(FP_CONTENT);
	if (file_exists(FP_CONTENT)) {
		if (!is_dir(FP_CONTENT)) {
			return $fs->error('permission_type', FP_CONTENT);
		}
		$ok = ($apply ? $fs->apply(FP_CONTENT, DIR_PERMISSIONS, true) : $fs->access(FP_CONTENT, false)) && $ok;
	}
	foreach ([CONFIG_DIR, USERS_DIR, CACHE_DIR, COMPILE_DIR, INDEX_DIR, CONTENT_DIR] as $path) {
		if (file_exists($path) && !is_dir($path)) {
			$ok = $fs->error('permission_type', $path) && $ok;
			continue;
		}
		$ok = $fs->tree($path, $apply, $apply, setup_path_mode($path, true), setup_path_mode($path, false)) && $ok;
	}
	foreach ([CONFIG_FILE, HASHSALT_FILE, CONFIG_DIR . 'plugins.conf.php', CONFIG_DIR . 'widgets.conf.php', SETUPTEMP_FILE, LOCKFILE, FP_CONTENT . '.setup-install.lock'] as $file) {
		if (!file_exists($file) && !is_link($file)) {
			continue;
		}
		if (!$fs->safe($file)) {
			$ok = false;
			continue;
		}
		if (!is_file($file)) {
			$ok = $fs->error('permission_type', $file) && $ok;
		}
	}
	return $ok;
}

/** @phpstan-impure Changes filesystem permissions and reports errors. */
function setup_core_permissions($apply = true) {
	$fs = setup_filesystem();
	$ok = true;
	foreach ([ABS_PATH . 'defaults.php', FP_INCLUDES, LANG_DIR, SHARED_TPLS] as $path) {
		if (!file_exists($path)) {
			$ok = $fs->error('permission_read', $path) && $ok;
			continue;
		}
		$ok = $fs->tree($path, false, $apply, CORE_DIR_PERMISSIONS, CORE_FILE_PERMISSIONS) && $ok;
	}
	return $ok;
}

function setup_render_preflight_errors() {
	global $err;
	http_response_code(503);
	header('Content-Type: text/html; charset=UTF-8');
	header('Cache-Control: no-store');
	echo '<!doctype html><html><meta charset="UTF-8"><title>FlatPress Setup</title><h1>FlatPress Setup</h1><ul>';
	foreach ($err as $message) {
		echo '<li>' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
	}
	echo '</ul></html>';
}

?>
