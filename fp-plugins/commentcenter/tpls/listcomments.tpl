{if !isset($is_managing)}
	{assign var="is_managing" value=false}
{/if}
<table class="entrylist">
<thead><tr>
{if !isset($delete)}<th></th>{/if}
<th>{$plang.app_date}</th>
<th>{$plang.app_content}</th>
<th>{$plang.app_author}</th>
<th>{$plang.app_email}</th>
<th>{$plang.app_ip}</th>
{if !isset($delete)}<th>{$plang.app_actions}</th>{/if}
</tr></thead>
<tbody>
{assign var="i" value=0}
{foreach from=$entries key=entryid item=entry}
	{if isset($entry.$fetch)}
		{if count($entry.$fetch)>0 && !$is_managing}
			<tr><td colspan="{if isset($delete)}5{else}7{/if}">{$entryid|idToSubject} ({$entryid})</td></tr>
		{/if}
		{foreach from=$entry.$fetch item=comm key=comm_id}
			{assign var="i" value=$i+1}
			<tr>
			{if !isset($delete)}<td class="td_select_{$fetch}"><input type="checkbox" name="select[e{$entryid}_c{$comm_id}]" /></td>{/if}
			<td>{$comm.date|date_format:"%D, %T"}</td>
			<td class="main_cell">
			{$comm.content|strip_tags}
			{if isset($delete)}<input type="hidden" value="on" name="select[e{$entryid}_c{$comm_id}]" />

			{/if}
			</td>
			<td>{if isset($comm.url)}<a href="{$comm.url|wp_specialchars}">{$comm.name|wp_specialchars}</a>{else}{$comm.name|wp_specialchars}{/if}</td>
			<td>{if isset($comm.email)}<a href="mailto:{$comm.email|wp_specialchars}">{$comm.email|wp_specialchars}</a>{else} {/if}</td>
			{* a bit hackish: {$comm.ip-adress} would lead to $this->_tpl_vars['comm']['ip']-$this->_tpl_vars['ddress']; *}
			{assign var=ipadress value="ip-address"}
			<td>{$comm.$ipadress}</td>
			{if !isset($delete)}<td>
			{if isset($is_managing) && isset($use_akismet)}
			<a href="{$action_url|cmd_link:commspam:"e`$entryid`_c`$comm_id`"}" title="{$plang.man_spam}"><img src="{$plugin_url}imgs/spam.png" alt="{$plang.man_spam}" /></a>
			{elseif !$is_managing}
			<a href="{$action_url|cmd_link:publishcomm:"e`$entryid`_c`$comm.id`"}" title="{$plang.app_publish}"><img src="{$plugin_url}imgs/publish.png" alt="{$plang.app_publish}" /></a>
			{if $fetch=='akismet'}
			<a href="{$action_url|cmd_link:pubnoham:"e`$entryid`_c`$comm.id`"}" title="{$plang.app_pubnotham}"><img src="{$plugin_url}imgs/publish.png" alt="{$plang.app_pubnotham}" /></a>
			{/if}
			{/if}
			{if $is_managing}
			{assign var="rm_url" value=$action_url|cmd_link:deletecomm2:"e`$entryid`_c`$comm_id`"}
			{else}
			{assign var="rm_url" value=$action_url|cmd_link:deletecomm:"e`$entryid`_c`$comm_id`"}
			{/if}
			<a href="{$rm_url}" title="{$plang.app_delete}"><img src="{$plugin_url}imgs/delete.png" alt="{$plang.app_delete}" /></a>
			</td>
			{/if}
			</tr>
		{/foreach}
	{/if}
{/foreach}
{if $i==0}
<tr><td colspan="{if isset($delete)}5{else}7{/if}">{$plang.app_nocomms}</td></tr>{/if}
</tbody>
</table>
{if !isset($delete)}
<div class="commentcenter_select" style="display: none;">
	<a href="#" rel="selectAll[td_select_{$fetch}]">{$plang.select_all}</a> 
	<a href="#" rel="deselectAll[td_select_{$fetch}]">{$plang.deselect_all}</a>
</div>
{/if}
