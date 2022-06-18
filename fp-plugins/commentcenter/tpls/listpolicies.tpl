<table id="commencenter-table">
	<thead id="commencenter-table-head">
		<tr>
{if !isset($delete)}			<th style="width: 10%;">{$plang.select}</th>{/if}
			<th class="main-cell">{$plang.criteria}</th>
			<th style="width: 20%;">{$plang.behavoir}</th>
{if !isset($delete)}			<th style="width: 25%;">{$plang.options}</th>{/if}
		</tr>
	</thead>
	<tbody id="commencenter-table-body">
{foreach name=policies from=$policies key=id item=policy}
<tr class="tr_policy{$id}">
{if !isset($delete)}	<td class="td_select"><input type="checkbox" name="select[{$id}]" /></td>{/if}
	<td class="main-cell">
{if isset($delete)}<input type="hidden" name="del_policy[]" value="{$id}" />
{/if}
{if isset($policy.is_all) && $policy.is_all}
		{$plang.all_entries}
{elseif isset($policy.entry) && is_array($policy.entry) && count($policy.entry)>0}
{$plang.fol_entries}
<ul>
{foreach from=$policy.entry item=entry}
<li><span title="{$entry}">{$entry|idToSubject}</span></li>
{/foreach}
</ul>
{elseif !empty($policy.entry)}
{$plang.fol_entries}
<ul>
<li><span title="{$policy.entry}">{$policy.entry|idToSubject}</span></li>
</ul>
{else}
	{if isset($policy.categories) && count($policy.categories)>0}
		<p>{$plang.fol_cats}
		{$policy.categories|@filed}</p>
	{/if}
	{if !empty($policy.older)}
		<p>{$plang.older|sprintf:"`$policy.older/86400`"}</p>
	{/if}
{/if}
	</td>
{if $policy.do==1}
	<td>{$plang.allow}</td>
{elseif $policy.do==0}
	<td>{$plang.approvation}</td>
{elseif $policy.do==-1}
	<td>{$plang.block}</td>
{/if}
{if !isset($delete)}	<td>
<a href="{$action_url|cmd_link:polup:$id}" title="{$plang.up}" rel="polup[{$id}]"><img src="{$plugin_url}imgs/up.png" alt="{$plang.up}" /></a>
<a href="{$action_url|cmd_link:poldown:$id}" title="{$plang.down}" rel="poldown[{$id}]"><img src="{$plugin_url}imgs/down.png" alt="{$plang.down}" /></a>
<a href="{$action_url|cmd_link:poledit:$id}" title="{$plang.edit}"><img src="{$plugin_url}imgs/edit.png" alt="{$plang.edit}" /></a>
<a href="{$action_url|cmd_link:poldelete:$id}" title="{$plang.delete}"><img src="{$plugin_url}imgs/delete.png" alt="{$plang.delete}" /></a>
	</td>{/if}
</tr>
{foreachelse}
<tr>
	<td colspan="{if isset($delete)}2{else}4{/if}">{$plang.nopolicies}</td>
</tr>
{/foreach}
	</tbody>
</table>
