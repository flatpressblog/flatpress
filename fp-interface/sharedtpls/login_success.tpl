{if isset($smarty.request.do) && $smarty.request.do.logout}
<p>{$lang.login.success.logout}</p>
{if isset($smarty.request.redirect)}
<p>{$lang.login.success.redirect} <a href="{$smarty.request.redirect}">{$smarty.request.redirect}</a>
{/if}

<ul>
	<li><a href="index.php">{$lang.login.success.opt1}</a></li>
</ul>
{else}
<p class="text-center">Redirecting...</p>
{if isset($redirect)}
<p>{$lang.login.success.redirect}
{/if}

{/if}
