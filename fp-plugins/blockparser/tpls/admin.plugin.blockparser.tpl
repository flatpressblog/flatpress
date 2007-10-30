{*assign var="ps" value=`$panelstrings.blockparser`*}
{*assign var="panelstrings" value=`$panelstrings.blockparser`*}
<h2>{$ps.head}</h2>
<p>{$ps.description}</p>

{include file=shared:errorlist.tpl}
{static_block}

<form method="post"
	action="{$smarty.const.BLOG_BASEURL}admin.php?p=entry"
	enctype="multipart/form-data">
	
<table class="entrylist">
<thead>
<tr><th>{$panelstrings.title}</th>
<th>{$panelstrings.enable}</th></tr></thead>
<tbody>
{static}
<tr>

<td>
<a href="admin.php?p=static&amp;page={$id}&amp;action=write">{$subject|truncate:70}</a>
</td>
<td>enable/disable</td>

</tr>

{/static}
</tbody></table>
</form>

{/static_block}

