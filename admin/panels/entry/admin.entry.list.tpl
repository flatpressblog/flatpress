
<h2>{$panelstrings.head}</h2>




{draft_block}
{if isset($draft_list)}
	<div id="admin-drafts">
		<p>{$lang.admin.entry.list.drafts}</p>
		<ul>
		{foreach from=$draft_list key=draftid item=draft}
			<li>
				<a href="admin.php?p=entry&amp;entry={$draftid}&amp;action=write">{$draft|truncate:50}</a>
			</li>
		{/foreach}
		</ul>
	</div>
{/if}
{/draft_block}



{include file="shared:errorlist.tpl"}

<p>{$panelstrings.descr}</p>

<form method="get" action="{$formtarget}?p=entry">
<p> <input type="hidden" name="p" value="entry" /> </p>
<fieldset><legend>{$panelstrings.filter}</legend>
	<select name="category" class="alignleft">
	<option label="{$lang.admin.entry.list.nofilter}" value="all">{$panelstrings.nofilter}</option>
	{*html_options options=$lang.entry.flags.short selected=$smarty.request.cat*}
	{if isset($smarty.request.category)} {assign var=category value=$smarty.request.category} {else} {assign var=category value=""}{/if}
	{html_options options=$categories_all selected=$category}
	</select>
	{html_submit name='filter' id='filter' class="alignright" value=$panelstrings.filterbtn}
</fieldset>

</form>

{entry_block}

<table class="entrylist">
<thead><tr>{*<th>{$panelstrings.sel}</th>*}
<th>{$panelstrings.date}</th>
<th class="main-cell">{$panelstrings.title}</th>
<!-- <th>{$panelstrings.author}</th> -->
<th>{$panelstrings.comms}</th>
<th>{$panelstrings.action}</th></tr></thead>
<tbody>
{entry}
<tr>
<td>{$id|entry_idtotime|date_format:"`$fp_config.locale.dateformatshort`, `$fp_config.locale.timeformat`"}</td>
<td class="main-cell">
{if in_array('draft',$categories)}
(<em class="entry-flag">{$lang.entry.flags.short.draft}</em>)
{/if}
<a class="link-general" 
href="{$panel_url|action_link:write}&amp;entry={$id}">
{$subject|truncate:70} 
</a>
</td>
<!-- <td>{$author}</td> -->
<td><a class="link-general" 
href="{$panel_url|action_link:commentlist}&amp;entry={$id}">
{* Compatibility with pre-0.702 *}
{$commentcount|default:$comments}</a></td>
<td>
<a class="link-general" 
href="{$id|link:post_link}">
{$panelstrings.act_view}
</a>
<a class="link-general" 
href="{$panel_url|action_link:write}&amp;entry={$id}">
{$panelstrings.act_edit}
</a>
<a class="link-delete" 
href="{$panel_url|action_link:delete}&amp;entry={$id}">
{$panelstrings.act_del}
</a>

</td>

</tr>

{/entry}

</tbody></table>

<div class="navigation">
	{prevpage admin=yes}
	{nextpage admin=yes}
</div>
{/entry_block}


