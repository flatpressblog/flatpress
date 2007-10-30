
	<ul id="admin-tabmenu">
		{foreach from=$menubar item=tab}
		{if $tab eq $panel}
		
		<li>
			<a id="admin-tab-current" href="{$smarty.const.BLOG_BASEURL}admin.php?p={$tab}">
				{$lang.admin.panels[$tab]|default:$tab}
			</a>
		</li>
		{else}
		<li>
			<a href="{$smarty.const.BLOG_BASEURL}admin.php?p={$tab}">
				{$lang.admin.panels[$tab]|default:$tab}
			</a>
		</li>
		{/if}
		{/foreach}
	</ul>
	
	{if $submenu}
	<ul id="admin-submenu">
		{foreach from=$submenu key=subtab item=item}
		{if $item}
		<li>
			<a {if $action == $subtab}class="active" {/if}
				href="{$smarty.const.BLOG_BASEURL}admin.php?p={$panel}&amp;action={$subtab}">
			{$lang.admin[$panel].submenu[$subtab]|default:$subtab}
			</a>
		</li>
		{/if}
		{/foreach}
	</ul>
	{/if}
	
	<div id="admin-content">
	{include file=$admin_resource|default:"admin:$panel/$action"}
	
	</div>
