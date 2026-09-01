<?php
header('Content-Type: text/plain; charset=UTF-8');

$values = [
	'HTTPS',
	'REQUEST_SCHEME',
	'SERVER_PORT',
	'SERVER_NAME',
	'HTTP_X_FORWARDED_PROTO',
	'HTTP_X_FORWARDED_PORT',
	'HTTP_X_FORWARDED_SSL',
	'HTTP_X_FORWARDED_SCHEME',
	'HTTP_FORWARDED'
];

foreach ($values as $key) {
	echo $key . ' = ' . ($_SERVER[$key] ?? '<unset>') . PHP_EOL;
}

echo 'HTTP_VIA present = ' . (isset($_SERVER['HTTP_VIA']) ? 'yes' : 'no') . PHP_EOL;

echo 'HTTP_X_FORWARDED_FOR present = ' . (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? 'yes' : 'no') . PHP_EOL;

$keys = [
	'REQUEST_URI',
	'SCRIPT_NAME',
	'PHP_SELF',
	'PATH_INFO',
	'ORIG_PATH_INFO',
	'REDIRECT_URL',
	'REDIRECT_STATUS',
	'SERVER_SOFTWARE',
	'DOCUMENT_ROOT',
	'REMOTE_ADDR'
];

foreach ($keys as $key) {
	echo $key . ' = ' . ($_SERVER[$key] ?? '<unset>') . PHP_EOL;
}
?>
