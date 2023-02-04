			{entry content=$entry}
			<div class="entry">
			{$date|date_format_daily:"<h2 class=\"date\">`$fp_config.locale.dateformat`</h2>"}
				<h3>{$subject|tag:the_title}</h3>
				{$content|tag:the_content}
				<ul class="entry-footer">
					<li class="entry-info">
					<p class="date">{$lang.entryauthor.posted_by} {$author} {$lang.entryauhor.on} {$date|date_format} </p>
					</li>
				</ul>
			</div>
			{/entry}
