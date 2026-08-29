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
	<dl id="prettyurls-mode-capabilities" data-pathinfo-probe-url="{$capability_probe_pathinfo_url|escape:'html'}" data-get-probe-url="{$capability_probe_get_url|escape:'html'}" data-pretty-probe-url="{$capability_probe_pretty_url|escape:'html'}">
		<dt>
			<label>
				<input type="radio" name="mode" value="0"{if $pconfig.mode == 0} checked=checked{/if}> {$plang.auto}
			</label>
		</dt>
		<dd>{$plang.autodescr}</dd>
		<dt>
			<label>
				<input type="radio" name="mode" value="1"{if not $can_pathinfo} disabled="disabled"{/if}{if $pconfig.mode == 1} checked=checked{/if}> {$plang.pathinfo}
				<span data-prettyurls-capability="pathinfo"{if not isset($detected_pathinfo) or not $detected_pathinfo} hidden="hidden"{/if}><img src="{$check_icon_url}" alt="" width="12" height="12" style="vertical-align:middle;"></span>
			</label>
		</dt>
		<dd>{$plang.pathinfodescr}</dd>
		<dt>
			<label>
				<input type="radio" name="mode" value="2"{if not $can_get} disabled="disabled"{/if}{if $can_get and $pconfig.mode == 2} checked=checked{/if}> {$plang.httpget}
				<span data-prettyurls-capability="get"{if not isset($detected_get) or not $detected_get} hidden="hidden"{/if}><img src="{$check_icon_url}" alt="" width="12" height="12" style="vertical-align:middle;"></span>
			</label>
		</dt>
		<dd>{$plang.httpgetdescr}</dd>
		<dt>
			<label>
				<input type="radio" name="mode" value="3"{if $pconfig.mode == 3} checked=checked{/if}> {$plang.pretty}
				<span data-prettyurls-capability="pretty"{if not isset($detected_pretty) or not $detected_pretty} hidden="hidden"{/if}><img src="{$check_icon_url}" alt="" width="12" height="12" style="vertical-align:middle;"></span>
			</label>
		</dt>
		<dd>{$plang.prettydescr}</dd>
	</dl>
	<script nonce="{$random_hex|escape:'html'}" src="{$capability_probe_script_url|escape:'html'}" defer></script>

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
