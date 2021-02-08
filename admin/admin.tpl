<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{$flatpress.title}{$pagetitle}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$flatpress.charset}" />
	<meta name="viewport" content="width=device-width, user-scalable=no">
	<link href="https://fonts.googleapis.com/css?family=Nunito&display=swap" rel="stylesheet"> 
	<link rel="stylesheet" href="{$smarty.const.BLOG_BASEURL}/fp-includes/bootstrap/css/bootstrap.min.css"> 
	<script src="{$smarty.const.BLOG_BASEURL}/fp-includes/jquery/jquery.min.js"></script>
	<link rel="stylesheet" href="{$smarty.const.BLOG_BASEURL}/admin/res/admin.css">
	<link rel="stylesheet" href="{$smarty.const.BLOG_BASEURL}/fp-includes/themify-icons/themify-icons.css"> 
	<script src="{$smarty.const.BLOG_BASEURL}/admin/res/admin.js" ></script>
	<link rel="stylesheet" href="{$smarty.const.BLOG_BASEURL}/admin/res/sceditor/themes/default.min.css" />
</head>
{if isset($action)}
<body class="{"admin-$panel-$action"|tag:admin_body_class}">
{else}
<body class="{"admin-$panel"|tag:admin_body_class}">
{/if}
	<nav class="navbar navbar-dark flex-md-nowrap top-nav top-color">
	<div class="container-fluid">
		<div class="row master-row top-row mobile_menu_hide">
			<div class="col-lg-6"><a href="#" onclick="mobile_open_button()"><span class="ti-menu mobile-menu" style="color: #fff;"></span></a><h3><span class="ti-arrow-circle-right"></span> {$panelstrings.head}</h3></div>
			<div class="col-lg-6 top-right-bar">
			    <ul class="navbar-nav ml-auto">
					<li class="nav-item">
					<a class="nav-link" href="login.php?do=logout">
						<span class="d-lg-inline small"><span class="ti-power-off"></span><span class="top_menu_item"> {$logout}</span></span>
					</a>
					</li>
					<li class="nav-item">
					<a class="nav-link" target="_blank" href="https://wiki.flatpress.org/">
						<span class="d-lg-inline small"><span class="ti-help-alt"></span><span class="top_menu_item"> {$help_top}</span></span>
					</a>
					</li>
					<li class="nav-item">
					<a class="nav-link" href="admin.php?p=config">
						<span class="d-lg-inline small"><span class="ti-user"></span><span class="top_menu_item"> {$username}</span></span>
					</a>
					</li>
					<li class="nav-item">
					<a class="nav-link" href="{$smarty.const.BLOG_BASEURL}">
						<span class="d-lg-inline small"><span class="ti-home"></span><span class="top_menu_item"> {$blog}</span></span>
					</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	</nav>
	<div class="top-background top-color shadow mobile_menu_hide"></div>
	<div class="container-fluid">
		<div class="row master-row">
			<nav class="bg-light sidebar left-sidebar" id="sidebar">
				<a href="admin.php">
					<div class="admin-logo">
						<img src="{$smarty.const.BLOG_BASEURL}/admin/res/fp-logo.png">
					</div>
				</a>
				<div class="admin-logo-border"></div>
				<div class="sidebar-sticky">
					<ul class="nav flex-column">
						{foreach from=$menubar item=tab}
						{if isset($tab) && isset($panel) && $tab eq $panel}
						
						<li id="admin-{$tab}" class="nav-item">
							<a id="admin-link-{$tab}" class="admin-tab-current nav-link active" href="{$smarty.const.BLOG_BASEURL}admin.php?p={$tab}">
								{$lang.admin.panels[$tab]|default:$tab}
							</a>
						</li>
						{if isset($submenu)}
						<ul class="nav flex-column submenu">
							{foreach from=$submenu key=subtab item=item}
							{if isset($item)}
							<li id="admin-{$panel}-{$subtab}" class="nav-item">
								<a class="nav-link{if isset($action) && isset($subtab) && $action == $subtab} sub-active{/if}"
									href="{$smarty.const.BLOG_BASEURL}admin.php?p={$panel}&amp;action={$subtab}">
									<span class="ti-arrow-circle-right"></span>
								{$lang.admin[$panel].submenu[$subtab]|default:$subtab}
								</a>
							</li>
							{/if}
							{/foreach}
						</ul>
						{/if}
						{else}
						<li id="admin-{$tab}" class="nav-item">
							<a id="admin-link-{$tab}" class="nav-link" href="{$smarty.const.BLOG_BASEURL}admin.php?p={$tab}">
								{$lang.admin.panels[$tab]|default:$tab}
							</a>
						</li>
						{/if}
						{/foreach}
						<li id="close-button">
							<div class="admin-logo-border"></div>
							<a class="nav-link" onclick="mobile_close_button()">
								<div class="btn btn-primary">
									<span class="ti-close"></span> 
									{$close}
								</div>
							</a>
						</li>
					</ul>
					<script>generate_menu_icons()</script>
				</div>
			</nav>
			<main role="main" class="container-fluid mobile_menu_hide">		
					{page}
							<div class="body">{controlpanel}</div>
					{/page}
					</div>
					<div id="footer" class="mobile_menu_hide">
						{action hook=wp_footer}
						<p>
						{$footer} <a href="http://www.flatpress.org/">FlatPress</a>. 
						</p>
					</div> <!-- end of #footer -->
			</main>
	</div>
</body>
</html>


