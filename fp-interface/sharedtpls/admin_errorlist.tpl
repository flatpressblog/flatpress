<div id="errorlist">
		{if isset($error)}
		<ul class="msgs errors">
			{foreach from=$error key=field item=msg}
			<li class="alert alert-danger" role="alert">
			{if isset($field) && is_numeric($field)}
				{$msg} 
			{else}
				<a href="#{$field}" class="alert-link">{$msg}</a>
			{/if}
			</li>
			{/foreach}
		</ul>
		{/if}
		
		{if isset($warnings)}
		<ul class="msgs warnings">
			{foreach from=$warnings key=field item=msg}
			<li class="alert alert-warning" role="alert">
			{if isset($field) && is_numeric($field)}
				{$msg} 
			{else}
				<a href="#{$field}" class="alert-link">{$msg}</a>
			{/if}
			</li>
			{/foreach}
		</ul>
		{/if}
		
		{if isset($notifications)}
		<ul class="msgs notifications alert alert-info">
			{foreach from=$notifications item=msg}
			<li>{$msg}</li>
			{/foreach}
		</ul>
		{/if}

		
		{if isset($success)}
		{if $success < 0}
			{assign var=class value="alert alert-danger"}
		{else}
			{assign var=class value="alert alert-success"}
		{/if}
		<ul class="msgs {$class}">
			<li>{$panelstrings.msgs[$success]}</li>
		</ul>
		{/if}
</div>