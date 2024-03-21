<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$fp_config.locale.lang}">
<head>
	<title>{$flatpress.title}{$pagetitle}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$fp_config.locale.charset}">
	{action hook=wp_head}
	{action hook=admin_head}
</head>

{if !isset($panel)} {assign var=panel value=""} {/if}
{if !isset($action)} {assign var=action value=""} {/if}
<body class="{"admin-$panel-$action"|tag:admin_body_class}">
	<div id="body-container">
	<div id="outer-container">
	
