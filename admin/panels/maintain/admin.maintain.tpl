<div class="maintain">
<div class="row">
<div class="col-lg-6 mb-4">
  <div class="card shadow mb-4">
	<div class="card-header">
	  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head}</h6>
	</div>
	<div class="card-body">
		{include file=shared:admin_errorlist.tpl}
		{if $files}
		<p>{$panelstrings.chmod_info}</p>
		<p><a href="admin.php?p=maintain">{$panelstrings.opt0}</a></p>
		<ul> 
		{foreach from=$files item=file}
			<li>{$file}</li>
		{/foreach}
		</ul>
		<p><a href="admin.php?p=maintain">{$panelstrings.opt0}</a></p>
		{elseif $phpinfo}
		<p><a href="admin.php?p=maintain">{$panelstrings.opt0}</a></p>
		{$phpinfo}
		<p><a href="admin.php?p=maintain">{$panelstrings.opt0}</a></p>
		{else}
		<p>{$panelstrings.descr}</p>
		<ul>
		<li><a href="{$action_url|cmd_link:do:rebuild}"><span class="ti-reload"></span> {$panelstrings.opt1}</a></li>
		<li><a href="{$action_url|cmd_link:do:purgetplcache}"><span class="ti-paint-bucket"></span> {$panelstrings.opt2}</a></li>
		<li><a href="{$action_url|cmd_link:do:restorechmods}"><span class="ti-lock"></span> {$panelstrings.opt3}</a></li>
		<li><a href="{$action_url|cmd_link:do:phpinfo}"><span class="ti-info-alt"></span> {$panelstrings.opt4}</a></li>
		<li><a href="{$panel_url|action_link:updates}"><span class="ti-package"></span> {$panelstrings.opt5}</a></li>
		</ul>
		{/if}
	</div>
  </div>
</div>
<div class="col-lg-6 mb-4">
  <div class="card shadow mb-4">
	<div class="card-header">
	  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.help}</h6>
	</div>
	<div class="card-body">
		<p>{$panelstrings.useful_links}</p>
		<ul>
			<li><a href="https://www.flatpress.org/" target="_blank"><span class="ti-home"></span> {$panelstrings.fp_home}</a></li>
			<li><a href="https://www.flatpress.org/blog.php?x=entry" target="_blank"><span class="ti-layers-alt"></span> {$panelstrings.fp_blog}</a></li>
			<li><a href="http://forum.flatpress.org/" target="_blank"><span class="ti-comments"></span> {$panelstrings.fp_forums}</a></li>
			<li><a href="http://wiki.flatpress.org/" target="_blank"><span class="ti-help-alt"></span> {$panelstrings.fp_wiki}</a></li>
		</ul>
	</div>
  </div>
</div>
</div>
</div>