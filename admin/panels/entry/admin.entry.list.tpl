{include file=shared:admin_errorlist.tpl}
		<div class="row">
            <div class="col-xl-8 col-lg-7">
              <div class="card shadow mb-4">
                <div class="card-header">
                  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.head}</h6>
                </div>
                <div class="card-body">
					<div class="table-responsive table-striped">
						<table class="entrylist table">
						<thead><tr>{*<th>{$panelstrings.sel}</th>*}
						<th>{$panelstrings.date}</th>
						<th class="main-cell">{$panelstrings.title}</th>
						<!-- <th>{$panelstrings.author}</th> -->
						<th>{$panelstrings.comms}</th>
						<th>{$panelstrings.action}</th></tr></thead>
						<tbody>
						{entry}
						<tr>
						<td>{$id|entry_idtotime|date_format:"`$fp_config.locale.dateformatshort`, `$fp_config.locale.timeformat`"}</td>
						<td class="main-cell">
						{if isset($categories) && in_array('draft',$categories)}
						(<em class="entry-flag">{$lang.entry.flags.short.draft}</em>)
						{/if}
						<a class="link-general" 
						href="{$panel_url|action_link:write}&amp;entry={$id}">
						{$subject|truncate:70} 
						</a>
						</td>
						<!-- <td>{$author}</td> -->
						<td><a class="link-general" 
						href="{$panel_url|action_link:commentlist}&amp;entry={$id}">
						{* Compatibility with pre-0.702 *}
						{$commentcount|default:$comments}
						<span class="ti-comments"></span></a></td>
						<td>
						<a class="link-general" 
						href="{$id|link:post_link}">
						<span class="ti-desktop"></span>
						{$panelstrings.act_view}
						</a>
						<a class="link-general" 
						href="{$panel_url|action_link:write}&amp;entry={$id}">
						<span class="ti-pencil-alt"></span>
						{$panelstrings.act_edit}
						</a>
						<a class="link-delete" 
						href="{$panel_url|action_link:delete}&amp;entry={$id}">
						<span class="ti-trash"></span>
						{$panelstrings.act_del}
						</a>
						</td>
						</tr>
						{/entry}
						</tbody></table>
					</div>
					{entry_block}
					<div class="navigation">
						<div class="prevpage">{prevpage admin=yes}</div>
						<div class="nextpage">{nextpage admin=yes}</div>
					</div>
					</br>
					{/entry_block}
				  	<div class="entry_filter">
					<form method="get" action="{if isset($smarty.request.PHP_SELF)}{$smarty.request.PHP_SELF}{/if}?p=entry">
						<p> <input type="hidden" name="p" value="entry" /> </p>
							<select name="category" class="alignleft form-control select_filter">
							<option label="Unfiltered" value="all">{$panelstrings.nofilter}</option>
							{*html_options options=$lang.entry.flags.short selected=$smarty.request.cat*}
							{html_options options=$categories_all selected=$smarty.request.category}
							</select>
							{html_submit name='filter' id='filter' class="alignright btn btn-primary select_filter apply_filter_button" value=$panelstrings.filterbtn}
						</fieldset>
						</form>
				  	</div>
				  	<a href="admin.php?p=entry&action=write" class="btn btn-primary new_entry_button"><span class="ti-user text-white-100 small"></span> {$panelstrings.add_new_entry}</a>
                </div>
              </div>
            </div>
			{draft_block}
            <div class="col-xl-4 col-lg-5">
              <div class="card shadow mb-4">
                <div class="card-header">
                  <h6 class="m-0 font-weight-bold text-primary">{$panelstrings.your_drafts}</h6>
                </div>
                <div class="card-body">
						<div id="admin-drafts">
						<ul>
						{draft}
						<li>
							<a href="admin.php?p=entry&amp;entry={$id}&amp;action=write">{$subject|truncate:70}</a>
						</li>
						{/draft}
						</ul>
						</div>
                </div>
              </div>
            </div>
			{/draft_block}
          </div>



