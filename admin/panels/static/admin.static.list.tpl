
<h2>{$panelstrings.head}</h2>

<p>{$panelstrings.descr}</p>

{include file="shared:errorlist.tpl"}

{static_block}

{html_form}

<table class="entrylist">

	<thead>
		<tr>
			{*<th>{$panelstrings.sel}</th>*}
			<th scope="col">{$panelstrings.name}</th>
			<th scope="col" class="main-cell">{$panelstrings.title}</th>
			<th scope="col">{$panelstrings.author}</th>
			<th scope="col">{$panelstrings.action}</th>
		</tr>
	</thead>

	<tbody>
	{static}
		<tr>
			{*<td><input type="checkbox"></td>*}
			<td data-label="{$panelstrings.name|escape:'html'}">{$id}</td>
			<td class="main-cell">
				<a class="link-general" href="{$panel_url|action_link:write}&amp;page={$id}">
					{$subject|truncate:70|tag:the_title}
				</a>
			</td>

			<td data-label="{$panelstrings.author|escape:'html'}">{$author}</td>
			<td data-label="{$panelstrings.action|escape:'html'}">
				<a class="link-general" href="{$id|link:page_link}">
					{$panelstrings.act_view}
				</a>
				<a class="link-general" href="{$panel_url|action_link:write}&amp;page={$id}">
					{$panelstrings.act_edit}
				</a>
				<a class="link-delete" href="{$panel_url|action_link:delete}&amp;page={$id}">
					{$panelstrings.act_del}
				</a>
			</td>
		</tr>
	{/static}
	</tbody>

</table>

<div class="buttonbar">
	<p>
		<input type="checkbox" name="naturalsort" id="naturalsort"{if $fp_config.staticlist.naturalsort|default:''} checked{/if}> {$panelstrings.natural}
	</p>
	{html_submit name="save" id="save" value=$panelstrings.submit}
</div>

{/html_form}

{/static_block}
