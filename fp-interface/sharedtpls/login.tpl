{validate id="userid" message=$lang.login.error.user append="error"}
{validate id="pwd" message=$lang.login.error.pass append="error"}
{validate id="password" message=$lang.login.error.match append="error"}

{include file=shared:errorlist.tpl}

<form id="login" method="post" action="{$smarty.server.PHP_SELF}?redirect={$smarty.request.redirect}" enctype="multipart/form-data">
	<fieldset><legend>{$lang.login.fieldset1}</legend>
	<p><label for="user">{$lang.login.user}</label><br />
	<input {$error.user|notempty:'class="field-error"'} type="text" name="user" id="user" /></p>
	<p><label for="pass">{$lang.login.pass}</label><br />
	<input type="password" {$error.pass|notempty:'class="field-error"'} name="pass" id="pass" /></p>
	</fieldset>
	
	<div class="buttonbar">
	<input type="submit" name="submit" id="submit" value="{$lang.login.submit}" />
	{* <input type="submit" name="forgot" id="forgot" value="{$lang.login.forgot}" /> *}
	</div>
	
</form>

