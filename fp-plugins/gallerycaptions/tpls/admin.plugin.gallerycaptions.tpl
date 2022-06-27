<link rel="stylesheet" type="text/css" href="{$pluginurl}res/adminstyle.css" />
<h2>{$plang.head}</h2>

{include file=shared:errorlist.tpl}

{html_form class=option-set}
<p>
    {$plang.label_selectgallery} 
    <select name='gallerycaptions-gallery'>
        {foreach from="$galleries" item="galleryname"}
            <option value="{$galleryname}" {if $galleryname == $currentgallery} selected="selected"{/if}>{$galleryname}</option>
        {/foreach}
    </select>
	<input type="submit" name="gallerycaptions-selectgallery" value="{$plang.button_selectgallery}"/>
</p>
{/html_form}

{if !empty($currentgallery)}
	<h4>{$plang.label_editcaptionsforgallery} {$currentgallery}</h4>
	{html_form class=option-set}
	<p>
	{if count($currentgalleryimages) == 0}
		{$plang.label_noimagesingallery}
	{else}
		<input type="hidden" name="galleryname" value="{$currentgallery}">
		<table class="entrylist plugin_gallerycaptions_captionstable">
		{foreach from="$currentgalleryimages" item="currentfilename"}
			<tr>
				<td>
					<a href="{$smarty.const.BLOG_BASEURL}{$smarty.const.IMAGES_DIR}{$currentgallery}/{$currentfilename}">
					<img 
						src="{$smarty.const.BLOG_BASEURL}{$smarty.const.IMAGES_DIR}{$currentgallery}/{if defined("THUMB_DIR")}{$smarty.const.THUMB_DIR}/{/if}{$currentfilename}" 
						alt="{$currentfilename}" 
						title="{$currentfilename}" 
						/>
					</a>
					<br>
					{$currentfilename}
				</td>
				<td>
					<input type="text" name="captions[{$currentfilename}]" value="{if array_key_exists($currentfilename, $currentgallerycaptions)}{$currentgallerycaptions[$currentfilename]|escape}{/if}">
				</td>
			</tr>
		{/foreach}
		</table>
		<input type="submit" name="gallerycaptions-savecaptions" value="{$plang.button_savecaptions}"/>
	{/if}
	</p>
	{/html_form}
{/if}

