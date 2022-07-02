{include file=plugin:commentcenter/header}
{html_form}
<h2>{$plang.configure}</h2>
<p>{$plang.desc_conf}</p>
{if !isset($pl_conf)}
	{assign var=pl_conf value=""}
{/if}

<dl class="option-set">
	<dt><label for="log_all">{$plang.log_all}</label></dt>
	<dd>
		<input type="checkbox" name="log_all" id="log_all"{if isset($pl_conf.log_all) and $pl_conf.log_all} checked="checked"{/if} /><br />
		{$plang.log_all_long}
	</dd>

	<dt><label for="email_alert">{$plang.email_alert}</label></dt>
	<dd>
		<input type="checkbox" name="email_alert" id="email_alert"{if isset($pl_conf.email_alert) and $pl_conf.email_alert} checked="checked"{/if} /><br />
		{$plang.email_alert_long}
	</dd>
</dl>

<h2>{$plang.akismet}</h2>
<dl class="option-set">
	<dt><label for="akismet_check">{$plang.akismet_use}</label></dt>
	<dd>
		<input type="checkbox" name="akismet_check" id="akismet_check"{if isset($pl_conf.akismet_check) and $pl_conf.akismet_check} checked="checked"{/if} />
	</dd>
	<dt class="akismet_opts"><label for="akismet_key">{$plang.akismet_key}</label></dt>
	<dd class="akismet_opts">
		{if isset($pl_conf.akismet_key)}
			{assign var=akismet_key value=$pl_conf.akismet_key}
		{else}
			{assign var=akismet_key value=""}
		{/if}
		<input type="text" name="akismet_key" id="akismet_key" value="{$akismet_key}" /><br />
		{$plang.akismet_key_long}
	</dd>
	<dt class="akismet_opts"><label for="akismet_url">{$plang.akismet_url}</label></dt>
	<dd class="akismet_opts">
		{if isset($pl_conf.akismet_url)}
			{assign var=akismet_url value=$pl_conf.akismet_url}
		{else}
			{assign var=akismet_url value=""}
		{/if}
		<input type="text" name="akismet_url" id="akismet_url" value="{$akismet_url}" /><br />
		{$plang.akismet_url_long|sprintf:$smarty.const.BLOG_BASEURL}
	</dd>
</dl>

<div class="buttonbar">
	{html_submit name="configure" id="configure" value=$plang.save_conf}
</div>

{/html_form}
