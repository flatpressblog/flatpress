<div id="errorlist">
		{if isset($error)}
		<ul class="msgs errors">
			{foreach from=$error key=field item=msg}
			<li>
			{if is_numeric($field)}
				{if !empty($escape_messages)}{$msg|escape:'html'}{else}{$msg}{/if}
			{else}
				<a href="#{$field|escape:'html'}">{if !empty($escape_messages)}{$msg|escape:'html'}{else}{$msg}{/if}</a>
			{/if}
			</li>
			{/foreach}
		</ul>
		{/if}

		{if isset($warnings) && !empty($warnings)}
		<ul class="msgs warnings">
			{foreach from=$warnings key=field item=msg}
			<li>
			{if is_numeric($field)}
				{if !empty($escape_messages)}{$msg|escape:'html'}{else}{$msg}{/if}
			{else}
				<a href="#{$field|escape:'html'}">{if !empty($escape_messages)}{$msg|escape:'html'}{else}{$msg}{/if}</a>
			{/if}
			</li>
			{/foreach}
		</ul>
		{/if}

		{if isset($notifications)}
		<ul class="msgs notifications">
			{foreach from=$notifications item=msg}
			<li>{if !empty($escape_messages)}{$msg|escape:'html'}{else}{$msg}{/if}</li>
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
			<li>{if !empty($escape_messages)}{$panelstrings.msgs[$success]|escape:'html'}{else}{$panelstrings.msgs[$success]}{/if}</li>
		</ul>
		{/if}
</div>
