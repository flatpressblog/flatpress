{include file=plugin:commentcenter/header}
{html_form}
<h2>{$plang.configure}</h2>
<p>{$plang.desc_conf}</p>

<dl class="option-set">
	<dt><label for="log_all">{$plang.log_all}</label></dt>
	<dd>
		<div class="form-check">
			<input class="form-check-input" type="checkbox" name="log_all" id="log_all"{if $pl_conf.log_all} checked="checked"{/if}>
			<label class="form-check-label" for="log_all">
				{$plang.log_all_long}
			</label>
		</div>
	</dd>

	<dt><label for="email_alert">{$plang.email_alert}</label></dt>
	<dd>
		<div class="form-check">
			<input type="checkbox" class="form-check-input" name="email_alert" id="log_all"{if $pl_conf.email_alert} checked="checked"{/if} >
			<label class="form-check-label" for="log_all">
				{$plang.email_alert_long}
			</label>
		</div>
	</dd>
</dl>

<h2>{$plang.akismet}</h2>
<dl class="option-set">
	<div class="form-check">
		<input type="checkbox" class="form-check-input" name="akismet_check" id="akismet_check"{if $pl_conf.akismet_check} checked="checked"{/if} >
		<label class="form-check-label" for="log_all">
			{$plang.akismet_use}
		</label>
	</div>
	<dt class="akismet_opts"><label for="akismet_key">{$plang.akismet_key}</label></dt>
	<dd class="akismet_opts">
		<input type="text" class="form-control" name="akismet_key" id="akismet_key" value="{$pl_conf.akismet_key}" /><br />
		{$plang.akismet_key_long}
	</dd>
	<dt class="akismet_opts"><label for="akismet_url">{$plang.akismet_url}</label></dt>
	<dd class="akismet_opts">
		<input type="text" class="form-control" name="akismet_url" id="akismet_url" value="{$pl_conf.akismet_url}" /><br />
		{$plang.akismet_url_long|sprintf:$smarty.const.BLOG_BASEURL}
	</dd>
</dl>

<div class="buttonbar">
	{html_submit name="configure" class="btn btn-primary" id="configure" value=$plang.save_conf}
</div>

{/html_form}
{include file=plugin:commentcenter/footer}