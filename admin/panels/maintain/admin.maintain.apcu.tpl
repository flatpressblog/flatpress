<h2>{$panelstrings.head}</h2>
{include file="shared:errorlist.tpl"}

{if !$apcu_available}
	<p>{$panelstrings.no_apcu}</p>
	<p><a href="admin.php?p=maintain">{$panelstrings.back}</a></p>
{else}
<style type="text/css">
.apcu-panel { max-width: 960px; margin: 0 auto; }
.apcu-status-box { padding: 1em 1.2em; margin-bottom: 1em; border-radius: 4px; }
.apcu-status-good { background: #dff0d8; border-left: 4px solid #3c763d; }
.apcu-status-bad { background: #f2dede; border-left: 4px solid #a94442; }
.apcu-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); grid-gap: 0.75em; margin-bottom: 1em; }
.apcu-card { padding: 0.75em 1em; border: 1px solid #ddd; border-radius: 4px; background: #fafafa; }
.apcu-label { font-size: 0.9em; color: #666; }
.apcu-value { font-weight: bold; }
.apcu-actions { margin: 0.5em 0 1em 0; }
.apcu-clear-result { margin: 0.25em 0 1em 0; }
@media (max-width: 720px) {
	.apcu-panel { padding: 0 0.25em; }
}
</style>

<div class="apcu-panel">

	<p>{$panelstrings.descr}</p>

	<div class="apcu-status-box {if $apcu.status == 'good'}apcu-status-good{else}apcu-status-bad{/if}">
		<strong>{$panelstrings.status_heading}</strong>
		<p>{if $apcu_status > 0}{$panelstrings.status_good}{else}{$panelstrings.status_bad}{/if}</p>
		{if $apcu.hit_rate_str}
			<p>{$panelstrings.hit_rate}: {$apcu.hit_rate_str}%</p>
		{/if}
		{if $apcu.free_pct_str}
			<p>{$panelstrings.free_mem}: {$apcu.free_pct_str}%</p>
		{/if}
	</div>

	{if isset($apcu_clear_result)}
	<p class="apcu-clear-result apcu-label">
		{$panelstrings.clear_fp_result|sprintf:$apcu_clear_result.cleared}
	</p>
	{/if}

	<p class="apcu-label">
		{$panelstrings.legend_good}<br>
		{$panelstrings.legend_bad}
	</p>

	<div class="apcu-grid">
		<div class="apcu-card">
			<div class="apcu-label">apc.shm_size</div>
			<div class="apcu-value">{$apcu.shm_size_ini}</div>
		</div>
		<div class="apcu-card">
			<div class="apcu-label">{$panelstrings.memory_type}</div>
			<div class="apcu-value">
				{if $apcu.memory_type}{$apcu.memory_type}{else}{$panelstrings.memory_type_unknown}{/if}
			</div>
		</div>
		<div class="apcu-card">
			<div class="apcu-label">{$panelstrings.num_slots}</div>
			<div class="apcu-value">{$apcu.num_slots}</div>
		</div>
		<div class="apcu-card">
			<div class="apcu-label">{$panelstrings.num_hits}</div>
			<div class="apcu-value">{$apcu.num_hits}</div>
		</div>
		<div class="apcu-card">
			<div class="apcu-label">{$panelstrings.num_misses}</div>
			<div class="apcu-value">{$apcu.num_misses}</div>
		</div>
		<div class="apcu-card">
			<div class="apcu-label">{$panelstrings.total_mem}</div>
			<div class="apcu-value">
				{if $apcu.total_mem_str}
					{$apcu.total_mem_str}
				{else}
					-
				{/if}
			</div>
		</div>
		<div class="apcu-card">
			<div class="apcu-label">{$panelstrings.used_mem}</div>
			<div class="apcu-value">
				{if $apcu.used_mem_str}
					{$apcu.used_mem_str}
				{/if}
				{if $apcu.used_pct_str}
					&nbsp;&ndash;&nbsp;{$apcu.used_pct_str}%
				{/if}
			</div>
		</div>
		<div class="apcu-card">
			<div class="apcu-label">{$panelstrings.avail_mem}</div>
			<div class="apcu-value">
				{if $apcu.avail_mem_str}
					{$apcu.avail_mem_str}
				{/if}
				{if $apcu.free_pct_str}
					&nbsp;&ndash;&nbsp;{$apcu.free_pct_str}%
				{/if}
			</div>
		</div>
		<div class="apcu-card">
			<div class="apcu-label">{$panelstrings.cache_type}</div>
			<div class="apcu-value">{$panelstrings.cache_user_only}</div>
		</div>
	</div>

	<div class="buttonbar">

		<div class="apcu-actions">
			{html_form method=post id='apcu-clear-form'}
				<input type="submit" name="apcu_clear_fp" value="{$panelstrings.clear_fp_button}">
			{/html_form}
		</div>

	</div>

	<p><a href="admin.php?p=maintain">{$panelstrings.back}</a></p>

	<script type="text/javascript" nonce="{$smarty.const.RANDOM_HEX}">
		/**
		 * Security prompt for deleting the APCu cache
		 */
		document.addEventListener('DOMContentLoaded', function () {
			var form = document.getElementById('apcu-clear-form');
			if (!form) {
				return;
			}
			form.addEventListener('submit', function (e) {
				if (!confirm('{$panelstrings.clear_fp_confirm|escape:"javascript"}')) {
					e.preventDefault();
				}
			});
		});
	</script>
</div>
{/if}
