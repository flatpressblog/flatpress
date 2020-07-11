<!DOCTYPE html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>{$flatpress.title}{$pagetitle}</title>
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
  <link href="{$smarty.const.BLOG_BASEURL}fp-includes/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="{$smarty.const.BLOG_BASEURL}admin/res/admin.css" rel="stylesheet">
  <link href="{$smarty.const.BLOG_BASEURL}fp-includes/themify-icons/themify-icons.css" rel="stylesheet">
</head>
<body class="login-background">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6">
      {include file=shared:admin_errorlist.tpl}
        <div class="card o-hidden border-0 shadow-lg my-5 shadow-lg">
          <div class="card-body p-0">
                <div class="p-5">
					<div class="login_logo text-center">
						<img src="{$smarty.const.BLOG_BASEURL}admin/res/fp-logo.png" class="img-fluid">
					</div>
					{if $rawcontent} {$content}
				{else}	{include file=$content}{/if}
                </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
