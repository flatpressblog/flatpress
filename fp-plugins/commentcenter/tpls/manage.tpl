{include file=plugin:commentcenter/header}
{html_form}
<h2>{$plang.man_searcht}</h2>
<p>{$plang.man_searchd}</p>
{if !isset($entry_id)}
	{assign var=entry_id value=""}
{/if}
<p><input type="text" name="entry" value="{$entry_id}" />
{html_submit name="entry_search" id="entry_search" value=$plang.man_search}</p>

{if !empty($entry_id)}
{assign var="titleok" value=$entry_id|idToSubject|wp_specialchars}
<h2>{$plang.man_commfor|sprintf:$titleok}</h2>
{include file=plugin:commentcenter/listcomments}
<input type="hidden" name="entry_hid" value="{$entry_id}" />
<div class="buttonbar">
	{html_submit name="mdelcomm_2" id="mdelcomm_2" value=$plang.app_dselected}
</div>
{/if}

{/html_form}
