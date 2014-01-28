{include file=cpheader.tpl}

		<div id="cpmain">
			

		<div class="entry">
		
		<ul id="admin-small-nav">
			<li><a href="{$smarty.const.BLOG_BASEURL}">Home</a></li>
			<li><a href="{$smarty.const.BLOG_BASEURL}login.php?do=logout">Logout</a></li>
		</ul>
		
		{page}
				<h1 class="title">{$subject}</h1>
				<div class="body">{controlpanel}</div>
		{/page}
		</div>
		
		</div>
	
{include file=footer.tpl}



