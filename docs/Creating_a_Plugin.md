# Creating a FlatPress Plugin

This tutorial explains the basic steps for building a FlatPress plugin. It is based on the current FlatPress 1.5.1 Stringendo and uses small examples that are easy to copy into a local test installation.

The goal is to understand four common plugin tasks:

- creating the plugin folder and main plugin file
- using actions and filters
- adding a new BBCode element
- using Smarty templates and an admin panel for plugin settings

## Requirements

Before you start, you need:

- a working FlatPress installation
- access to the `fp-plugins/` directory
- basic PHP knowledge
- the BBCode plugin enabled if you want to add BBCode elements

FlatPress plugins should be written conservatively so they work on different web hosts. For broad compatibility, avoid PHP features that are newer than the minimum PHP version supported by your FlatPress target. If your FlatPress branch supports PHP 7.2 and newer, avoid features such as typed properties, union types, the nullsafe operator, and `match`.

## 1. Plugin Structure

A FlatPress plugin lives in its own directory below `fp-plugins/`. The directory name is the plugin ID. The main plugin file must be named `plugin.PLUGINID.php`.

For this tutorial we create a plugin named `hellonote`:

```text
fp-plugins/
  hellonote/
    plugin.hellonote.php
    lang/
      lang.en-us.php
    panels/
      admin.plugin.panel.hellonote.php
    tpls/
      admin.plugin.hellonote.tpl
```

Only the main plugin file is required. The `lang/`, `panels/`, and `tpls/` directories are added when your plugin needs translations, an admin panel, or Smarty templates.

## 2. The Main Plugin File

Create `fp-plugins/hellonote/plugin.hellonote.php`:

```php
<?php
/**
 * Plugin Name: Hello Note
 * Plugin URI: https://www.flatpress.org
 * Author: Your Name
 * Author URI: https://www.example.com
 * Description: Adds a small configurable note to the blog footer.
 * Version: 1.0
 */

add_action('wp_footer', 'plugin_hellonote_footer');

if (class_exists('AdminPanelAction')) {
	require_once plugin_getdir('hellonote') . 'panels/admin.plugin.panel.hellonote.php';
}

function plugin_hellonote_footer() {
	$options = plugin_getoptions('hellonote');
	$message = '';

	if (is_array($options) && isset($options ['message'])) {
		$message = trim((string) $options ['message']);
	}

	if ($message === '') {
		return;
	}

	echo '<p class="hellonote">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
}
```

The header comment is used by FlatPress to show plugin information in the admin area. Keep the metadata simple and do not place executable code before it.

The function names use the prefix `plugin_hellonote_`. This avoids collisions with FlatPress core functions, themes, and other plugins.

## 3. Actions and Filters

FlatPress uses a WordPress-style hook system.

An action runs code at a specific point:

```php
add_action('wp_footer', 'plugin_hellonote_footer');
```

A filter receives a value, changes it, and returns it:

```php
add_filter('the_content', 'plugin_hellonote_append_text');

function plugin_hellonote_append_text($content) {
	return $content . "\n" . '<p>Thank you for reading.</p>';
}
```

Use actions when you want to output something or react to an event. Use filters when you want to transform data such as entry content, comments, titles, or generated links.

Common hooks include:

- `init` - after FlatPress has loaded
- `wp_head` - inside the public page `<head>`
- `wp_footer` - near the end of the public page
- `the_content` - entry content before it is printed
- `comment_form` - inside the comment form
- `comment_validate` - during comment validation

## 4. Loading Plugin Assets

Use `plugin_geturl()` for public URLs and `plugin_getdir()` for filesystem paths.

Example for a stylesheet:

```php
add_action('wp_head', 'plugin_hellonote_head');

function plugin_hellonote_head() {
	$url = plugin_geturl('hellonote') . 'res/hellonote.css';
	echo '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
}
```

If your plugin includes PHP files, load them with `plugin_getdir()`:

```php
require_once plugin_getdir('hellonote') . 'inc/helper.php';
```

## 5. Translations

FlatPress plugins can be multilingual. Add language files below `lang/`. The English file for this tutorial is `fp-plugins/hellonote/lang/lang.en-us.php`:

