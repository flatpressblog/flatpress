{include file="header.tpl"}
{include file="widgetstop.tpl"}
				<div id="main">

				{static_block}
				{static}

					<div id="{$id}" class="entry page-{$id}">
						<h2 class="entry-title">{$subject|tag:the_title}</h2>
						<p class="date">
							{if function_exists('plugin_webfonts_head')}

							<i class="fa-solid fa-user" role="img" aria-label="{$lang.staticauthor.published_by}"></i>
							{else}
								{$lang.staticauthor.published_by}
							{/if} {$author}
							{if function_exists('plugin_webfonts_head')}
								&nbsp;&nbsp;<i class="fa-regular fa-calendar-days" role="img" aria-label="{$lang.staticauthor.on}"></i>
							{else}
								{$lang.staticauthor.on}
							{/if}{$date|date_format_daily}
						</p>

						{$content|tag:the_content}

						{if function_exists('plugin_webshare_head') && function_exists('plugin_webfonts_head')}
							{include file="shared:static_webshare.tpl"}
						{/if}

					</div>
				{/static}

				{/static_block}

				</div>

		{include file="widgets.tpl"}
{include file="footer.tpl"}
