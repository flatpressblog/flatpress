			{capture assign='widgets_top'}
				{widgets pos=top}
					{assign var=w_content value=$content|default:''|trim}
					{if $w_content != ''}

					<div id="{$id}">
						{$content}
					</div>
					{/if}
				{/widgets}
			{/capture}
			{if $widgets_top|trim != ''}

				<!-- BOF top menu -->
				<div id="columntop">
					{$widgets_top nofilter}
				</div>
				<!-- EOF top menu -->
			{/if}

