#!/usr/bin/env php
<?php
/**
 * Command-line entry point for Mastodon comment-shard diagnostics and repair.
 *
 * Usage:
 *   php fp-plugins/mastodon/mastodon-state-cli.php diagnose
 *   php fp-plugins/mastodon/mastodon-state-cli.php repair
 */
if (PHP_SAPI !== 'cli') {
	header('HTTP/1.1 403 Forbidden');
	echo 'CLI only' . PHP_EOL;
	exit(1);
}

$blogRoot = dirname(dirname(__DIR__));
chdir($blogRoot);

require_once $blogRoot . DIRECTORY_SEPARATOR . 'defaults.php';
require_once $blogRoot . DIRECTORY_SEPARATOR . INCLUDES_DIR . 'includes.php';

if (!function_exists('plugin_mastodon_cli_comment_shard_maintenance')) {
	require_once $blogRoot . DIRECTORY_SEPARATOR . 'fp-plugins' . DIRECTORY_SEPARATOR . 'mastodon' . DIRECTORY_SEPARATOR . 'plugin.mastodon.php';
}

if (!function_exists('plugin_mastodon_cli_comment_shard_maintenance')) {
	echo 'Mastodon plugin could not be loaded.' . PHP_EOL;
	exit(1);
}

exit(plugin_mastodon_cli_comment_shard_maintenance(isset($argv) ? array_slice($argv, 1) : array()));
?>
