<?php
require_once 'defaults.php';

/**
 * After successful installation, hide the setup entry points in the FlatPress root.
 *
 * This reduces accidental exposure of the setup UI on production systems.
 * If renaming fails (e.g. insufficient permissions), admin.php will continue
 * to work normally.
 */
function fp_admin_get_restricted_file_perm(): int {
	return defined('RESTRICTED_FILE_PERMISSIONS') ? (int) RESTRICTED_FILE_PERMISSIONS : 0644;
}

function fp_admin_get_restricted_dir_perm(): int {
	return defined('RESTRICTED_DIR_PERMISSIONS') ? (int) RESTRICTED_DIR_PERMISSIONS : 0755;
}

function fp_admin_apply_restricted_file_permissions(string $path): void {
	if (is_file($path)) {
		@chmod($path, fp_admin_get_restricted_file_perm());
	}
}

function fp_admin_apply_restricted_dir_permissions(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}

	$dirPerm = fp_admin_get_restricted_dir_perm();
	$filePerm = fp_admin_get_restricted_file_perm();

	@chmod($dir, $dirPerm);

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ($iterator as $item) {
		// Never follow symlinks.
		if ($item->isLink()) {
			continue;
		}
		if ($item->isDir()) {
			@chmod($item->getPathname(), $dirPerm);
		} elseif ($item->isFile()) {
			@chmod($item->getPathname(), $filePerm);
		}
	}
}

function fp_admin_move_file(string $src, string $dst): bool {
	// Preferred: atomic rename (fast; works on same filesystem)
	if (@rename($src, $dst)) {
		fp_admin_apply_restricted_file_permissions($dst);
		return true;
	}

	// Fallback for cross-filesystem moves or platforms where rename() is unreliable.
	if (file_exists($dst)) {
		return false;
	}
	$tmp = $dst . '.tmp.' . str_replace('.', '', uniqid('', true));
	if (!@copy($src, $tmp)) {
		@unlink($tmp);
		return false;
	}
	if (!is_file($tmp)) {
		@unlink($tmp);
		return false;
	}
	// Promote temp file into place (atomic within the same directory).
	if (!@rename($tmp, $dst)) {
		@unlink($tmp);
		return false;
	}
	fp_admin_apply_restricted_file_permissions($dst);
	@unlink($src);
	return !file_exists($src);
}

function fp_admin_copy_dir_recursive(string $srcDir, string $dstDir): bool {
	$dirPerm = fp_admin_get_restricted_dir_perm();
	$filePerm = fp_admin_get_restricted_file_perm();

	if (!is_dir($dstDir)) {
		// Create destination (best effort; permissions may prevent this)
		if (!@mkdir($dstDir, $dirPerm, true) && !is_dir($dstDir)) {
			return false;
		}
		@chmod($dstDir, $dirPerm);
	}

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ($iterator as $item) {
		$subPath = $iterator->getSubPathName();
		$target = rtrim($dstDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $subPath;

		if ($item->isDir()) {
			if (!is_dir($target) && !@mkdir($target, $dirPerm, true) && !is_dir($target)) {
				return false;
			}
			@chmod($target, $dirPerm);
			continue;
		}

		if (!@copy($item->getPathname(), $target)) {
			return false;
		}
		@chmod($target, $filePerm);
	}

	return true;
}

function fp_admin_rmdir_recursive(string $dir): bool {
	if (!is_dir($dir)) {
		return true;
	}

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);

	foreach ($iterator as $item) {
		$path = $item->getPathname();
		if ($item->isDir()) {
			@rmdir($path);
		} else {
			@unlink($path);
		}
	}

	return @rmdir($dir);
}

function fp_admin_move_dir(string $src, string $dst): bool {
	// Preferred: atomic rename (fast; works on same filesystem)
	if (@rename($src, $dst)) {
		fp_admin_apply_restricted_dir_permissions($dst);
		return true;
	}

	// Fallback for cross-filesystem moves or platforms where rename() is unreliable.
	if (file_exists($dst)) {
		return false;
	}
	$tmp = $dst . '.tmp.' . str_replace('.', '', uniqid('', true));
	if (file_exists($tmp)) {
		return false;
	}
	if (!fp_admin_copy_dir_recursive($src, $tmp)) {
		fp_admin_rmdir_recursive($tmp);
		return false;
	}
	// Promote temp directory into place (atomic within the same parent directory).
	if (!@rename($tmp, $dst)) {
		fp_admin_rmdir_recursive($tmp);
		return false;
	}
	fp_admin_apply_restricted_dir_permissions($dst);
	fp_admin_rmdir_recursive($src);
	return is_dir($dst) && !is_dir($src);
}

function fp_admin_hide_setup_after_install(): void {
	// Only after setup has completed (lockfile exists)
	if (!defined('LOCKFILE')) {
		return;
	}

	$root = rtrim(str_replace('\\', '/', __DIR__), '/') . '/';
	$lockfile = $root . LOCKFILE;

	if (!is_file($lockfile)) {
		return;
	}

	$setupPhp = $root . 'setup.php';
	$setupPhpHidden = $root . '.setup.php';
	$setupDir = $root . 'setup';
	$setupDirHidden = $root . '.setup';

	// Hide setup.php
	if (is_file($setupPhp) && !file_exists($setupPhpHidden)) {
		fp_admin_move_file($setupPhp, $setupPhpHidden);
	}
	// Ensure restricted permissions even if it already existed.
	fp_admin_apply_restricted_file_permissions($setupPhpHidden);

	// Hide setup directory
	if (is_dir($setupDir) && !file_exists($setupDirHidden)) {
		fp_admin_move_dir($setupDir, $setupDirHidden);
	}
	// Ensure restricted permissions even if it already existed.
	fp_admin_apply_restricted_dir_permissions($setupDirHidden);

	// Report problems for an admin UI warning.
	$errors = array();
	if (is_file($setupPhp)) {
		$errors [] = 'setup.php';
	}
	if (is_dir($setupDir)) {
		$errors [] = 'setup/';
	}
	if (!empty($errors)) {
		$GLOBALS ['fp_setup_hide_report'] = array(
			'errors' => $errors
		);
	}

}

fp_admin_hide_setup_after_install();

require_once INCLUDES_DIR . 'includes.php';

require ADMIN_DIR . '/main.php'
?>
