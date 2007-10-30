{if $smarty.request.do.logout}
<p>{$lang.login.success.logout}</p>
{if $smarty.request.redirect}
<p>{$lang.login.success.redirect} <a href="{$smarty.request.redirect}">{$smarty.request.redirect}</a>
{/if}

<ul>
	<li><a href="index.php">{$lang.login.success.opt1}</a></li>
</ul>
{else}
<p>{$lang.login.success.success}</p>
{if $redirect}
<p>{$lang.login.success.redirect}
{/if}

<ul>
	<li><a href="{$smarty.const.BLOG_BASEURL}">
		{$lang.login.success.opt1}
	</a></li>
	<li><a href="{$smarty.const.BLOG_BASEURL}admin.php?p=main">
		{$lang.login.success.opt2}
	</a></li>
	<li><a href="{$smarty.const.BLOG_BASEURL}admin.php?p=entry&amp;action=write">
		{$lang.login.success.opt3}
	</a></li>
</ul>
{/if}
