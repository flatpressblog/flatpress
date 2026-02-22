{include file="header.tpl"}

		<div id="main">

		{entry_block}

			{entry}
			{include file='entry-default.tpl'}
			{/entry}

			{capture assign='fp_nextpage'}{strip}{nextpage}{/strip}{/capture}
			{capture assign='fp_prevpage'}{strip}{prevpage}{/strip}{/capture}
			{if $fp_nextpage != '' || $fp_prevpage != ''}
			<div class="navigation">
				{$fp_nextpage nofilter}{$fp_prevpage nofilter}
			</div>
			{/if}

		{/entry_block}

		</div>

		{include file="widgets.tpl"}

{include file="footer.tpl"}
