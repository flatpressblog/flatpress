<?php
$lang ['admin'] ['uploader'] ['default'] = array(
	'head' => 'Uploader',
	'descr' => 'Pick one or more file to upload.',
	'fset1' => 'File Picker',
	'fset2' => 'Upload',
	'submit' => 'Upload',
	'uploader_some_failed' => 'This file was not uploaded for security or system reasons:',
	'uploader_metadata_failed' => 'The file was uploaded, but metadata could not be removed:',
	'uploader_drop' => 'Drag files here',
	'uploader_browse_hint' => '...or click to select files',
	'uploader_drop_active' => 'Drop to add',
	'uploader_selected_count' => '%d file(s) selected',
	'uploader_clear' => 'Clear selection',
	'uploader_remove' => 'Remove',
	'uploader_limit_files' => 'Maximum %d files per upload.',
	'uploader_limit_size' => 'Maximum total upload size: %s.'
);

$lang ['admin'] ['uploader'] ['default'] ['msgs'] = array(
	1 => 'File(s) uploaded',
	-1 => 'An error occurred while trying to upload.',
	-2 => 'Upload rejected by the server: the total upload size exceeds post_max_size (%s).',
	-3 => 'Upload rejected by the server, probably due to upload size or file-count limits. No files were received.',
	-4 => 'No files were received. Please select one or more files before uploading.'
);

$lang ['admin'] ['uploader'] ['browse'] = array(
	'head' => 'Browse',
	'descr' => 'Pick one or more file to upload.',
	'fset1' => 'File Picker',
	'submit' => 'Upload'
);
?>
