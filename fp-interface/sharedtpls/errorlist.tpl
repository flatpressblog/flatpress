<div id="errorlist">
		{if isset($error)}
		<ul class="msgs errors">
			{foreach from=$error key=field item=msg}
			<li>
			{if is_numeric($field)}
				{$msg} 
			{else}
				<a href="#{$field}">{$msg}</a>
			{/if}
			</li>
			{/foreach}
		</ul>
		{/if}
		
		{if isset($warnings)}
		<ul class="msgs warnings">
			{foreach from=$warnings key=field item=msg}
			<li>
			{if is_numeric($field)}
				{$msg} 
			{else}
				<a href="#{$field}">{$msg}</a>
			{/if}
			</li>
			{/foreach}
		</ul>
		{/if}
		
		{if isset($notifications)}
		<ul class="msgs notifications">
			{foreach from=$notifications item=msg}
			<li>{$msg}</li>
			{/foreach}
		</ul>
		{/if}

		
		{if isset($success)}
		{if $success < 0}
			{assign var=class value=errors}
		{else}
			{assign var=class value=notifications}
		{/if}
		<ul class="msgs {$class}">
			<li>{$panelstrings.msgs[$success]}</li>
		</ul>
		{/if}
</div>
