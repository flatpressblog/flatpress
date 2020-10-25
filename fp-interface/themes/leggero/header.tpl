<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$fp_config.locale.lang}">
<head>
	<title>{$flatpress.title|tag:wp_title:'&laquo;'}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$flatpress.charset}" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700' rel='stylesheet' type='text/css'/>
	{action hook=wp_head}
</head>

<body>
	<div id="body-container">

		<div id="head">
			<h1><a href="{$smarty.const.BLOG_BASEURL}">{$flatpress.title}</a></h1>
			<p class="subtitle">{$flatpress.subtitle}</p>
		</div> <!-- end of #head -->
	
	<div id="outer-container">
