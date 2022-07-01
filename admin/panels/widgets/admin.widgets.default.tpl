{include file='shared:admin_errorlist.tpl'}

<!-- Jquery and Jqueryui (For widgets) -->
<script src="{$smarty.const.BLOG_BASEURL}/admin/includes/jquery/3.6/jquery-3.6.0.min.js"></script>
<script src="{$smarty.const.BLOG_BASEURL}/admin/includes/jqueryui/1.13.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="{$smarty.const.BLOG_BASEURL}/admin/includes/jqueryui/1.13.1/jquery-ui.min.css">
	<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card shadow mb-4">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head}</h6>
				</div>
				<div class="card-body">
					<p>{$panelstrings.descr}</p>
				</div>
			</div>
		</div>
	</div>

{html_form id="admin-widgets-default"}

		<div class="row">
            <div class="col-xl-4 col-lg-5">
              <div class="card shadow mb-4">
                <div class="card-header">
                  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.availwdgs}</h6>
                </div>
                <div class="card-body">
					<div id="widget-trashcan">
						<span class="ti-trash"></span> {$panelstrings.trashcan}
					</div>
					<div id="available-widgets">
					<ul>
					{foreach from=$fp_registered_widgets key=widgetid item=widget}
						<li class="widget-class widget-id-{$widgetid}"> 
							{* those are actually dummies just to have two inputs ready, but they might come handy *}
							<input class="widget-id" type="hidden" name="avalwidg[]" value="{$widgetid}" />
							{if $widget.nparams > 0}
							{* class is for javascript: this input will be converted into a type="text" :) *}
							<input class="textinput" style="float:right" type="hidden" />
							{/if} 
							<p><span class="ti-move"></span> {$widget.name}</p> 
						</li>
					{/foreach}
					</ul>

					<div class="buttonbar text-center">
					<input type="submit" name="save" class="btn btn-primary" value="{$panelstrings.submit}" />
					</div>

					</div>
                </div>
              </div>
            </div>
			<div class="col-xl-8 col-lg-7">
              <div class="card shadow mb-4">
                <div class="card-header">
                  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.themewdgs}</h6>
                </div>
                <div class="card-body widgetset_theme">
					{$panelstrings.themewdgsdescr}
					<div id="admin-widgetset-list">
						</br>
						<ul>
						{assign var=counter value=0}
						{foreach from=$widgetlist key=widgetset item=widgetarr}
						{if ($counter%2)==0} 
							<div class="row justify-content-center">
						{/if}
						<div class="col-xl-5 col-lg-5">
							<li class="admin-widgetset">
								<h3 class="widgetset-name"> 
										{$panelstrings.stdsets[$widgetset]|default:$widgetset} 
								</h3>

								<ul id="widgetsetid-{$widgetset}">
								{foreach from=$widgetarr item=widget}
									{if isset($widget.class)}
										{assign var=widgetclass value=$widget.class}
									{else}
										{assign var=widgetclass value=""}
									{/if}
									{if isset($widget.params)}
										{assign var=widgetparams value=$widget.params}
									{else}
										{assign var=widgetparams value=""}
									{/if}
									<li class="widget-instance widget-id-{$widget.id} {if isset($widget.class)}{$widget.class}{/if}">
										<input class="widget-id" type="hidden" name="widgets[{$widgetset}][]" 
										value="{$widget.id}{$widgetparams}" />
										{if !empty($widgetparams)}
										{* this will be hooked from javascript *}
										<input class="textinput" style="float:right"  
											type="text" value="{$widgetparams}"/>
										{/if} 
										<p> <span class="ti-move"></span> {$widget.name} </p>  
									</li> 
								{foreachelse}
									<li class="widget-placeholder"><span class="ti-layers"></span> Drop here </li>
								{/foreach}
								</ul>
							</li>
						</div>
						{if ($counter%2)!=0} 
							</div>
						{/if}
						{assign var=counter value=$counter+1}
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
									value="{$widget.id}{if isset($widget.params)}:{$widget.params}{/if}" />
									{if isset($widget.params)}
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
						<div class="buttonbar text-center">
						{html_submit name="save" id="save" class="btn btn-primary" value=$panelstrings.submit}
						</div>
                </div>
              </div>
            </div>
          </div>
{/html_form}

<script src="{$smarty.const.BLOG_BASEURL}admin/panels/widgets/admin.widgets.js"></script>