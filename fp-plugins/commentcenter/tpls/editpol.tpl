{include file=plugin:commentcenter/header}
{html_form}
<h2>{if isset($polnew)}{$plang.newpol}{else}{$plang.editpol}{/if}</h2>
<dl class="option-set">
	<dt>{$plang.apply_to}</dt>
	<dd>
		<input type="radio" class="form-check-input" name="apply_to" id="all_entries" value="all_entries"{if isset($policy.is_all)} checked="checked"{/if} />
		<label for="all_entries">{$plang.all_entries}</label><br />
		<input type="radio" class="form-check-input" name="apply_to" id="some_entries" value="some_entries"{if isset($policy.entry) && !empty($policy.entry)} checked="checked"{/if} />
		<label for="some_entries">{$plang.some_entries}</label><br />
		<input type="radio" class="form-check-input" name="apply_to" id="properties" value="properties"{if isset($polnew) && !$polnew && isset($policy.is_all) && !$policy.is_all && isset($policy.entry) && empty($policy.entry)} checked="checked"{/if} />
		<label for="properties">{$plang.properties}</label><br />
	</dd>
	<dt><label for="behavoir">{$plang.behavoir}</label></dt>
	<dd>
		<select name="behavoir" class="form-select" id="behavoir">
			<option value="1"{if isset($policy.do) && $policy.do==1} selected="selected"{/if}>{$plang.allow}</option>
			<option value="0"{if isset($policy.do) && $policy.do==0 && isset($polnew) && !$polnew} selected="selected"{/if}>{$plang.approvation}</option>
			<option value="-1"{if isset($policy.do) && $policy.do==-1} selected="selected"{/if}>{$plang.block}</option>
		</select>
	</dd>
</dl>

<!-- Maybe here I could add a JS to hide the unused sections -->
<div id="fill_entries">
<h3>{$plang.entries}</h3>
<p>{$plang.se_desc|sprintf:"<i>`$plang.some_entries`</i>"}</p>
<p>{$plang.se_fill}</p>

{if isset($policy.entry) && !empty($policy.entry) && !is_array($policy.entry)}
{assign var="parity" value=1}
<input type="text" class="form-control" name="entries[]" value="{$policy.entry|wp_specialchars}" />
{elseif isset($policy.entry) && !empty($policy.entry)}
{foreach name=entries_foreach from=$policy.entry item=entry}
<input type="text" class="form-control" name="entries[]" value="{$entry|wp_specialchars}" /> {if ($smarty.foreach.entries_foreach.iteration % 2)==0}<br />{/if}
{if ($smarty.foreach.entries_foreach.iteration % 2)==1 && $smarty.foreach.entries_foreach.last}{assign var="parity" value=1}{/if}
{/foreach}
{/if}
{if isset($parity) && $parity==1} <input type="text" name="entries[]" value="" /><br />{/if}

<input type="text" class="form-control" name="entries[]" value="" />
<input type="text" class="form-control" name="entries[]" value="" /><br />
<input type="text" class="form-control" name="entries[]" value="" />
<input type="text" class="form-control" name="entries[]" value="" /><br />
</div>

<div id="fill_properties" class="option-set">
<h3>{$plang.po_title}</h3>
<p>{$plang.po_desc|sprintf:"<i>`$plang.properties`</i>"}</p>
<p>{$plang.po_comp}</p>

<!-- That isn't the real id but... -->
<fieldset id="admin-entry-categories">
<legend>{$plang.categories}</legend>
{if isset($policy.categories)}
{list_categories type=form selected=$policy.categories}
{else}
{list_categories type=form}
{/if}
</fieldset>

<fieldset>
<legend>{$plang.po_time}</legend>
<p><label for="older">{$plang.po_older}</label>
<input type="text" class="form-control" name="older" id="older" value="{if isset($policy.older) && !empty($policy.older)}{$policy.older/86400}{/if}" class="smalltextinput" style="display: inline-block;"/>
{$plang.days}</p>
<!-- TODO: add the option for timestamp -->
</fiedlset>

</div>

<div class="buttonbar">
	<input type="hidden" name="policy_id" value="{$pol_id}" />
	{html_submit name="edit_policy" class="btn btn-primary" id="edit_policy" value=$plang.save_policy}
</div>
{/html_form}
{include file=plugin:commentcenter/footer}