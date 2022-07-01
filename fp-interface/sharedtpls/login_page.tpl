<!DOCTYPE html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>{$flatpress.title}{$pagetitle}</title>
  <link href="{$smarty.const.BLOG_BASEURL}fp-includes/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="{$smarty.const.BLOG_BASEURL}admin/res/admin.css" rel="stylesheet">
  <link href="{$smarty.const.BLOG_BASEURL}fp-includes/themify-icons/themify-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container h-100">
    <div class="row justify-content-center h-100 align-items-center">
      <div class="col-lg-6">
      {include file=shared:admin_errorlist.tpl}
        <div class="card o-hidden border-0 shadow-lg my-5 shadow mx-auto login">
          <div class="card-body p-0 border rounded">
                <div class="p-5">
                  <div class="login_logo text-center">
                    <img src="{$smarty.const.BLOG_BASEURL}admin/res/fp-logo.png" class="img-fluid">
                  </div>
                  {if isset($rawcontent)} 
                    {$content}
                  {else}	
                    {include file=$content}
                  {/if}
                </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
