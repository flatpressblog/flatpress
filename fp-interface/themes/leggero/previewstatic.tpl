			{static content=$entry}
			<div class="entry">
				<h3>{$subject}</h3>
				<p class="date">Published by {$author} on {$date|date_format:"%A, %B %e, %Y - %H:%M:%S"} </p>
				{$content}
			</div>
			{/static}

