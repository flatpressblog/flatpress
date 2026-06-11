<?php
$lang ['admin'] ['panel'] ['maintain'] = '维护';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => '维护',
	'descr' => '网站有问题的时候你来这里，也许能找到解决办法，但是网站可能不能正常访问。',
	'opt0' => '&laquo; 返回主菜单',
	'opt1' => '重新配置索引',
	'opt2' => '清除主题和模板缓存',
	'opt3' => '恢复权限',
	'opt4' => 'PHP显示信息',
	'opt5' => '检查更新',
	'opt6' => 'APCu缓存状态',

	'chmod_info' => '如果无法恢复（重置）权限，请查看文件或目录所有者和web服务器执行权限。<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>权限</th>
					<th>' . FP_CONTENT . '</th>
					<th>Core(核心部分)</th>
					<th>其他</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>文件</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>目录</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => '已成功更新所有权限。',
	'opt3_error' => '权限更新错误：'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => '已完成操作。',
	-1 => '无法完成操作。'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => '更新',
	'list' => '<ul>
		<li>当版本<big>%s</big></li>
		<li>上一个版本<big><a href="%s">%s</a></big>。</li>
		<li>最新版本<big><a href="%s">%s</a></big>。</li>
		</ul>',
	'notice' => '通知:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => '有更新!',
	2 => '正在使用最新版。',
	-1 => '无法搜索最新版本。'
);

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'APCu缓存',
	'descr' => 'APCu共享内存使用和缓存效率概述。',
	'status_heading' => '启发式状态',
	'status_good' => '高速缓存大小似乎适合当前工作负载。',
	'status_bad' => '错误率高或占用空间太少：APCu高速缓存可能太小或出现严重碎片。',
	'hit_rate' => '命中率',
	'free_mem' => '空闲内存',
	'total_mem' => '共享内存总数',
	'used_mem' => '已用内存',
	'avail_mem' => '可用内存',
	'memory_type' => '存储器类型',
	'memory_type_unknown' => '无',
	'num_slots' => '插槽数',
	'num_hits' => '命中数',
	'num_misses' => '错误数',
	'cache_type' => '高速缓存类型',
	'cache_user_only' => '用户数据高速缓存',
	'legend_good' => '绿色－配置运行良好（命中率高，可用内存充足）。',
	'legend_bad' => '红色：高速缓存处于负载状态（经常出现错误或几乎没有可用内存）。',
	'no_apcu' => '此服务器似乎未启用APCu。',
	'back' => '&laquo; 返回维护',
	'clear_fp_button'=> '清除FlatPress APCu条目',
	'clear_fp_confirm' => '您确定要删除所有APCu条目吗？这将清除FlatPress的APCu缓存。',
	'clear_fp_result'=> '已删除%d个APCu条目。',
	'msgs' => array(
		1  => 'FlatPress APCu条目已清除。',
		2  => '未找到APCu条目。',
		-1 => '未删除APCu，因为APCu不可用或不可访问。'
	)
);
?>
