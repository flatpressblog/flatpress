<link rel="stylesheet" type="text/css" href="{($mmurl|cat:'res/style.css')|ver:$smarty.const.SYSTEM_VER}">
<h2>{$plang.head}</h2>
<p>{$plang.description}</p>
{include file="shared:errorlist.tpl"}

{html_form class="option-set"}
{$plang.page}: {$paginator.current} /  {$paginator.total}<br>
{if $currentgallery!=""}<h3>gallery '{$currentgallery}'</h3>{/if}
<table class="entrylist">
	<thead>
		<tr>
			<th>&nbsp;</th>
			<th class="colname">{$plang.colname}</th>
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
			<td>
				&nbsp;
			</td>
			<td class="main-cell type-{$v.type}">
				<a class="link-general" href="admin.php?p=uploader&action=mediamanager&gallery={$v.name}">{$v.name}</a>
			</td>
			<td>{if $v.usecount>0}
				<a class="link-general" href="search.php?q=images%2F{$v.name|escape:'url'}&stype=full&author=&cat=-1&phrasetype=all&Date_Day=&Date_Month=&Date_Year=&submit=Search">{$v.usecount}</a>
				{else}
				0
				{/if}
			</td>
			<td>{$v.size}</td>
			<td>{$v.mtime}</td>
			<td>
				<a class="link-delete" href="{wp_nonce_url("{$mmbaseurl}&deletefile={$v.type}-{$v.name}", 'mediamanager_deletefile')}">{$plang.delete}</a>
			</td>
		</tr>
	{/foreach}
{/if}
{if $totalfilescount=="0"}
	<tr><td colspan="6"><br>{$plang.nofiles} <a class="link-general" href="admin.php?p=uploader&action=default">{$plang.loadfile}</a><br><br></td></tr>
{else}
	{foreach from=$files item=v}
		<tr>
			<td>
				{if $v.type=='images'}
					<input type='checkbox' name='file[{$v.type}-{$v.name}]'>
				{else}
					&nbsp;
				{/if}
			</td>
			<td class="main-cell type-{$v.type}"><a class="link-general{if $v.type=='images'} bbcode-popup{/if}" {if $v.type=='images'}rel="lightbox[mm]"{/if} href="{$v.url}">{$v.name}</a></td>
			<td>
			{if $v.usecount>0}
				{assign var="vrel" value=$v.relpath|default:$v.name}
				{assign var="vfolder" value=$v.relpath|default:''|regex_replace:"/\\/.+$/":""}
				{if $v.type == 'images' && $v.relpath ne '' && $v.relpath ne $vfolder}
					{assign var="vq" value="images/`$vfolder`"}
				{else}
					{assign var="vq" value="`$v.type`/`$vrel`"}
				{/if}
				<a class="link-general" href="search.php?q={$vq|escape:'url'}&stype=full&author=&cat=-1&phrasetype=all&Date_Day=&Date_Month=&Date_Year=&submit=Search">{$v.usecount}</a>
				{else}
				0
			{/if}
			</td>
			<td>{$v.size}</td>
			<td>{$v.mtime}</td>
			<td>
				<a class="link-delete" href="{wp_nonce_url("{$mmbaseurl}&deletefile={$v.type}-{$v.name}", 'mediamanager_deletefile')}">{$plang.delete}</a>
			</td>
		</tr>
	{/foreach}
{/if}
	</tbody>
</table>

<!-- paginator -->
<p class="paginator">
	{foreach name=pagelist from=$paginator.pages item=page}
		{if $paginator.current==$page}
			{$page}
		{else}
			<a href="{$mmbaseurl}&page={$page}">{$page}</a>
		{/if}
		{if $smarty.foreach.pagelist.last==false} - {/if}
	{/foreach}
</p>

<p>
	{$plang.selected}:
	<select name='action'>
		<option value='-'>{$plang.selectaction}</option>
		{foreach from=$dwgalleries item=v}
			<option value='atg-{$v.name}'>{$plang.addtogallery} '{$v.name}'</option>
		{/foreach}
	</select>
	<input type="submit" name="mm-addto" value="{$plang.go}">
</p>

<p>
	<label>{$plang.newgallery}:
	<input type="text" name="mm-newgallery-name">
	</label>
	<input type="submit" name="mm-newgallery" value="{$plang.add}">
</p>

{/html_form}
