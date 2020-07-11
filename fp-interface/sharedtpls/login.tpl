
<form id="login" method="post" action="{$smarty.const.BLOG_BASEURL}login.php" enctype="multipart/form-data">
	<div class="input-group shadow">
        <div class="input-group-prepend">
          <div class="input-group-text"><span class="ti-user"></span></div>
        </div>
        <input placeholder="{$lang.login.user}" {$error.user|notempty:'class="field-error form-control input_gray"'} type="text" name="user" id="user" {if $smarty.post.user}value="{$smarty.post.user|wp_specialchars:true}"{/if} class="form-control input_gray" />
      </div>
		<div class="input-group shadow">
        <div class="input-group-prepend">
          <div class="input-group-text"><span class="ti-lock"></span></div>
        </div>
        <input placeholder="{$lang.login.pass}" type="password" {$error.pass|notempty:'class="field-error form-control input_gray"'} name="pass" id="pass" class="form-control input_gray"/>
      </div>
	<div class="buttonbar text-center">
	<input type="submit" name="submit" id="submit" value="{$lang.login.submit}" class="btn btn-primary shadow" />
	{* <input type="submit" name="forgot" id="forgot" value="{$lang.login.forgot}" /> *}
	</div>
</form>
