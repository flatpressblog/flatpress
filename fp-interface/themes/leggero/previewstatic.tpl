			{static content=$entry}
			<div class="entry">
				<h3>{$subject}</h3>
				<p class="date">Published by {$author} on {$date|date_format:$fp_config.locale.dateformat} </p>
				{$content}
			</div>
			{/static}

