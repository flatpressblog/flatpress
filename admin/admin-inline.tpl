<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{$flatpress.title}{$pagetitle}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$flatpress.CHARSET}" />
	{action hook=wp_head}
</head>

<body id="inline-body">

	{if $submenu}
	<ul id="admin-submenu">
		{foreach from=$submenu key=subtab item=item}
		{if $item}
		<li>
			{if $actionname != $subtab}
			<a href="{$smarty.const.BLOG_BASEURL}admin.php?p={$panelname}&amp;action={$subtab}&mod=inline">
			{$lang.admin[$panel].submenu[$subtab]|default:$subtab}
			</a>
			{else}
			<strong>{$lang.admin[$panel].submenu[$subtab]|default:$subtab}</strong>
			{/if}
			</li>
			{/if}
		{/foreach}
	</ul>
	{/if}
	
	
	{include file=$admin_resource|default:"admin:$panel/$action"}
	
</body>
</html>
