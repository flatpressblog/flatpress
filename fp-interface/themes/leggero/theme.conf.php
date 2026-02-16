<?php
/**
 * Theme Name: Leggero
 * Theme URI: http://www.flatpress.org/
 * Description: The standard theme from FlatPress – a piece of FlatPress history. Various styles from classic to modern: FlatMaas Revisited, Leggero, Leggero v2, and Stringendo – <a href="admin.php?p=themes&action=style">try them out</a> :).
 * Version: 0.705
 * Author: NoWhereMan and Drudo
 * Author URI: http://www.flatpress.org/
 */
$theme ['name'] = 'leggero';
$theme ['author'] = 'NoWhereMan and Drudo';
$theme ['www'] = 'https://www.flatpress.org/';
$theme ['description'] = 'Default FlatPress Theme. Brings a breathe of fresh mint air.';

$theme ['version'] = 0.705;

$theme ['style_def'] = 'style.css';
$theme ['style_admin'] = 'admin.css';

$theme ['default_style'] = 'leggero-v2';

// Other theme settings

// overrides default tabmenu
// and panel layout
remove_filter('admin_head', 'admin_head_action');

// register widgetsets
register_widgetset('right');
register_widgetset('left');
?>
