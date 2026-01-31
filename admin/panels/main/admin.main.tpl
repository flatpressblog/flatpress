<h2>{$panelstrings.head}</h2>

{if isset($fp_setup_hide_report) && isset($fp_setup_hide_report.errors) && $fp_setup_hide_report.errors|count}
	<ul class="msgs errors">
		<li><strong>{$panelstrings.setup_hide_failed_head|default:'Security notice: setup is still accessible'|escape}</strong></li>
		<li>{$panelstrings.setup_hide_failed_descr|default:'FlatPress could not hide the setup entry points automatically. Please rename setup.php to .setup.php and the setup/ directory to .setup/ (or deny access via your webserver configuration).'|escape}</li>
		<li>{$panelstrings.setup_hide_failed_items|default:'Affected paths:'|escape}</li>
		{foreach from=$fp_setup_hide_report.errors item=path}
			<li><code>{$path|escape}</code></li>
		{/foreach}
	</ul>
{/if}

<p>{$panelstrings.descr}</p>

<dl>
	<dt class="admin-mainmenu-item">
		<img src="{$smarty.const.ADMIN_DIR}imgs/newentry.png" class="alignleft" alt="{$panelstrings.op1}"
		title="{$panelstrings.op1}">
		<a href="admin.php?p=entry&amp;action=write">{$panelstrings.op1}</a>
	</dt>
	<dd class="admin-icon-descr">{$panelstrings.op1d}</dd>

	<dt class="admin-mainmenu-item">
		<img src="{$smarty.const.ADMIN_DIR}imgs/entries.png" class="alignleft" alt="{$panelstrings.op2}"
		title="{$panelstrings.op2}">
		<a href="admin.php?p=entry">{$panelstrings.op2}</a>
	</dt>
	<dd class="admin-icon-descr">{$panelstrings.op2d}</dd>

	{if function_exists('plugin_commentcenter_editor')}
	<dt class="admin-mainmenu-item">
		<img src="{$smarty.const.ADMIN_DIR}imgs/commentcenter.png" class="alignleft" alt="{$panelstrings.op9}"
		title="{$panelstrings.op9}">
		<a href="admin.php?p=entry&action=commentcenter">{$panelstrings.op9}</a>
	</dt>
	<dd class="admin-icon-descr">{$panelstrings.op9d}</dd>
	{/if}

	<dt class="admin-mainmenu-item">
		<img src="{$smarty.const.ADMIN_DIR}imgs/uploader.png" class="alignleft" alt="{$panelstrings.op7}"
		title="{$panelstrings.op7}">
		<a href="admin.php?p=uploader">{$panelstrings.op7}</a>
	</dt>
	<dd class="admin-icon-descr">{$panelstrings.op7d}</dd>

	<dt class="admin-mainmenu-item">
		<img src="{$smarty.const.ADMIN_DIR}imgs/widgets.png" class="alignleft" alt="{$panelstrings.op3}"
		title="{$panelstrings.op3}">
		<a href="admin.php?p=widgets">{$panelstrings.op3}</a>
	</dt>
	<dd class="admin-icon-descr">{$panelstrings.op3d}</dd>

	<dt class="admin-mainmenu-item">
		<img src="{$smarty.const.ADMIN_DIR}imgs/plugins.png" class="alignleft" alt="{$panelstrings.op4}"
		title="{$panelstrings.op4}">
		<a href="admin.php?p=plugin">{$panelstrings.op4}</a>
	</dt>
	<dd class="admin-icon-descr">{$panelstrings.op4d}</dd>

	<dt class="admin-mainmenu-item">
		<img src="{$smarty.const.ADMIN_DIR}imgs/themes.png" class="alignleft" alt="{$panelstrings.op8}"
		title="{$panelstrings.op8}">
		<a href="admin.php?p=themes">{$panelstrings.op8}</a>
	</dt>
	<dd class="admin-icon-descr">{$panelstrings.op8d}</dd>

	<dt class="admin-mainmenu-item">
		<img src="{$smarty.const.ADMIN_DIR}imgs/config.png" class="alignleft" alt="{$panelstrings.op5}"
		title="{$panelstrings.op5}">
		<a href="admin.php?p=config">{$panelstrings.op5}</a>
	</dt>
	<dd class="admin-icon-descr">{$panelstrings.op5d}</dd>

	<dt class="admin-mainmenu-item">
		<img src="{$smarty.const.ADMIN_DIR}imgs/maintain.png" class="alignleft" alt="{$panelstrings.op6}"
		title="{$panelstrings.op6}">
		<a href="admin.php?p=maintain">{$panelstrings.op6}</a>
	</dt>
	<dd class="admin-icon-descr">{$panelstrings.op6d}</dd>

</dl>
