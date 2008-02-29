<h2>{$panelstrings.head}</h2>
<p>{$panelstrings.descr}</p>

{include file='shared:errorlist.tpl'}


{html_form id="admin-widgets-default"}
	
	<div id="available-widgets">
	<h2>{$panelstrings.availwdgs}</h2>
	
	<div id="widget-trashcan">
		{$panelstrings.trashcan}
	</div>	
	
	<ul>
	{foreach from=$fp_registered_widgets key=widgetid item=widget}
		<li class="widget-class widget-id-{$widgetid}"> 
			{* those are actually dummies just to have two inputs ready, but they might come handy *}
			<input class="widget-id" type="hidden" name="avalwidg[]" value="{$widgetid}" />
			{if $widget.nparams > 0}
			{* class is for javascript: this input will be converted into a type="text" :) *}
			<input class="textinput" style="float:right" type="hidden" />
			{/if} 
			<p>{$widget.name}</p> 
		</li>
	{/foreach}
	</ul>
	
	<div class="buttonbar">
	<input type="submit" name="save" value="{$panelstrings.submit}" />
	</div>

	</div>
	
	<div id="admin-widgetset-list">
	
	<h2>{$panelstrings.themewdgs}</h2>
	<p>{$panelstrings.themewdgsdescr}</p>
	
	<ul>
	{foreach from=$widgetlist key=widgetset item=widgetarr}
	<li class="admin-widgetset">
		<h3 class="widgetset-name"> 
				{$panelstrings.stdsets[$widgetset]|default:$widgetset} 
		</h3>
		
		<ul id="widgetsetid-{$widgetset}">
		{foreach from=$widgetarr item=widget}
			<li class="widget-instance widget-id-{$widget.id} {$widget.class}">
				<input class="widget-id" type="hidden" name="widgets[{$widgetset}][]" 
				value="{$widget.id}{if $widget.params}:{$widget.params}{/if}" />
				{if $widget.params}
				{* this will be hooked from javascript *}
				<input class="textinput" style="float:right"  
					type="text" value="{$widget.params}"/>
				{/if} 
				<p> {$widget.name} </p>  
			</li> 
		{foreachelse}
			<li class="widget-placeholder"> Drop here </li>
		{/foreach}
		</ul>
	</li>
	{/foreach}
	
	
	</ul>
	
	{if $oldwidgetlist}
	
	<h2>{$panelstrings.oldwdgs}</h2>
	<p>{$panelstrings.oldwdgsdescr}</p>
	
	<ul>	
	{foreach from=$oldwidgetlist key=widgetset item=widgetarr}
	<li class="admin-widgetset">
		<h3 class="widgetset-name"> 
				{$panelstrings.stdsets[$widgetset]|default:$widgetset} 
		</h3>
		
		<ul id="widgetsetid-{$widgetset}">
		{foreach from=$widgetarr item=widget}
			<li class="widget-instance widget-id-{$widget.id}">
				<input class="widget-id" type="hidden" name="widgets[{$widgetset}][]" 
				value="{$widget.id}{if $widget.params}:{$widget.params}{/if}" />
				{if $widget.params}
				{* this will be hooked from javascript *}
				<input class="textinput" style="float:right"  
					type="text" value="{$widget.params}"/>
				{/if} 
				<p> {$widget.name} </p>  
			</li> 
		{foreachelse}
			<li class="widget-placeholder"> Drop here </li>
		{/foreach}
		</ul>
	</li>
	{/foreach}
	</ul>

	{/if}
	
	</div>
		
	<div class="buttonbar">
	{html_submit name="save" id="save" value=$panelstrings.submit}
	</div>

{/html_form}
