<?php
/**
 * Compare ReadMore output between an unmodified FlatPress tree and the patched tree.
 *
 * Usage:
 *   php compare_readmore_behavior.php /path/to/original /path/to/patched
 *
 * The script starts isolated PHP child processes because PLUGIN_READMORE_MODE
 * is a constant and must be set independently for every test case.
 */

error_reporting(E_ALL);

if (PHP_SAPI !== 'cli') {
	fwrite(STDERR, "CLI only.\n");
	exit(2);
}

$originalRoot = isset($argv [1]) ? rtrim(str_replace('\\', '/', $argv [1]), '/') : '';
$patchedRoot = isset($argv [2]) ? rtrim(str_replace('\\', '/', $argv [2]), '/') : '';

$originalPlugin = $originalRoot . '/fp-plugins/readmore/plugin.readmore.php';
$patchedPlugin = $patchedRoot . '/fp-plugins/readmore/plugin.readmore.php';

if (!is_file($originalPlugin) || !is_file($patchedPlugin)) {
	fwrite(STDERR, "Both FlatPress roots must contain fp-plugins/readmore/plugin.readmore.php.\n");
	exit(2);
}

$childFile = tempnam(sys_get_temp_dir(), 'fp-readmore-child-');
if ($childFile === false) {
	fwrite(STDERR, "Cannot create temporary child script.\n");
	exit(2);
}

$childCode = <<<'PHP'
<?php
$plugin = $argv[1];
$mode = $argv[2];
$context = $argv[3];
$input = base64_decode($argv[4]);

define('PLUGIN_READMORE_MODE', $mode);

function add_filter($tag, $callback, $priority = 10, $acceptedArgs = 1) {
	return true;
}
function lang_load($id) {
	return array(
		'plugin' => array(
			'readmore' => array(
				'readmore' => 'Read more'
			)
		)
	);
}
function get_comments_link($id) {
	return 'https://example.test/?entry=' . rawurlencode((string) $id);
}
class RegressionReadMoreQuery {
	var $single = false;
	function __construct($single) {
		$this->single = (bool) $single;
	}
	function getLastEntry() {
		return array('entry-id', array());
	}
}
class RegressionReadMoreDB {
	var $query;
	function __construct($query) {
		$this->query = $query;
	}
	function &getQuery() {
		return $this->query;
	}
}

$single = ($context === 'single');
$query = new RegressionReadMoreQuery($single);
$fpdb = new RegressionReadMoreDB($query);
$GLOBALS ['fpdb'] = $fpdb;
$GLOBALS ['fp_params'] = $single ? array('entry' => 'entry-id') : array();
$_GET = $context === 'stream_page_param' ? array('page' => 'static-id') : array();

require $plugin;
echo base64_encode(plugin_readmore_main($input));
PHP;

file_put_contents($childFile, $childCode);

$cases = array(
	'plain_short' => 'abc',
	'plain_long' => 'abcdef',
	'manual_marker' => 'AA[more]BB',
	'sentences' => 'One. Two. Three. Four. Five. Six.',
	'image_like_html' => 'Intro <img src="x"> [more] tail'
);
$modes = array('manual', 'auto', 'semiauto', 'sentence', 'invalid-mode');
$contexts = array('stream', 'single', 'stream_page_param');

$results = array();
$failed = 0;

foreach ($modes as $mode) {
	foreach ($contexts as $context) {
		foreach ($cases as $name => $input) {
			$outputs = array();
			foreach (array('original' => $originalPlugin, 'patched' => $patchedPlugin) as $side => $plugin) {
				$command = escapeshellarg(PHP_BINARY)
					. ' '
					. escapeshellarg($childFile)
					. ' '
					. escapeshellarg($plugin)
					. ' '
					. escapeshellarg($mode)
					. ' '
					. escapeshellarg($context)
					. ' '
					. escapeshellarg(base64_encode($input));

				$lines = array();
				$status = 0;
				exec($command, $lines, $status);
				$outputs [$side] = array(
					'status' => $status,
					'output' => $status === 0 ? base64_decode(implode("\n", $lines)) : ''
				);
			}

			$equal = $outputs ['original'] ['status'] === 0
				&& $outputs ['patched'] ['status'] === 0
				&& $outputs ['original'] ['output'] === $outputs ['patched'] ['output'];

			if (!$equal) {
				$failed++;
			}

			$results [] = array(
				'mode' => $mode,
				'context' => $context,
				'case' => $name,
				'status' => $equal ? 'PASS' : 'FAIL'
			);
		}
	}
}

@unlink($childFile);

$summary = array(
	'php_version' => PHP_VERSION,
	'total' => count($results),
	'passed' => count($results) - $failed,
	'failed' => $failed,
	'results' => $results
);

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit($failed === 0 ? 0 : 1);
?>
