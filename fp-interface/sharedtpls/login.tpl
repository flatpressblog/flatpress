{include file=shared:errorlist.tpl}

<form id="login" method="post" action="{$smarty.const.BLOG_BASEURL}login.php" enctype="multipart/form-data">
	<fieldset><legend>{$lang.login.fieldset1}</legend>
	<p><label for="user">{$lang.login.user}</label><br />
	<input {$error.user|notempty:'class="field-error"'} type="text" name="user" id="user" {if $smarty.post.user}value="{$smarty.post.user|wp_specialchars:true}"{/if} /></p>
	<p><label for="pass">{$lang.login.pass}</label><br />
	<input type="password" {$error.pass|notempty:'class="field-error"'} name="pass" id="pass" /></p>
	</fieldset>
	
	<div class="buttonbar">
	<input type="submit" name="submit" id="submit" value="{$lang.login.submit}" />
	{* <input type="submit" name="forgot" id="forgot" value="{$lang.login.forgot}" /> *}
	</div>
	
</form>

