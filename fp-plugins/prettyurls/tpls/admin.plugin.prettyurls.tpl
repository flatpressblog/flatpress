<h2>{$plang.head}</h2>
<p>{$plang.description1}</p>

{if function_exists('fpprotect_harden_prettyurls_plugin')}
<p>{$lang.admin.plugin.prettyurls.fpprotect_is_on}</p>
{/if}

{if not function_exists('fpprotect_harden_prettyurls_plugin')}
<p>{$lang.admin.plugin.prettyurls.fpprotect_is_off}</p>
{/if}

{include file="shared:errorlist.tpl"}

{html_form}

	<h3>{$plang.mode}</h3>
	<dl>
		<dt>
			<label>
				<input type="radio" name="mode" value="0"{if $pconfig.mode == 0} checked=checked{/if}> {$plang.auto}
			</label>
		</dt>
		<dd>{$plang.autodescr}</dd>
		<dt>
			<label>
				<input type="radio" name="mode" value="1"{if not $can_pathinfo} disabled="disabled"{/if}{if $can_pathinfo and $pconfig.mode == 1} checked=checked{/if}> {$plang.pathinfo} {if $can_pathinfo && isset($auto_mode_index) && $auto_mode_index == 1} <img src="{$check_icon_url}" alt="auto" width="12" height="12" style="vertical-align:middle;">{/if}
			</label>
		</dt>
		<dd>{$plang.pathinfodescr}</dd>
		<dt>
			<label>
				<input type="radio" name="mode" value="2"{if not $can_get} disabled="disabled"{/if}{if $can_get and $pconfig.mode == 2} checked=checked{/if}> {$plang.httpget} {if $can_get && isset($auto_mode_index) && $auto_mode_index == 2} <img src="{$check_icon_url}" alt="auto" width="12" height="12" style="vertical-align: middle;">{/if}
			</label>
		</dt>
		<dd>{$plang.httpgetdescr}</dd>
		<dt>
			<label>
				<input type="radio" name="mode" value="3"{if not $can_pretty} disabled="disabled"{/if}{if $can_pretty and $pconfig.mode == 3} checked=checked{/if}> {$plang.pretty} {if $can_pretty && isset($auto_mode_index) && $auto_mode_index == 3} <img src="{$check_icon_url}" alt="auto" width="12" height="12" style="vertical-align: middle;">{/if}
			</label>
		</dt>
		<dd>{$plang.prettydescr}</dd>
	</dl>

	<div class="buttonbar">
		<input type="submit" name="saveopt" value="{$plang.saveopt}">
	</div>

	{if function_exists('fpprotect_harden_prettyurls_plugin')}
		{if not fpprotect_harden_prettyurls_plugin()} {* If the FlatPress Protect plugin option is activated, the .htaccess file can be edited and saved. *}
			<p class="alignright">
				<a class="hint externlink" href="{$lang.admin.plugin.prettyurls.wiki_nginx}" target="_blank">{$lang.admin.plugin.prettyurls.nginx}</a>
			</p>
			<h3>{$plang.htaccess}</h3>

			<p>{$plang.description2}</p>
			<p>
			<textarea id="htaccess" name="htaccess"{if $cantsave} readonly="readonly"{/if} cols="70" rows="16">{$htaccess|escape:'html'}</textarea>
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
	{/if}

{/html_form}
