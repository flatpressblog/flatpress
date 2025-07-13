
		<h2>{$lang.admin.plugin.newsletter.admin_subscribers_list|escape}</h2>

		{include file="shared:errorlist.tpl"}

		<p>{$lang.admin.plugin.newsletter.desc_subscribers|escape}</p>
		<table>
			<thead>
				<tr>
					<th>{$lang.admin.plugin.newsletter.email_address|escape}</th>
					<th>{$lang.admin.plugin.newsletter.subscribe_date|escape}</th>
					<th>{$lang.admin.plugin.newsletter.subscribe_time|escape}</th>
				</tr>
			</thead>
			<tbody>
				{if $subscribers|@count > 0}
					{foreach from=$subscribers item=sub}
						<tr>
							<td>
								<form method="post" style="display: inline;" class="newsletter-delete">
									<input type="hidden" name="newsletter_delete" value="{$sub.email_encoded|escape}">
									<input type="hidden" name="csrf_token" value="{$newsletter_csrf_token|escape}">
									<input type="submit" title="{$lang.admin.plugin.newsletter.delete_subscriber}" style="cursor: pointer;" class="link-button" value="{$sub.email|escape}">
								</form>
							</td>
							<td>{$sub.date|escape}</td>
							<td>{$sub.time|escape}</td>
						</tr>
					{/foreach}
				{else}
					<tr>
						<td colspan="3" class="no-data">{$lang.admin.plugin.newsletter.newsletter_no_subscribers}</td>
					</tr>
				{/if}
			</tbody>
		</table>

		{html_form class="newsletter-settings"}
			<p>{$lang.admin.plugin.newsletter.desc_batch}</p>
			<fieldset>
				<label for="newsletter_batch_size">
					{$lang.admin.plugin.newsletter.batch_size_label|escape}:
				</label>
				<input type="number" id="newsletter_batch_size" style="width: 5em;" name="newsletter_batch_size" value="{$batch_size|escape}" min="1" required>
				<div class="buttonbar" style="display: inline;">
					<input type="submit" name="newsletter_save_settings" value="{$lang.admin.plugin.newsletter.save_button|escape}">
				</div>
			</fieldset>
		{/html_form}

		<div class="buttonbar">
			<form id="newsletter-sendall" method="post">
				<input type="hidden" name="csrf_token" value="{$newsletter_csrf_token}">
				{if $batch_pending}
					<div class="newsletter-status">
						<p>{$batch_type} {$lang.admin.plugin.newsletter.sub_remaining|escape} <strong>{$subscribers_remaining}</strong></p>
					</div>
				{else}
					<input type="submit" style="cursor: pointer;" name="newsletter_send_all" value="{$lang.admin.plugin.newsletter.send_all_button|escape}">
				{/if}
			</form>
		</div>

		<script nonce="{$smarty.const.RANDOM_HEX}">
			/**
			 * Newletter plugin admin
			 */
			document.addEventListener('DOMContentLoaded', function(){
				var sendAll = document.getElementById('newsletter-sendall');
				if (sendAll) {
					sendAll.addEventListener('submit', function(e){
						if (!confirm('{$lang.admin.plugin.newsletter.send_all_confirm|escape}')) {
							e.preventDefault();
						}
					});
				}
				document.querySelectorAll('form.newsletter-delete').forEach(function(f){
					f.addEventListener('submit', function(e){
						if (!confirm('{$lang.admin.plugin.newsletter.delete_confirm|escape}')) {
							e.preventDefault();
						}
					});
				});
			});
		</script>
