<?php
$fp_widgets = array (

	/**
	 * to disable put // or # before the plugin name
	 * remove it to enable :)
	 */

	'top' => array (
		0 => 'blockparser:menu'
	),

	/**
	 * Left side widgets. Put here blocks which will appear 
	 * on the left side
	 * (Theme dependant)
	 */

	'left' => array (
		// (no widgets)
	),

	// Right side widgets
	'right' => array (
		0 => 'categories',
		1 => 'archives',
		2 => 'lastentries',
		3 => 'lastcomments',
		4 => 'searchbox',
		5 => 'feed',
		6 => 'newsletter',
		7 => 'mastodon'
	),

	'bottom' => array (
		0 => 'blockparser:bottommenu',
		1 => 'adminarea'
	),
);
?>
