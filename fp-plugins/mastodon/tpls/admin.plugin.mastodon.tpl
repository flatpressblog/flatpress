<h2>{$plang.head|escape}</h2>

{include file="shared:errorlist.tpl"}

<p>{$plang.intro|escape}</p>

<fieldset><legend>{$mastodon_companion_plugins_head|escape}</legend>
	<p>{$mastodon_companion_plugins_intro|escape}</p>
	<dl class="option-list">
		{foreach from=$mastodon_companion_plugins item=companion}
			<dt>{$companion.label|escape}</dt>
			<dd>
				<strong>{$companion.status_label|escape}</strong><br>
				<small>{$companion.description|escape}</small>
			</dd>
		{/foreach}
	</dl>
</fieldset>

{html_form class="option-set"}

	<fieldset><legend>{$plang.config_head|escape}</legend>
		<dl class="option-list">

			<dt><label for="username">{$plang.username|escape}</label></dt>
			<dd><input type="text" class="regular-text" id="username" name="username" value="{$mastodon_cfg.username|escape}"></dd>

			<dt><label for="instance_url">{$plang.instance_url|escape}</label></dt>
			<dd><input type="url" class="regular-text" id="instance_url" name="instance_url" value="{$mastodon_cfg.instance_url|escape}"></dd>

			<dt><label for="sync_time">{$plang.sync_time|escape}</label></dt>
			<dd><input type="time" id="sync_time" name="sync_time" value="{$mastodon_cfg.sync_time|escape}"></dd>

			<dt><label for="sync_start_date">{$plang.sync_start_date|escape}</label></dt>
			<dd><input type="date" id="sync_start_date" name="sync_start_date" value="{$mastodon_cfg.sync_start_date|escape}"></dd>
		</dl>

	<div class="buttonbar">
		<input type="submit" name="mastodon_save" value="{$plang.save|escape}">
	</div>
	</fieldset>

	<fieldset><legend>{$plang.more_options|escape}</legend>
		<dl class="option-list">
			<dt><label for="update_local_from_remote">{$plang.update_local_from_remote|escape}</label></dt>
			<dd>
				<label>
					<input type="checkbox" id="update_local_from_remote" name="update_local_from_remote" value="1"{if $mastodon_cfg.update_local_from_remote eq '1'} checked="checked"{/if}>
					{$plang.update_local_from_remote_desc|escape}
				</label>
			</dd>

			<dt><label for="import_synced_comments_as_entries">{$plang.import_synced_comments_as_entries|escape}</label></dt>
			<dd>
				<label>
					<input type="checkbox" id="import_synced_comments_as_entries" name="import_synced_comments_as_entries" value="1"{if $mastodon_cfg.import_synced_comments_as_entries eq '1'} checked="checked"{/if}>
					{$plang.import_synced_comments_as_entries_desc|escape}
				</label>
			</dd>
		</dl>

	<div class="buttonbar">
		<input type="submit" name="mastodon_save" value="{$plang.save|escape}">
	</div>
	</fieldset>


	<fieldset><legend>{$plang.oauth_head|escape}</legend>
		<p>{$plang.oauth_desc|escape}</p>
		<div class="buttonbar">
			<input type="submit" name="mastodon_register_app" value="{$plang.register_app|escape}">
		</div>

		<dl class="option-list">
			<dt><label for="authorize_url">{$plang.authorize_url|escape}</label></dt>
			<dd>
				{if $mastodon_authorize_url}
					<input type="url" readonly="readonly" class="regular-text" value="{$mastodon_authorize_url|escape}">
				{else}
					<em>-</em>
				{/if}
			</dd>

			<dt><label for="authorization_code">{$plang.authorization_code|escape}</label></dt>
			<dd><input type="text" class="regular-text" id="authorization_code" name="authorization_code" value=""></dd>
		</dl>

		<div class="buttonbar">
			<input type="submit" name="mastodon_exchange_code" value="{$plang.exchange_code|escape}">
			<input type="submit" name="mastodon_clear_token" value="{$plang.clear_token|escape}">
		</div>
	</fieldset>

	<div class="buttonbar">
		<input type="submit" name="mastodon_run_now" value="{$plang.run_now|escape}">
	</div>
{/html_form}

<h3>{$plang.status_head|escape}</h3>
<table>
	<thead>
		<tr>
			<th>{$plang.description|escape}</th>
			<th>{$plang.output|escape}</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>{$plang.temp_dir|escape}</td>
			<td><code>{$mastodon_temp_dir|escape}</code></td>
		</tr>
		<tr>
			<td>{$plang.token_state|escape}</td>
			<td>{if $mastodon_cfg.access_token}{$plang.token_available|escape}{else}{$plang.token_missing|escape}{/if}</td>
		</tr>
		<tr>
			<td>{$plang.last_run|escape}</td>
			<td>{if $mastodon_state.last_run}{$mastodon_state.last_run|escape}{else}-{/if}</td>
		</tr>
		<tr>
			<td>{$plang.last_error|escape}</td>
			<td>{if $mastodon_state.last_error}{$mastodon_state.last_error|escape}{else}-{/if}</td>
		</tr>
	</tbody>
</table>

<h3>{$plang.stats_head|escape}</h3>
<table>
	<thead>
		<tr>
			<th>{$plang.content|escape}</th>
			<th>{$plang.counter|escape}</th>
		</tr>
	</thead>
	<tbody>
		<tr><td>{$plang.stats_imported_entries|escape}</td><td>{$mastodon_state.stats.imported_entries|default:0}</td></tr>
		<tr><td>{$plang.stats_updated_entries|escape}</td><td>{$mastodon_state.stats.updated_entries|default:0}</td></tr>
		<tr><td>{$plang.stats_exported_entries|escape}</td><td>{$mastodon_state.stats.exported_entries|default:0}</td></tr>
		<tr><td>{$plang.stats_updated_remote_entries|escape}</td><td>{$mastodon_state.stats.updated_remote_entries|default:0}</td></tr>
		<tr><td>{$plang.stats_imported_comments|escape}</td><td>{$mastodon_state.stats.imported_comments|default:0}</td></tr>
		<tr><td>{$plang.stats_exported_comments|escape}</td><td>{$mastodon_state.stats.exported_comments|default:0}</td></tr>
		<tr><td>{$plang.stats_updated_remote_comments|escape}</td><td>{$mastodon_state.stats.updated_remote_comments|default:0}</td></tr>
	</tbody>
</table>
