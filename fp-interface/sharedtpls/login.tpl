
<form id="login" method="post" action="{$smarty.const.BLOG_BASEURL}login.php" enctype="multipart/form-data">
	<div class="input-group">
        <input placeholder="{$lang.login.user}" class="input_gray mb-0 mt-3 form-control {$error.user|notempty:'field-error'}" type="text" name="user" id="user" {if $smarty.post.user}value="{$smarty.post.user|wp_specialchars:true}"{/if} class="form-control w-100 mw-100" />
      </div>
		<div class="input-group">
        <input placeholder="{$lang.login.pass}" type="password" class="input_gray form-control mt-2 {$error.pass|notempty:'field-error'}" name="pass" id="pass" class="form-control w-100 mw-100 mt-3"/>
      </div>
	<div class="buttonbar text-center">
	<input type="submit" name="submit" id="submit" value="{$lang.login.submit}" class="btn btn-primary shadow mt-3" />
	{* <input type="submit" name="forgot" id="forgot" value="{$lang.login.forgot}" /> *}
	</div>
</form>
