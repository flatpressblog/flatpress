<h2>{$panelstrings.head}</h2>
{include file="shared:errorlist.tpl"}
{$panelstrings.list|sprintf:$smarty.const.SYSTEM_VER:$sfweb:$stableversion:$fpweb:$unstableversion}
{if $notice}
<h5>{$panelstrings.notice}</h5>
<p>{$notice}</p>
{/if}
{if $success == -1 && $update_diagnostic}
<p><code>{$update_diagnostic|escape:'html'}</code></p>
{/if}
