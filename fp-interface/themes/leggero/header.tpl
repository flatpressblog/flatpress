<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{$flatpress.title|tag:wp_title:'&laquo;'}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$flatpress.charset}" />
	{action hook=wp_head}
</head>

<body>
	<div id="body-container">

		<div id="head">
			<h1><a href="{$smarty.const.BLOG_BASEURL}">{$flatpress.title}</a></h1>
			<p class="subtitle">{$flatpress.subtitle}</p>
		</div> <!-- end of #head -->
	
	<div id="outer-container">
