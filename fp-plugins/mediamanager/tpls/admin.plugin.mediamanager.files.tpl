<link rel="stylesheet" type="text/css" href="{$mmurl}res/style.css" />

{include file=shared:errorlist.tpl}

{html_form class=option-set}

<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card shadow mb-4">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">{$plang.head} {if $currentgallery!=""}<span class="ti-angle-double-right"></span> gallery '{$currentgallery}'{/if}</h6>
				</div>
				<div class="card-body">
					<div class="table-responsive table-striped">
						<table class="entrylist table">
							<thead>
								<colgroup><col/><col width="50%"/><col/><col/><col/></colgroup>
								<tr class="head_table" style="background-color:#aa4142; color:#fff">
									<th>&nbsp;</th>
									<th>{$plang.colname}</th>
									<th>{$plang.colusecount}</th>
									<th>{$plang.colsize}</th>
									<th>{$plang.colmtime}</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
						{if $currentgallery!=""}
							<tr><td>&nbsp;</td>
								<td class="main-cell type-gallery" colspan="5">
									<a class="link-general" href="admin.php?p=uploader&action=mediamanager">{$plang.up}</a>
								</td>
							</tr>
						{else}
							{foreach from=$galleries item=v}
								<tr>
									<td>&nbsp;

									</td>
									<td class="main-cell type-{$v.type}">
										<a class="link-general" href="admin.php?p=uploader&action=mediamanager&gallery={$v.name}">{$v.name}</a>
									</td>
									<td>{if $v.usecount>0}
										<a class="link-general" href="search.php?q=images%2F{$v.name}&stype=full&Date_Day=&Date_Month=&Date_Year=&submit=Search">{$v.usecount}</a>
										{else}
										0
										{/if}
									</td>
									<td>{$v.size}</td>
									<td>{$v.mtime}</td>
									<td>
										<a class="link-delete" href="{$mmbaseurl}&deletefile={$v.type}-{$v.name}">
										<span class="ti-trash"></span> {$plang.delete}</a>
									</td>
								</tr>
							{/foreach}
						{/if}
						{if $totalfilescount=="0" }
							<tr><td colspan="6"><br>{$plang.nofiles} <a class="link-general" href="admin.php?p=uploader&action=default">{$plang.loadfile}</a><br><br></td></tr>
						{else}
							{foreach from=$files item=v}
								<tr>
									<td>
										{if $v.type=='images'}
											<input type='checkbox' class='mt-1' name='file[{$v.type}-{$v.name}]'>
										{else}
											&nbsp;
										{/if}
									</td>
									<td class="main-cell type-{$v.type}"><a class="link-general" {if $v.type=='images'}rel="lightbox[mm]"{/if} href="{$v.url}">{$v.name}</a></td>
									<td>{if $v.usecount>0}
										<a class="link-general" href="search.php?q={$v.type}%2F{$v.name}&stype=full&Date_Day=&Date_Month=&Date_Year=&submit=Search">{$v.usecount}</a>
										{else}
										0
										{/if}
									</td>
									<td>{$v.size}</td>
									<td>{$v.mtime}</td>
									<td>
										<a class="link-delete" href="{$mmbaseurl}&deletefile={$v.type}-{$v.name}"><span class="ti-trash"></span> {$plang.delete}</a>
									</td>
								</tr>
							{/foreach}
						{/if}
							</tbody>
						</table>
					</div>
					<!-- paginator -->
						<p class="paginator">
							  <div class="paginator-left">
								{$plang.page}: {$paginator.current} /  {$paginator.total}</br>
							  </div>
							  <div class="paginator-right">
								  <ul class="pagination pagination-sm">
									{foreach name=pagelist from=$paginator.pages item=page}
									{if $paginator.current==$page}
									<li class="page-item disabled">
									  <a class="page-link" href="#" tabindex="-1">{$page}</a>
									</li>
									 {else}
									<li class="page-item"><a class="page-link" href="{$mmbaseurl}&page={$page}">{$page}</a></li>
									{/if}
										<!-- {if $smarty.foreach.pagelist.last==false} - {/if} -->
									{/foreach}
								  </ul>
							  </div>
						</p>
						</br>
						</br>
						<p>	
							<label>
							{$plang.selected}:
							<select name='action' class="form-control gallery_select">
								<option value='-'>{$plang.selectaction}</option>
								<{foreach from=$dwgalleries item=v}
									<option value='atg-{$v.name}'>{$plang.addtogallery} '{$v.name}'</option>
								{/foreach}
							</select>
							</label>
							<input type="submit" name="mm-addto" class="btn btn-primary" value="{$plang.go}" style="margin-bottom:0.2rem"/>
						</p>
						<p>
							<label>{$plang.newgallery}:
							<input type="text" name="mm-newgallery-name" class="form-control input_gray"/>
							</label>
							<input type="submit" name="mm-newgallery" class="btn btn-primary" value="{$plang.add}" style="margin-bottom:0.2rem"/>
						</p>
				</div>
			</div>
		</div>
	</div>

{/html_form}

