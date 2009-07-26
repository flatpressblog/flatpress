<h2>{"Statistics"}</h2>

{include file='shared:errorlist.tpl'}

<h3>{"Entries"}</h3>
{"<p>You have <strong>%s</strong> 
entries using <strong>%s</strong> characters
in  <strong>%s</strong> words.</p>
<p>Total disk space is 
<strong>%s</strong>.</p>"|sprintf:$entries.count:$entries.chars:$entries.words:$entries.size}

<h3>{"Comments"}</h3>
{"<p>You have <strong>%s</strong> 
comments using <strong>%s</strong> characters
in  <strong>%s</strong> words.</p>
<p>Total disk space is 
<strong>%s</strong>.</p>"|sprintf:$comments.count:$comments.chars:$comments.words:$comments.size}


{if $entries.topten}

<h3> {$entries.topten|@count} {"most commented entries"} </h3>

<ol>
{foreach from=$entries.topten key=id item=this_entry}
<li><a href="{$id|link:post_link}">{$this_entry.subject}</a> ({$this_entry.comments})</li>
{/foreach}
</ol>

{/if}

