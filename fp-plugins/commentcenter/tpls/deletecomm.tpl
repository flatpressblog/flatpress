{include file=plugin:commentcenter/header}
<h2>{$plang.delc_title}</h2>
<p>{if isset($single)}{$plang.delc_descs}{else}{$plang.delc_descm}{/if}</p>
{html_form}
{assign var="delete" value=true}
{assign var="fetch" value="del"}
{include file=plugin:commentcenter/listcomments}
<p>{$plang.sure}</p>
<div class="buttonbar">
{if isset($is_managing)}
<input type="hidden" name="entry" value="{$entry}" />
{assign var="button_suff" value="_2"}
{else}
{assign var="button_suff" value=""}
{/if}
{if isset($single)}
	{html_submit name="commdelok$button_suff" class="btn btn-primary" id="commdelok$button_suff" value=$plang.del_subs}
{else}
	{html_submit name="commdelok$button_suff" class="btn btn-primary" id="commdelok$button_suff" value=$plang.del_subm}
{/if}
	{html_submit name="ccancel$button_suff" class="btn btn-secondary" id="ccancel$button_suff" value=$plang.del_cancel}
</div>
{/html_form}
{include file=plugin:commentcenter/footer}