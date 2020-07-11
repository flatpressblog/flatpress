{include file='shared:admin_errorlist.tpl'}
{static_block}
{html_form}
	<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card shadow mb-4">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head}</h6>
				</div>
				<div class="card-body">
					<div class="table-responsive table-striped">
						<table class="entrylist table">
						<thead><tr>{*<th>{$panelstrings.sel}</th>*}
						<th>{$panelstrings.name}</th>
						<th class="main-cell">{$panelstrings.title}</th>
						<th>{$panelstrings.author}</th>
						<th>{$panelstrings.action}</th></tr></thead>
						<tbody>
						{static}
						<tr>
						{*<td><input type="checkbox" /></td>*}
						<td>{$id}</td>
						<td class="main-cell">
						<a class="link-general"  
						href="{$panel_url|action_link:write}&amp;page={$id}">
						{$subject|truncate:70}
						</a>
						</td>
						<td>{$author}</td>
						<td>
						<a class="link-general" 
						href="{$id|link:page_link}">
						<span class="ti-desktop"></span>
						{$panelstrings.act_view}
						</a>
						<a 
						class="link-general" 
						href="{$panel_url|action_link:write}&amp;page={$id}">
						<span class="ti-pencil-alt"></span>
						{$panelstrings.act_edit}
						</a>
						<a class="link-delete" 
						href="{$panel_url|action_link:delete}&amp;page={$id}">
						<span class="ti-trash"></span>
						{$panelstrings.act_del}
						</a>
						</td>

						</tr>

						{/static}
						</tbody></table>
					</div>
				</div>
			</div>
		</div>
	</div>
{/html_form}
{/static_block}


