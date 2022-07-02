
<h2>{$panelstrings.head}</h2>

{include file='shared:errorlist.tpl'}

<p>{$panelstrings.descr}</p>

{static_block}

{html_form}
	
<table class="entrylist">
<thead><tr>{*<th>{$panelstrings.sel}</th>*}
<th>{$panelstrings.name}</th>
<th class="main-cell">{$panelstrings.title}</th>
<th>{$panelstrings.author}</th>
<th>{$panelstrings.action}</th></tr></thead>
<tbody>
{static}
<tr>
{*<td><input type="checkbox" /></td>*}
<td>{$id}</td>
<td class="main-cell">
<a class="link-general"  
href="{$panel_url|action_link:write}&amp;page={$id}">
{$subject|truncate:70}
</a>
</td>
<td>{$author}</td>
<td>
<a class="link-general" 
href="{$id|link:page_link}">
{$panelstrings.act_view}
</a>
<a 
class="link-general" 
href="{$panel_url|action_link:write}&amp;page={$id}">
{$panelstrings.act_edit}
</a>
<a class="link-delete" 
href="{$panel_url|action_link:delete}&amp;page={$id}">
{$panelstrings.act_del}
</a>
</td>

</tr>

{/static}
</tbody></table>
{/html_form}
{/static_block}


