	<div itemscope itemtype="http://schema.org/BlogPosting" id="{$id}" class="entry {$date|date_format:"y-%Y m-%m d-%d"}">
				{*    using the following way to print the date, if more     *}
				{*    than one entry have been written the same day,         *}
				{*    the date will be printed only once                     *}

		{$date|date_format_daily:"<h2 class=\"date\">`$fp_config.locale.dateformat`</h2>"}

				<h2 itemprop="name" class="entry-title">
					<a href="{if !(in_array('commslock', $categories) && !$comments)}{$id|link:comments_link}{else}{$id|link:post_link}{/if}">
					{$subject|tag:the_title}
					</a>
				</h2>
				{include file="shared:entryadminctrls.tpl"}

				<div itemprop="articleBody">
					{if $seo_desc}

					<br>
					<!-- BOF SEO Metagtag info description -->
					<fieldset><legend>{$lang.plugin.seometataginfo.introduction}</legend><b>&rArr;</b> {$seo_desc|escape}</fieldset>
					<!-- EOF SEO Metagtag info description -->

					{/if}

				{$content|tag:the_content}
				</div>

				<ul class="entry-footer">

					<li class="entry-info">{$lang.entryauthor.posted_by} <span itemprop="author">{$author}</span> {$lang.entryauthor.at}
					{$date|date_format:"`$fp_config.locale.timeformat`"}

						<span itemprop="articleSection">
							{assign var="__filed_cats" value=$categories|@filed}
							{if $__filed_cats} {$lang.plugin.categories.in} {$__filed_cats}{/if}
						</span>
					</li>

					<li class="link-comments">

					{if function_exists('plugin_postviews_calc')}
						<strong>{views}</strong> {$lang.postviews.views}
					{/if}

					{if !(in_array('commslock', $categories) && !$comments)}
						<a href="{$id|link:comments_link}{if $comments > 0}#comments{else}#addcomment{/if}">{$comments|tag:comments_number}</a>
					{/if}
					</li>

				</ul>

	</div>
