<h2>{$plang.head}</h2>
<p>{$plang.description}</p>

{include file=shared:errorlist.tpl}
{static_block}

<form method="post"
	action="{$smarty.const.BLOG_BASEURL}admin.php?p=entry"
	enctype="multipart/form-data">
	
<table class="entrylist">
<thead>
<tr>
<th>{$plang.id}</th>
<th>{$panelstrings.title}</th>
<th>{$panelstrings.action}</th></tr></thead>
<tbody>
{static}

	{assign var=inarr value=$id|in_array:$enabledlist}
	<tr{if $inarr} class="enabled" {/if}>
		<td> {$id} </td>
		<td class="main-cell"> {$subject} </td>
		<td>
			<a 
			class="link-general" 
			href="{"?p=static"|action_link:write}&amp;page={$id}">
			{$panelstrings.edit}
			</a>
			
			{if $inarr} 
			
			<a class="link-disable"
			href="{$action_url|cmd_link:disable:$id}"> 
				{$panelstrings.disable}
			</a> 
			{else}
			<a class="link-enable" 
			href="{$action_url|cmd_link:enable:$id}"> 
				{$panelstrings.enable}
			</a> 
			{/if}
		</td>
	</tr>

{***	<tr>

<td>
<a href="admin.php?p=static&amp;page={$id}&amp;action=write">{$subject|truncate:70}</a>
</td>
<td>enable/disable</td>

</tr>
***}
{/static}
</tbody></table>
</form>

{/static_block}

