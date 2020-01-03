{include file=plugin:commentcenter/header}
{html_form}
<h2>{$plang.app_title}</h2>
<p>{$plang.app_desc}</p>
{assign var="fetch" value="confirm"}
{include file=plugin:commentcenter/listcomments}
{if $use_akismet}
<h3>{$plang.app_akismet}</h3>
<p>{$plang.app_spamdesc}</p>
{assign var="fetch" value="akismet"}
{include file=plugin:commentcenter/listcomments}
<p><input type="checkbox" name="submitham" id="submitham" checked="checked" />
<label for="submitham">{$plang.app_hamsubmit}</label></p>
{/if}
{if $other}
<h3>{$plang.app_other}</h3>
{assign var="fetch" value="denided"}
{include file=plugin:commentcenter/listcomments}
{/if}
<div class="buttonbar">
	{html_submit name="mpubcomm" id="mpubcomm" value=$plang.app_pselected}
	{html_submit name="mdelcomm" id="mdelcomm" value=$plang.app_dselected}
</div>
{/html_form}
