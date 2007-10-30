
<h2>{$panelstrings.head}</h2>



{draft_block}
<div id="admin-drafts">
<p>Your drafts:</p>
<ul>

{draft}
<li>
<a href="admin.php?p=entry&amp;entry={$id}&amp;action=write">{$subject|truncate:70}</a>

</li>
{/draft}

</ul>
</div>

{/draft_block}



{include file=shared:errorlist.tpl}

<p>{$panelstrings.descr}</p>

{html_form}


<fieldset><legend>{$panelstrings.filter}</legend>
	<select name="category" class="alignleft">
	<option label="Unfiltered" value="all">{$panelstrings.nofilter}</option>
	{*html_options options=$lang.entry.flags.short selected=$smarty.request.cat*}
	{html_options options=$categories_all selected=$smarty.request.category}
	</select>
	{html_submit name='filter' id='filter' class="alignright" value=$panelstrings.filterbtn}
</fieldset>


{entry_block}

<table class="entrylist">
<thead><tr>{*<th>{$panelstrings.sel}</th>*}
<th>{$panelstrings.date}</th>
<th class="main-cell">{$panelstrings.title}</th>
<th>{$panelstrings.author}</th>
<th>{$panelstrings.comms}</th>
<th>{$panelstrings.action}</th></tr></thead>
<tbody>
{entry}
<tr>
<td>{$date|date_format:"%D, %T"}</td>
<td class="main-cell">
{if in_array('draft',$categories)}
(<em class="entry-flag">{$lang.entry.flags.short.draft}</em>)
{/if}
{$subject|truncate:70} 
</td>
<td>{$author}</td>
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


{/html_form}