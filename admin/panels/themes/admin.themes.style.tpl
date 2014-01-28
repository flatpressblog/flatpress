{include file=shared:errorlist.tpl}

<div id="current-theme">

<h2>{$panelstrings.head1}</h2>
<img src="{$current_style.preview}" alt="{$current_style.name}">
<h5>{$current_style.title} &#8212; {$current_style.author|default:$panelstrings.noauthor}</h5>
{$current_style.description|default:$panelstrings.nodescr}
</div>


<div id="available-themes">

<h2>{$panelstrings.head2}</h2>
<p>{$panelstrings.descr}</p>

{if $available_styles}

<!--<ul>--> <!--changed by liquibyte to allow for easier styling: unordered and ordered lists for content display is antiquated and while valid for screen readers it displays horribly.  That being said, menus are ok for this because it seems to be ubiquitous but best practices should be div's, span's, or paragraphs within menu's and nav's /rant-->
{foreach from=$available_styles item=thm}
	<div class="available-themes">
		<h5><a href="{$action_url|cmd_link:select:$thm.id}">{$thm.title}</a></h5>
		<a href="{$action_url|cmd_link:select:$thm.id}"><img src="{$thm.preview}" alt="{$thm.name}"></a>
		
		<p>{$thm.description|default:$panelstrings.nodescr}</p>
		
	</div>
{/foreach}
<!--</ul>-->

{/if}

</div>