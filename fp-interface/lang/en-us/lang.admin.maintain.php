<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Maintainance';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Maintenance',
	'descr' => 'Come here when you think something got messed, and maybe here you\'ll find a solution. However this might not work.',
	'opt0' => '&laquo; Back to main menu',
	'opt1' => 'Rebuild index',
	'opt2' => 'Purge theme and templates cache',
	'opt3' => 'Restore authorizations for productive operation',
	'opt4' => 'Show info about PHP',
	'opt5' => 'Check for updates',
	'opt6' => 'APCu cache status',

	'chmod_info' => 'If the permissions <strong>could not</strong> be reset, the owner of the file/directory is probably not the same as the owner of the web server.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>Permissions</th>
					<th>' . FP_CONTENT . '</th>
					<th>Core</th>
					<th>All other</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Files</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>Directories</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'All authorizations have been successfully updated.',
	'opt3_error' => 'Error when setting the authorizations:'
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

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'APCu cache',
	'descr' => 'Overview of APCu shared memory usage and cache efficiency.',
	'status_heading' => 'Heuristic status',
	'status_good' => 'Cache seems well sized for the current workload.',
	'status_bad' => 'High miss rate or very low free memory: APCu cache might be too small or heavily fragmented.',
	'hit_rate' => 'Hit rate',
	'free_mem' => 'Free memory',
	'total_mem' => 'Total shared memory',
	'used_mem' => 'Used memory',
	'avail_mem' => 'Available memory',
	'memory_type' => 'Memory type',
	'memory_type_unknown' => 'n/a',
	'num_slots' => 'Number of slots',
	'num_hits' => 'Number of hits',
	'num_misses' => 'Number of misses',
	'cache_type' => 'Cache type',
	'cache_user_only' => 'User data cache.',
	'legend_good' => 'Green: configuration looks healthy (high hit rate, reasonable free memory).',
	'legend_bad' => 'Red: cache under pressure (many misses or almost no free memory).',
	'no_apcu' => 'APCu does not appear to be enabled on this server.',
	'back' => '&laquo; Back to maintenance',
	'clear_fp_button'=> 'Clear FlatPress APCu entries',
	'clear_fp_confirm' => 'Do you really want to delete all APCu entries? This will clear FlatPress\' APCu caches.',
	'clear_fp_result'=> 'Deleted %d APCu entries.',
	'msgs' => array(
		1  => 'FlatPress APCu entries have been cleared.',
		2  => 'No APCu entries were found.',
		-1 => 'APCu is not available or could not be accessed; nothing was deleted.'
	)
);
?>
