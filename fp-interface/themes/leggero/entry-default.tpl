	<div itemscope itemtype="http://schema.org/BlogPosting" id="{$id}" class="entry {$date|date_format:"y-%Y m-%m d-%d"}">
				{* 	using the following way to print the date, if more 	*}
				{*	than one entry have been written the same day,		*}
				{*	 the date will be printed only once 				*}

		{$date|date_format_daily:"<h2 class=\"date\">`$fp_config.locale.dateformat`</h2>"}

				<h2 itemprop="name" class="entry-title">
					<a href="{$id|link:post_link}">
					{$subject|tag:the_title}
					</a>
				</h2>
				{include file="shared:entryadminctrls.tpl"}

				<div itemprop="articleBody">
				{$content|tag:the_content}
				</div>

				<ul class="entry-footer">

					<li class="entry-info">{$lang.entryauthor.posted_by} <span itemprop="author">{$author}</span> {$lang.entryauthor.at}
					{$date|date_format}

						<span itemprop="articleSection">
							{if ($categories)} {$lang.plugin.categories.in} {$categories|@filed}{/if}
						</span>
					</li>

					<li class="link-comments">
					{if isset($views)}
						<strong>{$views}</strong> {$lang.postviews.views}
					{/if}
					{if !(in_array('commslock', $categories) && !$comments)}
						{if $comments > 0}
							<a href="{$id|link:comments_link}#comments">{$comments|tag:comments_number}</a>
						{else}
							<a href="{$id|link:comments_link}#addcomment">{$comments|tag:comments_number}</a>
						{/if}
					{/if}
					</li>

				</ul>

	</div>
