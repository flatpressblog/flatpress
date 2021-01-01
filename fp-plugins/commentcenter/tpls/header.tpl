
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">{$plang.title}</h6></div>
            <div class="card-body">
            <p>{$plang.desc1}</p>
            <p>{$plang.desc2}</p>
            <ul>
            <li><a href="{$panel_url|action_link:commentcenter}" title="{$plang.lpolicies}">{$plang.lpolicies}</a></li>
            <li><a href="{$action_url|cmd_link:approve_list:1}" title="{$plang.lapprove}">{$plang.lapprove}</a></li>
            <li><a href="{$action_url|cmd_link:manage:'search'}" title="{$plang.lmanage}">{$plang.lmanage}</a></li>
            <li><a href="{$action_url|cmd_link:configure:1}" title="{$plang.lconfig}">{$plang.lconfig}</a></li>
            </ul>

            {include file='shared:errorlist.tpl'}