```php
<?php
$lang ['admin'] ['plugin'] ['submenu'] ['hellonote'] = 'Hello Note';

$lang ['admin'] ['plugin'] ['hellonote'] = array(
	'head' => 'Hello Note configuration',
	'description' => 'Show a small note in the blog footer.',
	'message' => 'Footer note',
	'submit' => 'Save configuration',
	'msgs' => array(
		1 => 'Hello Note configuration saved.',
		-1 => 'Hello Note configuration was not saved.'
	)
);
```

Load strings in PHP with:

```php
$lang = lang_load('plugin:hellonote');
```

FlatPress also loads plugin language files when plugins are loaded, so admin panels usually access the translated strings through `$plang` in Smarty templates.

## 6. A Simple Admin Panel

Admin panels let users configure plugin options from the FlatPress admin area.

Create `fp-plugins/hellonote/panels/admin.plugin.panel.hellonote.php`:

```php
<?php

if (class_exists('AdminPanelAction')) {
	class admin_plugin_hellonote extends AdminPanelAction {
		var $langres = 'plugin:hellonote';

		function setup() {
			$this->smarty->assign('admin_resource', 'plugin:hellonote/admin.plugin.hellonote');
		}

		function main() {
			$options = plugin_getoptions('hellonote');
			$message = '';

			if (is_array($options) && isset($options['message'])) {
				$message = (string) $options['message'];
			}

			$this->smarty->assign('message', $message);
		}

		function onsubmit($data = null) {
			if (isset($_POST['hellonote-submit'])) {
				$message = isset($_POST ['hellonote-message']) ? trim((string) $_POST ['hellonote-message']) : '';

				plugin_addoption('hellonote', 'message', $message);
				plugin_saveoptions('hellonote');
				$this->smarty->assign('success', 1);
			} else {
				$this->smarty->assign('success', -1);
			}

			return 2;
		}
	}

	admin_addpanelaction('plugin', 'hellonote', true);
}
```

Then create `fp-plugins/hellonote/tpls/admin.plugin.hellonote.tpl`:

```smarty
<h2>{$plang.head}</h2>
<p>{$plang.description}</p>

{include file="shared:errorlist.tpl"}

{html_form class="option-set"}
<dl class="option-list">
	<dt><label for="hellonote-message">{$plang.message}</label></dt>
	<dd>
		<input type="text" name="hellonote-message" id="hellonote-message" value="{$message|escape}" size="60">
	</dd>
</dl>

<p class="buttonbar">
	<input type="submit" name="hellonote-submit" value="{$plang.submit}">
</p>
{/html_form}
```

Important details:

- the class name is `admin_plugin_hellonote`
- the panel is registered with `admin_addpanelaction('plugin', 'hellonote', true)`
- `admin_resource` points to `plugin:hellonote/admin.plugin.hellonote`
- the template file is stored in the plugin's `tpls/` directory
- dynamic output in Smarty should be escaped with `|escape`

## 7. Adding a New BBCode Element

The BBCode plugin exposes a `bbcode_init` filter. Other plugins can use it to add custom tags.

This example adds:

```text
[note]This is a note.[/note]
[note=warning]Be careful.[/note]
```

Add this code to `plugin.hellonote.php`:

```php
add_filter('bbcode_init', 'plugin_hellonote_bbcode', 10, 1);

function plugin_hellonote_bbcode($bbcode) {
	$bbcode->addCode(
		'note',
		'callback_replace',
		'plugin_hellonote_render_note',
		array('usecontent_param' => array('default')),
		'block',
		array('block'),
		array()
	);
	$bbcode->setCodeFlag('note', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);

	return $bbcode;
}

function plugin_hellonote_render_note($action, $attributes, $content, $params, $node_object) {
	if ($action === 'validate') {
		return true;
	}

	$type = 'info';
	if (isset($attributes['default'])) {
		$type = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $attributes ['default']);
	}

	if ($type === '') {
		$type = 'info';
	}

	return '<aside class="hellonote hellonote-' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '">' .
		htmlspecialchars((string) $content, ENT_QUOTES, 'UTF-8') .
		'</aside>';
}
```

For simple paired tags, use `callback_replace` and `BBCODE_CLOSETAG_MUSTEXIST`. For single tags without content, look at existing FlatPress plugins such as `bbcode` and `audiovideo` for `callback_replace_single` examples.

