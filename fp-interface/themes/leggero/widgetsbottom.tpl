		{capture assign='widgets_bottom'}
			{widgets pos=bottom}
				{assign var=w_content value=$content|default:''|trim}
				{if $w_content != ''}

				<div id="{$id}">
					{$content}
				</div>
				{/if}
			{/widgets}
		{/capture}
		{if $widgets_bottom|trim != ''}

			<!-- BOF bottom menu -->
			<div id="columnbottom">
				{$widgets_bottom nofilter}
			</div>
			<!-- EOF bottom menu -->
		{/if}
