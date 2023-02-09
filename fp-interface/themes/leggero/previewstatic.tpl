			{static content=$entry}
			<div class="entry">
				<h2 class="entry-title">{$subject|tag:the_title}</h2>
				<p class="date">{$lang.staticauthor.published_by} {$author} {$lang.staticauthor.on} {$date|date_format_daily} </p>
				{$content|tag:the_content}
			</div>
			{/static}
