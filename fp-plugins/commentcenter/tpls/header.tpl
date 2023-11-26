<h2>{$plang.title}</h2>
<p>{$plang.desc1}</p>
<p>{$plang.desc2}</p>
<ul>
<li><a href="{$panel_url|action_link:commentcenter}" title="{$plang.lpolicies}">{$plang.lpolicies}</a></li>
<li><a href="{$action_url|cmd_link:approve_list:1}" title="{$plang.lapprove}">{$plang.lapprove}</a></li>
<li><a href="{$action_url|cmd_link:manage:'search'}" title="{$plang.lmanage}">{$plang.lmanage}</a></li>
<li><a href="{$action_url|cmd_link:configure:1}" title="{$plang.lconfig}">{$plang.lconfig}</a></li>
<li><a class="hint externlink" href="https://wiki.flatpress.org/doc:techfaq#comments" target="_blank">{$plang.faq_spamcomments}</a></li>
</ul>

{include file='shared:errorlist.tpl'}


