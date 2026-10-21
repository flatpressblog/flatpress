{include file="shared:errorlist.tpl" escape_messages=true}

<form id="login" method="post" action="{$smarty.const.BLOG_BASEURL|escape:'html'}login.php" enctype="multipart/form-data">

	<input type="hidden" name="csrf_token" value="{$csrf_token|escape:'html'}">

	<fieldset><legend>{$lang.login.fieldset1}</legend>
	<p><label for="user">{$lang.login.user}</label><br>
		<input autofocus {if !empty($error.user)}class="field-error"{/if} type="text" name="user" id="user" value="{$user|default:''|escape:'html'}"></p>
	<p><label for="pass">{$lang.login.pass}</label><br>
	{if isset($error) && isset($error.pass)}
		<input type="password" {$error.pass|notempty:'class="field-error"'} name="pass" id="pass"></p>
	{else}
		<input type="password" name="pass" id="pass"></p>
	{/if}
	</fieldset>

	<div class="buttonbar">
	<input type="submit" name="submit" id="submit" value="{$lang.login.submit}">
	{* <input type="submit" name="forgot" id="forgot" value="{$lang.login.forgot}"> *}
	</div>

</form>
