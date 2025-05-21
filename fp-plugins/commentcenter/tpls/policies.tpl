{include file=plugin:commentcenter/header}
<h2>{$plang.policies}</h2>
<p>{$plang.desc_pol}</p>
{html_form}
{include file=plugin:commentcenter/listpolicies}
<div class="commentcenter_select" style="display: none;">
	<a href="#" rel="selectAll[td_select]">{$plang.select_all}</a> 
	<a href="#" rel="deselectAll[td_select]">{$plang.deselect_all}</a>
</div>
<div class="buttonbar">
	<a href="{$action_url|cmd_link:poledit:-1}" class="btn btn-secondary"><img src="{$plugin_url}imgs/edit.png" alt="{$plang.newpol}" /> {$plang.newpol}</a>
	{html_submit class="btn btn-primary" name="multidel" id="multidel" value=$plang.del_selected}
</div>
{/html_form}
{include file=plugin:commentcenter/footer}