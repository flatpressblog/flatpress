<h2>{$panelstrings.head}</h2>
{include file="shared:errorlist.tpl"}
{if isset($files)}
	<p>{$panelstrings.chmod_info}</p>
	<p><a href="admin.php?p=maintain">{$panelstrings.opt0}</a></p>
	{if isset($files) && count($files) > 0}
		<h2>{$panelstrings.opt3_error}</h2>
		<ul>
		{foreach from=$files item=file}
			<li>{$file}</li>
		{/foreach}
		</ul>
	{else}
		<p>{$panelstrings.opt3_success}</p>
	{/if}
	<p><a href="admin.php?p=maintain">{$panelstrings.opt0}</a></p>
{elseif isset($phpinfo)}
	<p><a href="admin.php?p=maintain">{$panelstrings.opt0}</a></p>
	{$phpinfo}
	<p><a href="admin.php?p=maintain">{$panelstrings.opt0}</a></p>
{else}
	<p>{$panelstrings.descr}</p>
	<ul>
	<li><a href="{$action_url|cmd_link:do:rebuild}">{$panelstrings.opt1}</a></li>
	<li><a href="{$action_url|cmd_link:do:purgetplcache}">{$panelstrings.opt2}</a></li>
	<li><a href="{$action_url|cmd_link:do:restorechmods}">{$panelstrings.opt3}</a></li>
	<li><a href="{$action_url|cmd_link:do:phpinfo}">{$panelstrings.opt4}</a></li>
	<li><a href="{$panel_url|action_link:updates}">{$panelstrings.opt5}</a></li>
	</ul>
{/if}
