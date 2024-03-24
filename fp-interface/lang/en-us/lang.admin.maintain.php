<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Maintainance';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Maintenance',
	'descr' => 'Come here when you think something got messed, and maybe here you\'ll find a solution. However this might not work.',
	'opt0' => '&laquo; Back to main menu',
	'opt1' => 'Rebuild index',
	'opt2' => 'Purge theme and templates cache',
	'opt3' => 'Restore file permissions',
	'opt4' => 'Show info about PHP',
	'opt5' => 'Check for updates',

	'chmod_info' => 'If the file permissions <strong>could not</strong> be reset to ' . decoct(FILE_PERMISSIONS) . ', the owner of the file is probably not the same as the owner of the web server.<br>' . //
		'Usually you can ignore this notice.'
);
	
$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Operation completed',
	-1 => 'Operation failed'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Updates',
	'list' => '<ul>
		<li>You have FlatPress version <big>%s</big></li>
		<li>Last stable version for FlatPress is <big><a href="%s">%s</a></big></li>
		<li>Last unstable version for FlatPress is <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Notice:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'There are updates available!',
	2 => 'You are already up-to-date',
	-1 => 'Unable to retrieve updates'
);
?>
