<h2>{$panelstrings.head}</h2>
{include file=shared:errorlist.tpl}
{$panelstrings.list|sprintf:$smarty.const.SYSTEM_VER:$sfweb:$updates.stable:$fpweb:$updates.unstable}
{if $updates.notice}
<h5>{$panelstrings.notice}</h5>
<p>{$updates.notice}</p>
{/if}