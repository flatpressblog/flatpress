<h2>{$plang.head}</h2>
<p>{$plang.description1}</p>

{include file="shared:errorlist.tpl"}


{html_form}


	<h3>{$plang.mode}</h3>
	<dl>
		<dt><label><input type="radio" name="mode" value="0" {if $pconfig.mode == 0 }checked=checked{/if}> 
						{$plang.auto} 	</label>	</dt>
						<dd>{$plang.autodescr}</dd>
		<dt><label><input type="radio" name="mode" value="1" {if $pconfig.mode==1}checked=checked{/if}> 
						 {$plang.pathinfo}	</label> </dt>
						<dd>{$plang.pathinfodescr}</dd>
		<dt><label><input type="radio" name="mode" value="2" {if $pconfig.mode==2}checked=checked{/if}> 
						 {$plang.httpget} 	</label>	</dt>
						<dd>{$plang.httpgetdescr}</dd>
		<dt><label><input type="radio" name="mode" value="3" {if $pconfig.mode==3}checked=checked{/if}> 
						 {$plang.pretty}	</label>	</dt>
						<dd>{$plang.prettydescr}</dd>
	</dl>

	<div class="buttonbar">
		<input type="submit" name="saveopt" value="{$plang.saveopt}">
	</div>


	{if not function_exists('hidde_input_field')} {* If the FlatPress Protect plugin is deactivated, the .htaccess file can be edited and saved. *}
		<p class="alignright">
			<a class="hint externlink" href="{$lang.admin.plugin.prettyurls.wiki_nginx}" target="_blank">{$lang.admin.plugin.prettyurls.nginx}</a>
		</p>
		<h3>{$plang.htaccess}</h3>

		<p>{$plang.description2}</p>
		<p>
		<textarea id="htaccess" name="htaccess" 
		{if $cantsave}readonly="readonly" {/if}cols="70" rows="16">{$htaccess|escape:'html'}</textarea>
		</p>

		<div class="buttonbar">
		{if $cantsave}
			<p><em>{$plang.cantsave}</em></p>
		{else}
			<p>{$lang.admin.plugin.prettyurls.location}</p>
			<input type="submit" name="htaccess-submit" value="{$plang.submit}">
		{/if}
		</div>
	{/if}

{/html_form}
