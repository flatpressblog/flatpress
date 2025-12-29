<?php
/**
 * Plugin Name: Storage
 * Description: Displays storage information from FlatPress. Part of the standard distribution.
 * Version: 1.0.1
 * Plugin URI: https://flatpress.org
 * Author: FlatPress
 * Author URI: https://flatpress.org
 */
if (class_exists('AdminPanelAction')) {

	// ---- BOF: Caching helpers ----
	/**
	 * Returns a versioned APCu namespace suffix for Storage plugin caching (":vN") or "" if APCu is unavailable; memoized per request.
	 * Reads "fp:storage:v" from APCu and initializes it to 1 when missing, enabling global cache invalidation by version bump.
	 * @return string Namespace suffix to append to cache keys.
	 */
	function plugin_storage_cache_ns() {
		static $ns = null;
		if ($ns !== null) {
			return $ns;
		}
		$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
		if (!$apcu_on) {
			return $ns = '';
		}
		$v = apcu_get('fp:storage:v');
		if (!$v) {
			@apcu_set('fp:storage:v', 1);
			$v = 1;
		}
		return $ns = ':v' . (int)$v;
	}

	/**
	 * Globally invalidates Storage plugin caches by bumping the APCu namespace version and purging file-based cache files.
	 * Increments APCu key "fp:storage:v" (creating it if absent) and best-effort unlinks JSON caches under fp-content/cache (e.g., storage.dirsize.*, storage.quota.json).
	 * @return void
	 */
	function plugin_storage_cache_bump() {
		if (!(function_exists('is_apcu_on') && is_apcu_on())) {
			return;
		}
		$ok = false;
		apcu_incr('fp:storage:v', 1, $ok);
		if (!$ok) {
			@apcu_set('fp:storage:v', 1);
		}
		// File fallback best-effort purge
		$cf = (defined('FP_CONTENT') ? FP_CONTENT : 'fp-content/') . 'cache/storage.aggregate.json';
		@unlink($cf);
	}

	/**
	 * Retrieves aggregated Storage metrics from APCu (versioned namespace) or JSON file fallback within TTL.
	 * Validates freshness and structure; returns associative array of raw values (e.g., entries_*, comments_*, topten, ts) or null on miss.
	 * @return array|null
	 */
	function plugin_storage_cache_get() {
		$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
		$key = $apcu_on ? ('fp:storage:aggregate' . plugin_storage_cache_ns()) : null;
		if ($apcu_on) {
			$hit = false;
			$val = apcu_get($key, $hit);
			if ($hit && is_array($val)) {
				return $val;
			}
		}
		// File fallback
		$cf = (defined('FP_CONTENT') ? FP_CONTENT : 'fp-content/') . 'cache/storage.aggregate.json';
		if (@file_exists($cf)) {
			$mt = @filemtime($cf);
			$ttl = 120;
			if ($mt !== false && (time() - (int)$mt) < $ttl) {
				$raw = @io_load_file($cf);
				if (is_string($raw)) {
					$jd = @json_decode($raw, true);
					if (is_array($jd)) {
						return $jd;
					}
				}
			}
		}
		return null;
	}

	/**
	 * Stores aggregated Storage metrics in APCu (versioned namespace) and writes a JSON fallback file for non-APCu environments.
	 * @param array $payload Raw counters and sizes to cache; persisted with a TTL and JSON-encoded to disk.
	 * @return void
	 */
	function plugin_storage_cache_set(array $payload) {
		$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
		if ($apcu_on) {
			@apcu_set('fp:storage:aggregate' . plugin_storage_cache_ns(), $payload, 300);
		}
		// Also write JSON fallback
		$cf = (defined('FP_CONTENT') ? FP_CONTENT : 'fp-content/') . 'cache/storage.aggregate.json';
		@io_write_file($cf, json_encode($payload));
	}
	// Hook-based invalidation
	if (function_exists('add_action')) {
		add_action('publish_post', 'plugin_storage_cache_bump', 10, 1);
		add_action('delete_post', 'plugin_storage_cache_bump', 10, 1);
		add_action('comment_save', 'plugin_storage_cache_bump', 10, 2);
		add_action('comment_delete', 'plugin_storage_cache_bump', 10, 2);
	}

	/**
	 * Recursively sums file sizes and counts under $abs_root, with APCu+JSON caching; can skip ".thumbs" dirs and ".captions.conf" files.
	 * @param string $abs_root, string $channel, int $ttl=120, bool $excludeThumbDirs=false, bool $excludeCaptionsConf=false
	 * @return array{size:float,count:int,ts:int}
	 */
	function plugin_storage_dirsize_get($abs_root, $channel, $ttl = 120, $excludeThumbDirs = false, $excludeCaptionsConf = false) {
		$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
		$ns = plugin_storage_cache_ns();
		$suffix = ($excludeThumbDirs ? ':nth' : '') . ($excludeCaptionsConf ? ':ncc' : '');
		$key = $apcu_on ? ('fp:storage:dirsize:' . $channel . $suffix . $ns) : null;
		if ($apcu_on) {
			$ok = false;
			$val = apcu_get($key, $ok);
			if ($ok && is_array($val) && isset($val ['size']) && isset($val ['count']) && isset($val ['ts'])) {
				if ((time() - (int)$val ['ts']) < $ttl) {
					return $val;
				}
			}
		}
		$cfdir = (defined('FP_CONTENT') ? FP_CONTENT : 'fp-content/') . 'cache/';

		$cfdir = (defined('FP_CONTENT') ? FP_CONTENT : 'fp-content/') . 'cache/';
		if (!@is_dir($cfdir)) {
			if (function_exists('fs_mkdir')) {
				@fs_mkdir($cfdir);
			} else {
				@mkdir($cfdir, DIR_PERMISSIONS, true);
			}
		}
		$cf = $cfdir . 'storage.dirsize.' . $channel . ($excludeThumbDirs ? '.nth' : '') . ($excludeCaptionsConf ? '.ncc' : '') . '.json';

		if (@file_exists($cf)) {
			$mt = @filemtime($cf);
			if ($mt !== false && (time() - (int)$mt) < $ttl) {
				$raw = @io_load_file($cf);
				if (is_string($raw)) {
					$jd = @json_decode($raw, true);
					if (is_array($jd) && isset($jd ['size']) && isset($jd ['count'])) {
						if ($apcu_on) {
							@apcu_set($key, array('size' => (float)$jd ['size'], 'count' => (int)$jd ['count'], 'ts' => (int)$mt), $ttl);
						}
						return array('size' => (float)$jd ['size'], 'count' => (int)$jd ['count'], 'ts' => (int)$mt);
					}
				}
			}
		}
		$total = 0.0;
		$count = 0;
		try {
			if (@is_dir($abs_root)) {
				$rdi = new RecursiveDirectoryIterator($abs_root, FilesystemIterator::SKIP_DOTS);
				$needFilter = ($excludeThumbDirs || $excludeCaptionsConf);
				if ($needFilter) {
					$exThumbs = $excludeThumbDirs;
					$exCap = $excludeCaptionsConf;
					$filter = new RecursiveCallbackFilterIterator($rdi, function($current/*SplFileInfo*/) use ($exThumbs, $exCap) {
						$name = strtolower($current->getFilename());
						if ($current->isDir()) {
							if ($exThumbs && $name === '.thumbs') {
								return false;
							}
							return true;
						}
						if ($exCap && $name === '.captions.conf') {
							return false;
						}
						return true;
					});
					$it = new RecursiveIteratorIterator($filter);
				} else {
					$it = new RecursiveIteratorIterator($rdi);
				}
				foreach ($it as $fi) {
					/** @var SplFileInfo $fi */
					if ($fi->isLink()) {
						continue;
					}
					if ($fi->isFile()) {
						$fs = @$fi->getSize();
						if ($fs !== false) {
							$total += (float)$fs;
							$count++;
						}
					}
				}
			}
		} catch (Throwable $e) {
			/* ignore */
		}
		$payload = array('size' => (float)$total, 'count' => (int)$count, 'ts' => time());
		if ($apcu_on) {
			@apcu_set($key, $payload, $ttl);
		}
		@io_write_file($cf, json_encode($payload));
		return $payload;
	}

	// ---- EOF: Caching helpers ----

	/**
	 * Determines if the PostViews plugin is active (feature gate for Top-10 output).
	 * Checks for function existence and presence in $GLOBALS['fp_plugins'] when available.
	 * @return bool True if PostViews is active, false otherwise.
	 */
	function plugin_storage_postviews_active() {
		$ok = function_exists('plugin_postviews_calc');
		if ($ok && isset($GLOBALS ['fp_plugins']) && is_array($GLOBALS ['fp_plugins'])) {
			$ok = in_array('postviews', $GLOBALS ['fp_plugins'], true);
		}
		return $ok;
	}

	/**
	 * Checks if a function name appears in php.ini's disable_functions list (thus unavailable).
	 * @param string $fn Function name to check.
	 * @return bool True if disabled, false otherwise.
	 */
	function plugin_storage_is_disabled($fn) {
		if (!function_exists('ini_get')) {
			return false;
		}
		$df = (string) @ini_get('disable_functions');
		if ($df === '') {
			return false;
		}
		$bl = array_map('trim', explode(',', $df));
		return in_array($fn, $bl, true);
	}

	/**
	 * Convenience helper: checks if a function can be called (exists and not disabled via php.ini).
	 * @param string $fn Function name.
	 * @return bool
	 */
	function plugin_storage_can_use($fn) {
		return function_exists($fn) && !plugin_storage_is_disabled($fn);
	}

	// ---- BOF: Webspace/Quota detection with cache ----

	/**
	 * Builds the APCu cache key for webspace quota data, including the versioned namespace suffix.
	 * @return string Cache key, e.g. "fp:storage:quota:vN".
	 */
	function plugin_storage_quota_cache_key() {
		$ns = function_exists('plugin_storage_cache_ns') ? plugin_storage_cache_ns() : '';
		return 'fp:storage:quota' . $ns;
	}

	/**
	 * Returns cached webspace quota from APCu (versioned key) or JSON file if still within TTL.
	 * Validates freshness and structure; fields include total_bytes, used_bytes, free_bytes, source.
	 * @param int $ttl Default 3600 seconds. @return array|null
	 */
	function plugin_storage_quota_get_cached($ttl = 3600) {
		$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
		if ($apcu_on) {
			$ok = false;
			$val = apcu_get(plugin_storage_quota_cache_key(), $ok);
			if ($ok && is_array($val) && isset($val ['ts']) && (time() - (int)$val ['ts']) < $ttl) {
				return $val;
			}
		}
		$cfdir = (defined('FP_CONTENT') ? FP_CONTENT : 'fp-content/') . 'cache/';
		$cf = $cfdir . 'storage.quota.json';
		if (@file_exists($cf)) {
			$mt = @filemtime($cf);
			if ($mt !== false && (time() - (int)$mt) < $ttl) {
				$raw = @io_load_file($cf);
				if (is_string($raw)) {
					$jd = @json_decode($raw, true);
					if (is_array($jd) && isset($jd ['total_bytes'])) {
						return $jd + array('ts' => (int)$mt);
					}
				}
			}
		}
		return null;
	}

	/**
	 * Caches webspace quota payload in APCu (versioned key) and writes a JSON fallback file.
	 * @param array $payload Associative data (e.g., total_bytes, used_bytes, free_bytes, source) persisted with TTL.
	 * @return void
	 */
	function plugin_storage_quota_set_cached(array $payload, $ttl = 3600) {
		$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
		$payload ['ts'] = time();
		if ($apcu_on) {
			@apcu_set(plugin_storage_quota_cache_key(), $payload, $ttl);
		}
		$cfdir = (defined('FP_CONTENT') ? FP_CONTENT : 'fp-content/') . 'cache/';
		if (!@is_dir($cfdir)) {
			@fs_mkdir($cfdir);
		}
		@io_write_file($cfdir . 'storage.quota.json', json_encode($payload));
	}

	/**
	 * Parses a human-readable size string (e.g., "12K", "3.5G") into bytes.
	 * @param string $s Input size; supports units K, M, G, T, P (base 1024).
	 * @return float|null Bytes on success, null on invalid format.
	 */
	function plugin_storage_parse_hsize($s) {
		$m = array();
		if (!preg_match('~^\s*([0-9]+(?:\.[0-9]+)?)\s*([KMGTP]?)~i', (string)$s, $m)) {
			return null;
		}
		$val = (float)$m [1]; $u = strtoupper($m [2]);
		$pow = array('' => 0, 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4, 'P' => 5);
		return (float) ($val * pow(1024, isset($pow [$u]) ? $pow [$u] : 0));
	}

	/**
	 * Detects per-user webspace quota via `quota` (preferred), config constant, or filesystem fallback; caches result (APCu+JSON).
	 * @param string $root Root path for filesystem fallback; @param int $ttl Cache TTL in seconds.
	 * @return array{total_bytes:float,used_bytes:float|null,free_bytes:float|null,source:string}
	 */
	function plugin_storage_detect_webspace_quota($root, $ttl = 3600) {
		// Cache first
		$cached = plugin_storage_quota_get_cached($ttl);
		if (is_array($cached)) {
			return $cached;
		}

		// Try 'quota -s' for current user
		$total = 0.0;
		$used = null;
		$free = null;
		$source = 'unknown';
		$can_shell = function_exists('shell_exec') && !plugin_storage_is_disabled('shell_exec');
		if ($can_shell) {
			$out = @shell_exec('quota -s 2>/dev/null');
			if (is_string($out) && trim($out) !== '') {
				$lines = preg_split('~\R+~', trim($out));
				// Find first data row with at least 4 columns
				foreach ($lines as $ln) {
					if (strpos($ln, 'Filesystem') !== false) {
						continue;
					}
					$cols = preg_split('~\s+~', trim($ln));
					if (count($cols) >= 4) {
						// Formats vary; try to read used/quota/limit from typical positions
						// Try human format: FS used quota limit ...
						$uB = plugin_storage_parse_hsize($cols [1]);
						$qB = plugin_storage_parse_hsize($cols [2]);
						$lB = plugin_storage_parse_hsize($cols [3]);
						if (is_float($qB) && $qB > 0) {
							$total = (float) $qB; // Soft limit
							$used = is_float($uB) ? (float)$uB : null;
							$free = ($used !== null) ? max(0.0, $total - $used) : null;
							$source = 'quota';
							break;
						}
					}
				}
			}
			if ($source !== 'quota') {
				// Fallback to 1K blocks format: FS blocks quota limit ...
				$out2 = @shell_exec('quota -u ' . escapeshellarg(get_current_user()) . ' 2>/dev/null');
				if (is_string($out2) && trim($out2) !== '') {
					$lines = preg_split('~\R+~', trim($out2));
					foreach ($lines as $ln) {
						if (strpos($ln, 'Filesystem') !== false) continue;
						$cols = preg_split('~\s+~', trim($ln));
						if (count($cols) >= 4 && ctype_digit($cols [1]) && ctype_digit($cols [2])) {
							$uB = (float)$cols [1] * 1024.0;
							$qB = (float)$cols [2] * 1024.0; // Soft
							$lB = (float)$cols [3] * 1024.0; // Hard
							if ($qB > 0) {
								$total = $qB;
								$used  = $uB;
								$free  = max(0.0, $total - $used);
								$source = 'quota';
								break;
							}
						}
					}
				}
			}
		}

		// Plugin-config constant override
		if ($source !== 'quota' && defined('WEBSPACE_LIMIT_BYTES') && (float)WEBSPACE_LIMIT_BYTES > 0) {
			$total = (float) WEBSPACE_LIMIT_BYTES;
			$source = 'config';
		}

		// Last resort: filesystem capacity
		if ($total <= 0 && is_string($root) && $root !== '') {
			$tot = plugin_storage_can_use('disk_total_space') ? @disk_total_space($root) : false;
			if ($tot !== false) {
				$total = (float)$tot;
				$fre = plugin_storage_can_use('disk_free_space') ? @disk_free_space($root) : false;
				if ($fre !== false) {
					$free = (float)$fre;
					$used = $total - $free;
				}
				$source = 'filesystem';
			}
		}

		$res = array(
			'total_bytes' => (float)$total,
			'used_bytes' => is_null($used) ? null : (float)$used,
			'free_bytes' => is_null($free) ? null : (float)$free,
			'source' => $source
		);
		plugin_storage_quota_set_cached($res, $ttl);
		return $res;
	}

	// ---- EOF: Webspace/Quota detection with cache ----

	class admin_uploader_storage extends AdminPanelAction {

		var $lang = 'plugin:storage';

		/**
		 * Scales a number by successive powers of $base and returns the compact value with its unit exponent.
		 * @param float|int $number Input value; @param int $base Typically 1000 or 1024.
		 * @return array{0:string,1:int} [formattedValue, exponentIndex]
		 */
		function format_number($num, $sep) {

			$i = 0;
			// For sizes use true binary steps. For counts keep legacy behavior.
			if ($sep === 1024) {
				while ($num >= $sep && $i < 6) {
					$num = (float) $num / $sep;
					$i++;
				}
			} else {
				$ss = $sep * $sep;
				while ($num > $ss) {
					$num = (float) $num / $sep;
					$i++;
				}
			}

			return array(number_format((int)$num), $i);

		}

		function setup() {
			global $lang;

			$lang = lang_load('plugin:storage');
			$this->smarty->assign('admin_resource', 'plugin:storage/admin.plugin.storage');
		}

		/**
		 * Builds and caches storage metrics for the admin Storage panel, then assigns them to Smarty.
		 * Computes entries/comments counts and sizes; images/attachments sizes excluding ".thumbs" and ".captions.conf".
		 * Calculates FlatPress folder size, filesystem totals and share, plus per-user webspace quota (quota|config|filesystem).
		 * Uses APCu and JSON fallback caching; conditionally computes Top-10 commented entries when PostViews is active.
		 * @return void
		 */
		function main() {

			global $lang;
			$lang = lang_load('plugin:storage');

			// Aggregates
			$entries = array('count' => 0, 'size' => 0, 'comments' => 0, 'topten' => array());
			$comments = array('count' => 0, 'size' => 0);

			// Feature gate for top 10
			$doTopTen = plugin_storage_postviews_active();

			// Try hot cache first
			$cached = plugin_storage_cache_get();
			if (is_array($cached)) {
				// Populate from cached raw values
				$entries ['count'] = (int)($cached ['entries_count'] ?? 0);
				$entries ['size'] = (float)($cached ['entries_size'] ?? 0);
				$entries ['comments'] = (int)($cached ['comments_count'] ?? 0);
				$entries ['topten'] = $doTopTen ? (array)($cached ['topten'] ?? array()) : array();
				$comments ['count'] = (int)($cached ['comments_count'] ?? 0);
				$comments ['size'] = (float)($cached ['comments_size'] ?? 0);
			} else {
				// Compute once and cache
				$idx = @entry_cached_index(0);
				if ($idx) {
					$len = $idx->length();
					if (is_int($len) || is_float($len)) {
						$entries ['count'] = (int)$len;
					}
				}
				$perEntry = $doTopTen ? array() : null; // id => comment_count
				$entryFileCount = 0; // Fallback if index missing
				$pl = new fs_pathlister(CONTENT_DIR);
				$files = (array) $pl->getList();
				foreach ($files as $path) {
					$base = basename($path);
					if (fnmatch('entry*' . EXT, $base)) {
						$entryFileCount++;
						$id = basename($path, EXT);
						$sz = @filesize($path);
						if ($sz !== false) {
							$entries ['size'] += (float)$sz;
						}
						$e = @bdb_parse_entry($id);
						continue;
					}
					if (fnmatch('comment*' . EXT, $base)) {
						$cid = basename($path, EXT);
						$d1 = dirname($path); // .../comments
						$d2 = dirname($d1); // .../entryYYYYMM-HHMMSS
						$eid = basename($d2);
						$comments ['count']++;
						$sz = @filesize($path);
						if ($sz !== false) {
							$comments ['size'] += (float)$sz;
						}
						if ($doTopTen) {
							$perEntry [$eid] = isset($perEntry [$eid]) ? $perEntry [$eid] + 1 : 1;
						}
					}
				}
				if (!$entries ['count'] && $entryFileCount) {
					$entries ['count'] = $entryFileCount;
				}
				$entries ['comments'] = $comments ['count'];

				if ($doTopTen && !empty($perEntry)) {
					arsort($perEntry);
					$i = 0;
					foreach ($perEntry as $k => $v) {
						if ($i >= 10) {
							break;
						}
					$subject = '';
					if ($idx) {
						$ekey = entry_idtokey($k);
						$subject = $idx->getitem($ekey);
					}
						$entries ['topten'] [$k] = array('subject' => $subject, 'comments' => $v);
						$i++;
					}
				}

				// Store raw values for future fast path
				plugin_storage_cache_set(array(
					'entries_count' => (int)$entries ['count'],
					'entries_size'  => (float)$entries ['size'],
					'comments_count'=> (int)$comments ['count'],
					'comments_size' => (float)$comments ['size'],
					'topten' => $doTopTen ? $entries ['topten'] : array(),
					'ts' => time()
				));
			}

			$decunit = array('', 'Thousand', 'Million', 'Billion', 'Trillion', 'Zillion', 'Gazillion');
			$binunit = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB');

			list($count, $approx) = $this->format_number($entries ['count'], 1000);
			$entries ['count'] = $count . ' ' . $decunit [$approx];
			list($count, $approx) = $this->format_number($entries ['comments'], 1000);
			$entries ['comments'] = $count . ' ' . $decunit [$approx];
			list($count, $approx) = $this->format_number($entries ['size'], 1024);
			$entries ['size'] = $count . ' ' . $binunit [$approx];
			$this->smarty->assign('entries', $entries);
			list($count, $approx) = $this->format_number($comments ['count'], 1000);
			$comments ['count'] = $count . ' ' . $decunit [$approx];
			list($count, $approx) = $this->format_number($comments ['size'], 1024);
			$comments ['size'] = $count . ' ' . $binunit [$approx];
			$this->smarty->assign('comments', $comments);
			$this->smarty->assign('show_topten', $doTopTen);

			// Static files storage
			$statics = array('count' => 0, 'size' => 0);
			if (function_exists('static_getlist')) {
				$static_ids = (array) static_getlist();
				$statics ['count'] = count($static_ids);
				$tot = 0.0;
				foreach ($static_ids as $sid) {
					$path = static_exists($sid);
					if ($path && is_file($path)) {
						$sz = @filesize($path);
						if ($sz !== false) {
							$tot += (float)$sz;
						}
					}
				}
				$statics ['size_bytes'] = (float)$tot;
				list($cnt, $approx) = $this->format_number((float)$tot, 1024);
				$statics ['size'] = $cnt . ' ' . $binunit [$approx];
			} else {
				$statics = array('count' => 0, 'size' => '0 Bytes', 'size_bytes' => 0.0);
			}
			$this->smarty->assign('statics', $statics);

			// Images and Attachments storage
			$images = array('count' => 0, 'size' => '0 Bytes', 'size_bytes' => 0.0);
			$attachs = array('count' => 0, 'size' => '0 Bytes', 'size_bytes' => 0.0);
			$imgRoot = (defined('ABS_PATH') ? ABS_PATH : '') . IMAGES_DIR;
			$attRoot = (defined('ABS_PATH') ? ABS_PATH : '') . ATTACHS_DIR;
			// Exclude ".captions.conf" and preview thumbnails in ".thumbs" folders
			$img = plugin_storage_dirsize_get($imgRoot, 'images', 120, true, true);
			$att = plugin_storage_dirsize_get($attRoot, 'attachs', 120);
			if (is_array($img)) {
				$images ['size_bytes'] = (float)$img ['size'];
				$images ['count'] = (int)$img ['count'];
				list($cnt, $ap) = $this->format_number((float)$img ['size'], 1024);
				$images ['size'] = $cnt . ' ' . $binunit [$ap];
			}
			if (is_array($att)) {
				$attachs ['size_bytes'] = (float)$att ['size'];
				$attachs ['count'] = (int)$att ['count'];
				list($cnt, $ap) = $this->format_number((float)$att ['size'], 1024);
				$attachs ['size'] = $cnt . ' ' . $binunit [$ap];
			}
			$this->smarty->assign('images', $images);
			$this->smarty->assign('attachs', $attachs);

			// FlatPress folder total and disk usage
			$storage = array('fp_size' => '0 Bytes', 'fp_size_bytes' => 0.0, 'total' => 'n/a', 'total_bytes' => 0.0, 'free' => 'n/a', 'free_bytes' => 0.0, 'pct' => 'n/a');
			$root = defined('BASE_DIR') ? BASE_DIR : dirname(__FILE__, 3);
			$ttl = 120;
			$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
			$ck = 'fp:storage:dirsize:v1:' . sha1($root);
			$sz = false;
			if ($apcu_on) {
				$ok = false;
				$val = apcu_get($ck, $ok);
				if ($ok && is_array($val) && isset($val ['size'])) {
					$sz = (float)$val ['size'];
				}
			}
			if ($sz === false) {
				// Try file cache fallback
				$cf = (defined('FP_CONTENT') ? FP_CONTENT : 'fp-content/') . 'cache/storage.dirsize.json';
				if (@file_exists($cf)) {
					$mt = @filemtime($cf);
					if ($mt !== false && (time() - (int)$mt) < $ttl) {
						$raw = io_load_file($cf);
						if ($raw !== false) {
							$jd = @json_decode($raw, true);
							if (is_array($jd) && isset($jd ['size'])) {
								$sz = (float)$jd ['size'];
							}
						}
					}
				}
			}
			if ($sz === false) {
				$sz = 0.0;
				try {
					$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
					foreach ($it as $fi) {
						/** @var SplFileInfo $fi */
						if ($fi->isLink()) {
							continue;
						}
						if ($fi->isFile()) {
							$fs = @$fi->getSize();
							if (is_int($fs) || is_float($fs)) {
								$sz += (float)$fs;
							}
						}
					}
				} catch (Throwable $e) {
					// ignore
				}
				if ($apcu_on) {
					@apcu_set($ck, array('size' => $sz), $ttl);
				}
				// Write file cache
				$payload = json_encode(array('size' => $sz));
				if (function_exists('io_write_file')) {
					@io_write_file($cf, $payload);
				} else {
					if (plugin_storage_can_use('file_put_contents')) {
						@file_put_contents($cf, $payload);
					}
				}
			}
			$storage ['fp_size_bytes'] = (float)$sz;
			list($cnt, $approx) = $this->format_number((float)$sz, 1024);
			$storage ['fp_size'] = $cnt . ' ' . $binunit [$approx];

			$tot = plugin_storage_can_use('disk_total_space') ? @disk_total_space($root) : false;
			$fre = plugin_storage_can_use('disk_free_space') ? @disk_free_space($root) : false;
			if ($tot !== false) {
				$storage ['total_bytes'] = (float)$tot;
				list($c2, $a2) = $this->format_number((float)$tot, 1024);
				$storage ['total'] = $c2 . ' ' . $binunit[$a2];
			}
			if ($fre !== false) {
				$storage ['free_bytes'] = (float)$fre;
				list($c3, $a3) = $this->format_number((float)$fre, 1024);
				$storage ['free'] = $c3 . ' ' . $binunit[$a3];
			}
			if (!empty($storage ['total_bytes']) && $storage ['total_bytes'] > 0) {
				$pct = ($storage ['fp_size_bytes'] / $storage ['total_bytes']) * 100.0;
				// Show finer details with a very small proportion
				$digits = ($pct < 0.1) ? 3 : 2;
				$storage ['pct'] = number_format($pct, $digits, '.', '');
			}
			$this->smarty->assign('storage', $storage);

			// Webspace quota (user-level)
			$quota = plugin_storage_detect_webspace_quota($root, 3600);
			$quota_view = array('total' => 'n/a', 'total_bytes' => 0.0, 'used' => 'n/a', 'used_bytes' => 0.0, 'free' => 'n/a', 'free_bytes' => 0.0, 'source' => (string)($quota ['source'] ?? 'unknown'), 'fp_pct_ws' => 'n/a');
			if (is_array($quota) && !empty($quota ['total_bytes'])) {
				$quota_view ['total_bytes'] = (float)$quota ['total_bytes'];
				list($cnt, $ap) = $this->format_number((float)$quota ['total_bytes'], 1024);
				$quota_view ['total'] = $cnt . ' ' . $binunit [$ap];
				if (isset($quota ['used_bytes']) && is_numeric($quota ['used_bytes'])) {
					$quota_view ['used_bytes'] = (float)$quota ['used_bytes'];
					list($cu, $au) = $this->format_number((float)$quota ['used_bytes'], 1024);
					$quota_view ['used'] = $cu . ' ' . $binunit [$au];
				}
				if (isset($quota ['free_bytes']) && is_numeric($quota ['free_bytes'])) {
					$quota_view ['free_bytes'] = (float)$quota ['free_bytes'];
					list($cf, $af) = $this->format_number((float)$quota ['free_bytes'], 1024);
					$quota_view ['free'] = $cf . ' ' . $binunit [$af];
				}
				// FP share of web space (if total > 0)
				if ($quota_view ['total_bytes'] > 0) {
					$pct2 = ($storage ['fp_size_bytes'] / $quota_view ['total_bytes']) * 100.0;
					$digits2 = ($pct2 < 0.1) ? 3 : 2;
					$quota_view ['fp_pct_ws'] = number_format($pct2, $digits2, '.', '');
				}
			}
			$this->smarty->assign('quota', $quota_view);

		}
	}

	// Register to 'main' menu
	admin_addpanelaction('uploader', 'storage', true);
}
?>
