<?php
/*
 * Theme Name: Leggero
 * Theme URI: http://www.flatpress.org/
 * Description: The default FlatPress theme which brings a breath of fresh mint air.<br><br>This theme comes with <a href="admin.php?p=themes&action=style">different styles</a> - try them :)
 * Version: 0.705
 * Author: NoWhereMan and Drudo
 * Author URI: http://www.flatpress.org/
 */
$theme ['name'] = 'leggero';
$theme ['author'] = 'drudo and NoWhereMan';
$theme ['www'] = 'http://www.flatpress.org/';
$theme ['description'] = 'Default FlatPress Theme. Brings a a breathe of fresh mint air.';

$theme ['version'] = 0.705;

$theme ['style_def'] = 'style.css';
$theme ['style_admin'] = 'admin.css';

$theme ['default_style'] = 'leggero';

// Other theme settings

// overrides default tabmenu
// and panel layout
remove_filter('admin_head', 'admin_head_action');

// register widgetsets
register_widgetset('right');
register_widgetset('left');

?>
