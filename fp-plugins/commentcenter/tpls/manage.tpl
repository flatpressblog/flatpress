{include file=plugin:commentcenter/header}
{html_form}
<h2>{$plang.man_searcht}</h2>
<p>{$plang.man_searchd}</p>

<div class="form-row">
	<input type="text" class="form-control" name="entry" value="{$entry_id}" style="display: inline-block;"/>
	{html_submit name="entry_search" class="btn btn-primary mt-1" id="entry_search" value=$plang.man_search}
</div>

{if !empty($entry_id)}
{assign var="titleok" value=$entry_id|idToSubject|wp_specialchars}
<h2>{$plang.man_commfor|sprintf:$titleok}</h2>
{include file=plugin:commentcenter/listcomments}
<input type="hidden" name="entry_hid" value="{$entry_id}" />
<div class="buttonbar">
	{html_submit name="mdelcomm_2" id="mdelcomm_2" class="btn btn-primary" value=$plang.app_dselected}
</div>
{/if}

{/html_form}
{include file=plugin:commentcenter/footer}