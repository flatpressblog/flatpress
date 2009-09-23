			{entry content=$entry}
			<div class="entry">
				<h3>{$subject|tag:the_title}</h3>
				<p class="date">Published by {$author} on {$date|date_format:$fp_config.locale.dateformat} </p>
				{$content|tag:the_content}
			</div>
			{/entry}

