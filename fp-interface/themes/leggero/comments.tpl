{include file=header.tpl}

		<div id="main">
		{entry_block}
		{entry}
			{include file=entry-default.tpl}
		{comment_block}
		<ol id="comments">
		{comment}
			<li id="{$id}" {$loggedin|notempty:"class=\"comment-admin\""}>
				
				<strong class='comment-name'>
				{* 
					using this tag combo, the name is displayed as a link only
					if user entered a URL.
					
					Syntax is quite intuitive:
					"if $url is not empty, show $name between a tags, 
					else default fallback on displaying plain $name"
					
				*}
				{$url|notempty:"<a href=\"$url\" title=\"Permalink to $name's comment\">$name</a>"|default:$name}
				</strong>
				
				{include file=shared:commentadminctrls.tpl} {* this shows edit/delete links*}
				
				<p class="date">
				<a href="{$entryid|link:comments_link}#{$id}">{$date|date_format:"%A, %B %e, %Y - %H:%M:%S"}</a>
				</p>
				
				{$content|tag:comment_text}
				
			</li>
		{/comment}
		</ol>
		{/comment_block}

		{/entry}

		
				
			<div class="navigation">
				{nextpage}{prevpage}
			</div>


		{/entry_block}
		
		{include file="shared:comment-form.tpl"}

	
		</div>
		
		{include file=widgets.tpl}
	
		

	<hr />
	
{include file=footer.tpl}
