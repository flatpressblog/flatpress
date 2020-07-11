{include file='shared:admin_errorlist.tpl'}

{html_form}

		<div class="col-xl-12 col-lg-12">
			<div class="card shadow mb-4">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head}</h6>
				</div>
				<div class="card-body">
					<div class="table-responsive table-striped">
						<table id="plugin-table" class="table">
							<thead id="plugin-table-head">
								<tr>
									<th>{$panelstrings.name}</th>
									<th class="main-cell">{$panelstrings.description}</th>
									<th>{$panelstrings.author}</th>
									<th>{$panelstrings.version}</th>
									<th>{$panelstrings.action}</th>
								</tr>
							</thead>
							<tbody id="plugin-table-body">
							{foreach from=$pluginlist item=plugin}
							{assign var=inarr value=$plugin|in_array:$enabledlist}
							{$plugin|plugin_getinfo}
							<tr{if $inarr} class="enabled" {/if}>
								<td> {$name} </td>
								<td class="main-cell"> {$description} </td>
								<td> {$author} </td>
								<td> {$version} </td>
								<td> {if $inarr} 
									<a class="link-disable"
									href="{$action_url|cmd_link:disable:$plugin}"> 
										{$panelstrings.disable}
									</a> 
									{else}
									<a class="link-enable" 
									href="{$action_url|cmd_link:enable:$plugin}"> 
										{$panelstrings.enable}
									</a> 
									{/if}
								</td>
							</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>	
{/html_form}
