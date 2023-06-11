{include file="header.tpl"}
	
			<div id="main">
				

			<div class="entry">
				<h2 class="title">{$subject}</h2>
				<div class="body">
				
				{if isset($rawcontent) and $rawcontent} {$content}
				{else}	{include file=$content}{/if}
				
				</div>
			</div>
			
			</div>
			
			{include file="widgets.tpl"}
			
{include file="footer.tpl"}



