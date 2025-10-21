<h2>{$panelstrings.head}</h2>

{include file="shared:errorlist.tpl"}

<p>{$panelstrings.filesystem_total}: <strong>{$quota.total}</strong></p>
{if $quota.used != 'n/a'}<p>{$panelstrings.webspace_used}: <strong>{$quota.used}</strong></p>{/if}
{if $quota.free != 'n/a'}<p>{$panelstrings.webspace_free}: <strong>{$quota.free}</strong></p>{/if}
<p>{$panelstrings.source}: <code>{$quota.source}</code></p>
{if $quota.fp_pct_ws != 'n/a'}
	<p>{$panelstrings.flatpress_share}: <strong>{$quota.fp_pct_ws} %</strong></p>
{/if}
<p>{$panelstrings.flatpress_folder_total}: <strong>{"%s"|sprintf:$storage.fp_size}</strong></p>

<h3>{$panelstrings.images}</h3>
<p>{$panelstrings.you_have} <strong>{"%s"|sprintf:$images.count}</strong> {$panelstrings.files}.</p>
<p>{$panelstrings.total_disk_space_is} <strong>{"%s"|sprintf:$images.size}</strong>.</p>

<h3>{$panelstrings.attachs}</h3>
<p>{$panelstrings.you_have} <strong>{"%s"|sprintf:$attachs.count}</strong> {$panelstrings.files}.</p>
<p>{$panelstrings.total_disk_space_is} <strong>{"%s"|sprintf:$attachs.size}</strong>.</p>

<h3>{$panelstrings.statics}</h3>
<p>{$panelstrings.you_have} <strong>{"%s"|sprintf:$statics.count}</strong> {$panelstrings.static_files}.</p>
<p>{$panelstrings.total_disk_space_is} <strong>{"%s"|sprintf:$statics.size}</strong>.</p>

<h3>{$panelstrings.entries}</h3>
<p>{$panelstrings.you_have} <strong>{"%s"|sprintf:$entries.count}</strong> {$panelstrings.entries}.</p>
<p>{$panelstrings.total_disk_space_is} <strong>{"%s"|sprintf:$entries.size}</strong>.</p>

<h3>{$panelstrings.comments}</h3>
<p>{$panelstrings.you_have} <strong>{"%s"|sprintf:$comments.count}</strong> {$panelstrings.comments}.</p>
<p>{$panelstrings.total_disk_space_is} <strong>{"%s"|sprintf:$comments.size}</strong>.</p>

{if $show_topten && $entries.topten}

<h3>{$panelstrings.the} {$entries.topten|@count} {$panelstrings.most_commented_entries}</h3>

<ol>
{foreach from=$entries.topten key=id item=this_entry}
<li><a href="{$smarty.const.BLOG_BASEURL}admin.php?p=entry&amp;entry={$id}">{$this_entry.subject}</a> ({$this_entry.comments})</li>
{/foreach}
</ol>

{/if}

