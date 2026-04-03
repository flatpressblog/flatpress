<!DOCTYPE html>
{$lang_parts = $fp_config.locale.lang|split:'-'}
<html lang="{$lang_parts[0]|lower}-{$lang_parts[1]|upper}"{if function_exists('plugin_seometataginfo_head')} prefix="og: https://ogp.me/ns#"{else} xmlns="http://www.w3.org/1999/xhtml"{/if}>
<head>
	<title>{$flatpress.title|tag:wp_title:'&laquo;'}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$fp_config.locale.charset}">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	{action hook=wp_head}
</head>

<body>
	<div id="body-container">

		<div id="head">
			<h1><a href="{$smarty.const.BLOG_BASEURL}">{$flatpress.title}</a></h1>
			<p class="subtitle">{$flatpress.subtitle}</p>
		</div> <!-- end of #head -->

	<div id="outer-container">
