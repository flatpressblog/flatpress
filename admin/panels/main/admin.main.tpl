
<div class="row">
	<!-- Pie Chart -->
	<div class="col-lg-6 mb-4">
		<div class="card shadow mb-4 quick_menu">
			<!-- Card Header - Dropdown -->
			<div class="card-header">
				<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.quick_menu}</h6>
			</div>
			<!-- Card Body -->
			<div class="card-body">
				<p>Wecome back <b>{$username}</b>, {$panelstrings.can_do}</p>
				<ul>
					<li><a href="admin.php?p=entry&amp;action=write"><span class="ti-pencil-alt"></span> {$panelstrings.op1d}</a></li>
					<li><a href="admin.php?p=entry"><span class="ti-layers-alt"></span> {$panelstrings.op2d}</a></li>
					<li><a href="admin.php?p=widgets"><span class="ti-move"></span> {$panelstrings.op3d}</a></li>
					<li><a href="admin.php?p=plugin"><span class="ti-hummer"></span> {$panelstrings.op4d}</a></li>
					<li><a href="admin.php?p=config"><span class="ti-settings"></span> {$panelstrings.op5d}</a></li>
					<li><a href="admin.php?p=maintain"><span class="ti-check-box"></span> {$panelstrings.op6d}</a></li>
				</ul>
			</div>
		</div>
			<div class="card shadow mb-4">
			<div class="card-header">
				<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.help}</h6>
			</div>
			<div class="card-body maintain">
				<p>{$panelstrings.useful_links}</p>
				<ul>
					<li><a href="https://www.flatpress.org"><span class="ti-home"></span> {$panelstrings.fp_home}</a></li>
					<li><a href="https://www.flatpress.org/blog.php?x=entry"><span class="ti-layers-alt"></span> {$panelstrings.fp_blog}</a></li>
					<li><a href="http://forum.flatpress.org/"><span class="ti-comments"></span> {$panelstrings.fp_forums}</a></li>
					<li><a href="http://wiki.flatpress.org/"><span class="ti-help-alt"></span> {$panelstrings.fp_wiki}</a></li>
				</ul>
			</div>
			</div>
	</div>
	<div class="col-lg-6 mb-4">
		
			<div class="card shadow mb-4">
			<div class="card-header">
				<h6 class="m-0 font-weight-bold text-primary">{$panelstrings.last_comments}</h6>
			</div>
			<div class="card-body lc_table">
				{if isset($last_comments_table)}
					{$last_comments_table}
				{else}
					<h2 class="text-center font-italic">No comments yet :(</h2>
				{/if}
			</div>
			</div>
	</div>
</div>
<div class="row">



</div>
