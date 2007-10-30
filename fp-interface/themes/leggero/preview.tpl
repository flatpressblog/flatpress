			{entry content=$entry}
			<div class="entry">
				<h3>{$subject|tag:the_title}</h3>
				<p class="date">Published by {$author} on {$date|date_format:"%A, %B %e, %Y - %H:%M:%S"} </p>
				{$content|tag:the_content}
			</div>
			{/entry}

