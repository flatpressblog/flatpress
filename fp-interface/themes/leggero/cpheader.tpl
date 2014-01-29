<!DOCTYPE html>
<html>
<head>
	<title>{$flatpress.title}{$pagetitle}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$flatpress.charset}">
	{action hook=wp_head}
	{action hook=admin_head}
</head>

<body class="{"admin-$panel-$action"|tag:admin_body_class}">
	<div id="body-container">
	<div id="outer-container">
	
