
<h2>{$panelstrings.head}</h2>
<p>{$panelstrings.descr}</p>

{include file='shared:errorlist.tpl'}

{html_form}
	

	<table id="plugin-table">
	<thead id="plugin-table-head">
		<tr>
			<th>{$panelstrings.name}</th>
			<th class="main-cell">{$panelstrings.description}</th>
			<th>{$panelstrings.author}</th>
			<th>{$panelstrings.version}</th>
			<th>{$panelstrings.action}</th>
		</tr>
	</thead>
	<tbody id="plugin-table-body">
	{foreach from=$pluginlist item=plugin}
	{assign var=inarr value=$plugin|in_array:$enabledlist}
	{$plugin|plugin_getinfo}
	<tr{if $inarr} class="enabled" {/if}>
		<td> {$name} </td>
		<td class="main-cell"> {$description} </td>
		<td> {$author} </td>
		<td> {$version} </td>
		<td> {if $inarr} 
			<a class="link-disable"
			href="{$action_url|cmd_link:disable:$plugin}"> 
				{$panelstrings.disable}
			</a> 
			{else}
			<a class="link-enable" 
			href="{$action_url|cmd_link:enable:$plugin}"> 
				{$panelstrings.enable}
			</a> 
			{/if}
		</td>
	</tr>
	{/foreach}
	</tbody>
	</table>
	
{/html_form}
