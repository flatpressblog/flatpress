<h2>{$panelstrings.head}</h2>

{include file="shared:errorlist.tpl"}

<h3>{$panelstrings.entries}</h3>
<p>{$panelstrings.you_have} <strong>{"%s"|sprintf:$entries.count}</strong> 
{$panelstrings.entries_using} <strong>{"%s"|sprintf:$entries.chars}</strong> {$panelstrings.characters_in} <strong>{"%s"|sprintf:$entries.words}</strong> {$panelstrings.words}.</p>
<p>{$panelstrings.total_disk_space_is} <strong>{"%s"|sprintf:$entries.size}</strong>.</p>

<h3>{$panelstrings.comments}</h3>
<p>{$panelstrings.you_have} <strong>{"%s"|sprintf:$comments.count}</strong> 
{$panelstrings.comments_using} <strong>{"%s"|sprintf:$comments.chars}</strong> {$panelstrings.characters_in} <strong>{"%s"|sprintf:$comments.words}</strong> {$panelstrings.words}.</p>
<p>{$panelstrings.total_disk_space_is} <strong>{"%s"|sprintf:$comments.size}</strong>.</p>


{if $entries.topten}

<h3>{$panelstrings.the} {$entries.topten|@count} {$panelstrings.most_commented_entries} </h3>

<ol>
{foreach from=$entries.topten key=id item=this_entry}
<li><a href="{$panel_url|action_link:commentlist}&amp;entry={$id}">{$this_entry.subject}</a> ({$this_entry.comments})</li>
{/foreach}
</ol>

{/if}

