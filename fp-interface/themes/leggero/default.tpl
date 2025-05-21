{include file=header.tpl}
	
			<div id="main">
				

			<div class="entry">
				<h3 class="title">{$subject}</h3>
				<div class="body">
				
				{if isset($rawcontent) and $rawcontent} {$content}
				{else}	{include file=$content}{/if}
				
				</div>
			</div>
			
			</div>
			
			{include file=widgets.tpl}
			
{include file=footer.tpl}



