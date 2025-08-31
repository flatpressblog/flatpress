			{entry content=$entry}
			<div class="entry">
			{$date|date_format_daily:"<h6 class=\"date\">`$fp_config.locale.dateformat`</h6>"}
				<h2 class="entry-title">{$subject|tag:the_title}</h2>
				{$content|tag:the_content}
				<ul class="entry-footer">
					<li class="entry-info">
					<p class="date">{$lang.entryauthor.posted_by} {$author} {$lang.entryauthor.at} {$date|date_format:"`$fp_config.locale.timeformat`"} </p>
					</li>
				</ul>
			</div>
			{/entry}
