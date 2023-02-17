			{static content=$entry}
			<div class="entry">
				<h3>{$subject|tag:the_title}</h3>
				<p class="date">{$lang.staticauthor.published_by} {$author} {$lang.staticauthor.on} {$date|date_format_daily} </p>
				{$content|tag:the_content}
			</div>
			{/static}
