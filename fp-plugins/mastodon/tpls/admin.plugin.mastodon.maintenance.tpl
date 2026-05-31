<h2>{$mastodon_lang.state_maintenance_head|escape}</h2>

{include file="shared:errorlist.tpl"}

<p>{$mastodon_lang.state_maintenance_desc|escape}</p>
<p><a class="link-general" href="{$mastodon_admin_main_url|escape}">{$mastodon_lang.state_maintenance_back|escape}</a></p>

{html_form class="option-set"}
	<fieldset><legend>{$mastodon_lang.state_maintenance_actions_head|escape}</legend>
		<p>{$mastodon_lang.state_maintenance_actions_desc|escape}</p>
		<div class="buttonbar">
			<input type="submit" name="mastodon_diagnose_state" value="{$mastodon_lang.state_maintenance_diagnose|escape}">
			<input type="submit" name="mastodon_repair_state" value="{$mastodon_lang.state_maintenance_repair|escape}">
		</div>
	</fieldset>
{/html_form}

{if $mastodon_state_maintenance_result.available}
<h3>{$mastodon_lang.state_maintenance_result_head|escape}</h3>
<table>
	<thead>
		<tr>
			<th>{$mastodon_lang.description|escape}</th>
			<th>{$mastodon_lang.output|escape}</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>{$mastodon_lang.state_maintenance_status|escape}</td>
			<td>{if $mastodon_state_maintenance_result.ok}{$mastodon_lang.state_maintenance_status_ok|escape}{else}{$mastodon_lang.state_maintenance_status_attention|escape}{/if}</td>
		</tr>
		{foreach from=$mastodon_state_maintenance_result.stats_rows item=maintenance_row}
		<tr>
			<td>{$maintenance_row.label|escape}</td>
			<td>{$maintenance_row.value|escape}</td>
		</tr>
		{/foreach}
		{if $mastodon_state_maintenance_result.warnings|@count gt 0}
		<tr>
			<td>{$mastodon_lang.state_maintenance_warnings|escape}</td>
			<td>
				<ul>
					{foreach from=$mastodon_state_maintenance_result.warnings item=maintenance_warning}
						<li>{$maintenance_warning|escape}</li>
					{/foreach}
				</ul>
			</td>
		</tr>
		{/if}
		{if $mastodon_state_maintenance_result.errors|@count gt 0}
		<tr>
			<td>{$mastodon_lang.state_maintenance_errors|escape}</td>
			<td>
				<ul>
					{foreach from=$mastodon_state_maintenance_result.errors item=maintenance_error}
						<li>{$maintenance_error|escape}</li>
					{/foreach}
				</ul>
			</td>
		</tr>
		{/if}
	</tbody>
</table>
{/if}
