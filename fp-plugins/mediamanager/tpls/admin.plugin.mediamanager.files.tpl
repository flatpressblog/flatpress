<link rel="stylesheet" type="text/css" href="{($mmurl|cat:'res/style.css')|ver:$smarty.const.SYSTEM_VER}">
<script nonce="{$smarty.const.RANDOM_HEX}" src="{($mmurl|cat:'res/mediamanager.js')|ver:$smarty.const.SYSTEM_VER}" defer></script>

<h2>{$plang.head}</h2>
<p>{$plang.description}</p>
{include file="shared:errorlist.tpl"}

{html_form class="option-set"}
	{$plang.page}: {$paginator.current} /  {$paginator.total}<br>
	<label>{$plang.view}: 
		<select name="mm-viewmode" onchange="this.form.submit()">
			<option value="detail"{if $viewmode ne 'thumb'} selected{/if}>{$plang.view_detail}</option>
			<option value="thumb"{if $viewmode eq 'thumb'} selected{/if}>{$plang.view_thumb}</option>
		</select>
	</label><br><br>
	{if $currentgallery!=""}<h3>gallery '{$currentgallery}'</h3>{/if}

	{if $viewmode eq 'thumb'}
		{* Back tile if inside a gallery *}
		{if $currentgallery!=""}
			<div class="mm-grid">
				<div class="mm-item">
					<a class="link-general" href="admin.php?p=uploader&action=mediamanager">
						<img class="mm-thumb" src="{$mmurl}res/folder.gif" alt="{$plang.up}">
						<div class="mm-caption">{$plang.up}</div>
					</a>
				</div>
			</div>
		{/if}
		{* Galleries tiles *}
		{if $galleries|@count gt 0}
			<div class="mm-grid">
				{foreach from=$galleries item=v}
					<div class="mm-item">
						<a class="link-general" href="admin.php?p=uploader&action=mediamanager&gallery={$v.name}">
							<img class="mm-thumb" src="{$mmurl}res/{if !empty($v.used_in_posts)}folder.gif{else}not-use-folder.gif{/if}" alt="{$v.name}">
							<div class="mm-caption">{$v.name}</div>
						</a>
					</div>
				{/foreach}
			</div>
		{/if}
		{* Files tiles *}
		{if $files|@count gt 0}
			<div class="mm-grid">
				{foreach from=$files item=v}
					<div class="mm-item">
						{if $v.type=='images'}
							{assign var=mmid value=$v.name|regex_replace:"/[^A-Za-z0-9\-]+/":"_"}
							<label class="mm-check" for="mmchk_{$mmid}"><input id="mmchk_{$mmid}" type="checkbox" name="file[{$v.type}-{$v.name}]"></label>
						{/if}
						<a class="link-general{if $v.type=='images'} mm-preview{/if}" {if $v.type=='images'}data-mm-preview="{$v.url}"{/if} href="{$v.url}" target="_blank" rel="noopener">
							<img class="mm-thumb" src="{if $v.type=='images'}{$v.url}{else}{$mmurl}res/unknown.gif{/if}" alt="{$v.name}">
							<div class="mm-caption">{$v.name|truncate:24:"…":true}</div>
						</a>
						<div class="mm-actions">
							<a class="link-delete" href="{wp_nonce_url("{$mmbaseurl}&delete={$v.type}-{$v.name}", 'mediamanager_deletefile')}">{$plang.delete}</a>
						</div>
					</div>
				{/foreach}
			</div>
		{/if}
	{else}

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
					<td class="main-cell {if !empty($v.used_in_posts)}type-gallery{else}type-not-use{/if}">
						<a class="link-general" href="admin.php?p=uploader&action=mediamanager&gallery={$v.name}">{$v.name}</a>
					</td>
					<td>
					{if $v.usecount>0}
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
							<input type="checkbox" name="file[{$v.type}-{$v.name}]">
						{else}
							&nbsp;
						{/if}
					</td>
					<td class="main-cell type-{$v.type}"><a class="link-general{if $v.type=='images'} bbcode-popup{/if}" {if $v.type=='images'}rel="lightbox[mm]"{/if} data-mm-preview="{$v.url}" href="{$v.url}">{$v.name}</a></td>
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
	{/if}

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
