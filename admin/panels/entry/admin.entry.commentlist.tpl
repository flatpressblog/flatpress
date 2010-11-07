
{entry_block}
{entry}

<h2>{$panelstrings.head} <a href="admin.php?p=entry&amp;action=write&amp;entry={$id}">{$subject}</a></h2>
{include file=shared:errorlist.tpl}

<p>{$panelstrings.descr}</p>



{comment_block}

{html_form}

<script type="text/javascript">
{literal}

function admin_entry_comment_delete() { return confirm({/literal}'{$plang.act_del_confirm}'{literal}); }

{/literal}
</script>
<table class="entrylist">
<thead><tr>
<th>{$panelstrings.date}</th>
<th>{$panelstrings.content}</th>
<th>{$panelstrings.author}</th>
<th>{$panelstrings.email}</th>
<th>{$panelstrings.ip}</th>
<th>{$panelstrings.actions}</th>
</tr></thead>
<tbody>
{comment}
<tr>
{*<td><input type="checkbox" /></td>*}
<td>{$date|date_format:"%D, %T"}</td>
<td class="main_cell">
{$content|strip_tags|truncate:70}
</td>
<td>{if $url}<a href="{$url}">{$name}</a>{else}{$name}{/if}</td>
<td><a href="mailto:{$email}">{$email}</a></td>
<td>{$ip_address}</td>
<td>
<a class="link-general"
href="{"`$panel_url`&entry=`$entryid`"|action_link:commedit|cmd_link:comment:$id}">
{$plang.act_edit}
</a>
<a class="link-delete" onclick="return admin_entry_comment_delete();" href="{"`$panel_url`&entry=`$entryid`"|action_link:commentlist|cmd_link:delete:$id}">
{$plang.act_del}
</a>
</td>
</tr>
{/comment}
</tbody></table>
{/html_form}

{/comment_block}

{/entry}
{/entry_block}





