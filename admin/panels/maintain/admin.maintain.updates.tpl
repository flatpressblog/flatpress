{include file=shared:admin_errorlist.tpl}

<div class="row">
<div class="col-lg-6 mb-4">
  <div class="card shadow mb-4">
	<div class="card-header">
	  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head}</h6>
	</div>
	<div class="card-body">
		{$panelstrings.list|sprintf:$smarty.const.SYSTEM_VER:$sfweb:$updates.stable:$fpweb:$updates.unstable}	
	</div>
  </div>
</div>
<div class="col-lg-6 mb-4">
  <div class="card shadow mb-4">
	<div class="card-header">
	  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.notice}</h6>
	</div>
	<div class="card-body">
		{if $updates.notice}
		<p>{$updates.notice}</p>
		{else}
			<p>{$panelstrings.no_news}</p>
		{/if}
		
	</div>
  </div>
</div>
</div>