Always escape user-provided BBCode content unless your plugin intentionally supports a controlled subset of HTML.

## 8. Using Smarty Outside the Admin Panel

For public output, simple plugins often echo small HTML fragments from an action hook. If your output grows, replace the earlier footer function with a template-based version.

A common pattern is:

```php
function plugin_hellonote_footer() {
	global $smarty;

	$options = plugin_getoptions('hellonote');
	$message = is_array($options) && isset($options ['message']) ? (string) $options ['message'] : '';

	if (trim($message) === '') {
		return;
	}

	$smarty->assign('hellonote_message', $message);
	$smarty->display('plugin:hellonote/footer');
}
```

Then create `fp-plugins/hellonote/tpls/footer.tpl`:

```smarty
<p class="hellonote">{$hellonote_message|escape}</p>
```

Use templates when markup becomes larger than a few lines or when theme designers may want to override or inspect it more easily.

## 9. Security and Compatibility Checklist

Before you release a plugin, check the following:

- Escape HTML output with `htmlspecialchars()` or Smarty's `|escape`.
- Validate and cast values from `$_POST`, `$_GET`, and BBCode attributes.
- Do not trust uploaded file names or remote URLs.
- Use `plugin_getdir()` for filesystem paths and `plugin_geturl()` for browser URLs.
- Prefix all functions, classes, constants, CSS classes, and form fields with your plugin ID.
- Keep language strings in `lang/lang.LOCALE.php` files.
- Keep admin templates in `tpls/` and admin panel classes in `panels/`.
- Avoid new PHP syntax if the plugin must support older PHP versions.
- Check optional PHP extensions with `function_exists()` or `class_exists()` before using them.
- Test with the plugin enabled and disabled.

## 10. Testing the Plugin

After copying the files:

1. Log in to the FlatPress admin area.
2. Open the plugin panel and enable `Hello Note`.
3. Open the plugin configuration panel.
4. Save a short footer note.
5. Open the public blog and check the footer.
6. Create an entry containing `[note]This is a note.[/note]`.
7. Check that the note is rendered and that no raw BBCode remains.

For code quality, run PHP linting on your plugin files:

```bash
php -l fp-plugins/hellonote/plugin.hellonote.php
php -l fp-plugins/hellonote/panels/admin.plugin.panel.hellonote.php
```

If your FlatPress branch includes PHPStan, analyze your plugin with the project's PHPStan configuration.

## 11. Troubleshooting

### The plugin does not appear in the admin area

Check that the folder and main file names match:

```text
fp-plugins/hellonote/plugin.hellonote.php
```

The plugin ID, folder name, and main file name must use the same ID.

### The admin panel is empty

Check these three names:

- class name: `admin_plugin_hellonote`
- admin resource: `plugin:hellonote/admin.plugin.hellonote`
- template file: `fp-plugins/hellonote/tpls/admin.plugin.hellonote.tpl`

### Translations are missing

Check that your language file uses the correct array path:

```php
$lang ['admin'] ['plugin'] ['hellonote'] = array(
	'head' => 'Hello Note configuration'
);
```

Also check that the admin panel has:

```php
var $langres = 'plugin:hellonote';
```

### The BBCode tag is not rendered

Make sure the BBCode plugin is enabled. Then check that your plugin registers the `bbcode_init` filter and returns the `$bbcode` object.

## 12. Existing Plugins Worth Reading

The FlatPress already contains useful examples:

- `fp-plugins/accessibleantispam/` - simple actions, comment form integration, translations
- `fp-plugins/bbcode/` - BBCode parser integration, assets, admin panel, templates
- `fp-plugins/thumb/` - file handling and image-related output

Reading small plugins first is often easier than starting with a large plugin. Once the basic structure is clear, the larger plugins show how FlatPress handles more advanced cases.

## Summary

A FlatPress plugin is usually built from a small set of predictable pieces: a plugin directory, a main `plugin.PLUGINID.php` file, optional language files, optional templates, and optional admin panels. Actions and filters connect your plugin to FlatPress. The BBCode plugin can be extended through `bbcode_init`. Keep the code conservative, escaped, translated, and easy to test.
