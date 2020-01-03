{include file=plugin:commentcenter/header}
<h2>{$plang.del_policies}</h2>
<p>{if $single}{$plang.del_descs}{else}{$plang.del_descm}{/if}</p>
{html_form}
{assign var="delete" value=true}
{include file=plugin:commentcenter/listpolicies}
<p>{$plang.sure}</p>
<div class="buttonbar">
{if $single}
	{html_submit name="delok" id="delok" value=$plang.del_subs}
{else}
	{html_submit name="delok" id="delok" value=$plang.del_subm}
{/if}
	{html_submit name="cancel" id="cancel" value=$plang.del_cancel}
</div>
{/html_form}
