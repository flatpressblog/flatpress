{include file=shared:admin_errorlist.tpl}

<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card shadow mb-4">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head1}</h6>
				</div>
				<div class="card-body">
					<div id="current-theme">
					<img src="{$current_style.preview}" class="current-theme-img" alt="{$current_style.name}" />
					<div class="current-theme-description">
						<h3>{$current_style.title} &#8212; {$current_style.author|default:$panelstrings.noauthor}</h3>
						{$current_style.description|default:$panelstrings.nodescr}
					</div>
					</div>
				</div>
			</div>
		 </div>
</div>

{if $available_styles}

<div class="row">
	<div class="col-xl-12 col-lg-12">
		<div class="card shadow mb-4">
			<div class="card-header all_radius text-center red_background">
				<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head2}</h6>
			</div>
		</div>
	</div>
</div>


<div id="available-themes">

<div class="row">
{foreach from=$available_styles item=thm}
	<div class="col-lg-6 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header">
				<h6 class="m-0 font-weight-bold text-primary text-center"><a href="{$action_url|cmd_link:select:$thm.id}">{$thm.title}</a></h6>
			</div>
			<div class="card-body">
				<ul >
					<li>
						<h5><a href="{$action_url|cmd_link:select:$thm.id}">{$thm.title}</a></h5>
						<a href="{$action_url|cmd_link:select:$thm.id}"><img src="{$thm.preview}" alt="{$thm.name}" /></a>

						<p>{$thm.description|default:$panelstrings.nodescr}</p>

					</li>
				</ul>
			</div>
		</div>
	</div>
{/foreach}
</div>

{/if}

</div>