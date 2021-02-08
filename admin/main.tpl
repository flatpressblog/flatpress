
	<div id="admin-content">
	{if isset($action)}
		{include file=$admin_resource|default:"admin:$panel/$action"}
	{else}
		{include file=$admin_resource|default:"admin:$panel"}
	{/if}
	</div>
