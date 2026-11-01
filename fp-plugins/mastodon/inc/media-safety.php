<?php
/** Bounded media I/O and optional video inspection. PHP 7.2+. */

/** @return void */
function plugin_mastodon_download_budget_start() {
	$GLOBALS ['plugin_mastodon_download_budget'] = array('bytes' => 0, 'requests' => 0);
	$GLOBALS ['plugin_mastodon_media_import_error'] = '';
}

/** @param string $key @return int */
function plugin_mastodon_download_limit($key) {
	$names = array('image' => 'PLUGIN_MASTODON_DOWNLOAD_IMAGE_BYTES', 'video' => 'PLUGIN_MASTODON_DOWNLOAD_AV_BYTES', 'total' => 'PLUGIN_MASTODON_DOWNLOAD_TOTAL_BYTES', 'requests' => 'PLUGIN_MASTODON_DOWNLOAD_REQUESTS');
	$value = constant($names [$key]);
	if (isset($GLOBALS ['plugin_mastodon_test_download_limits'] [$key])) {
		$value = $GLOBALS ['plugin_mastodon_test_download_limits'] [$key];
	}
	return max(1, (int) $value);
}

/**
 * All transports feed this same bounded sink. Failed attempts still consume bytes.
 * @param resource $handle
 * @param string $chunk
 * @param int $bytes
 * @param int $limit
 * @param string $error
 * @return int
 */
function plugin_mastodon_download_write($handle, $chunk, &$bytes, $limit, &$error) {
	$length = strlen($chunk);
	$budget = &$GLOBALS ['plugin_mastodon_download_budget'];
	$budget ['bytes'] += $length;
	if ($budget ['bytes'] > plugin_mastodon_download_limit('total')) {
		$error = 'media_download_total_budget_exhausted';
		return 0;
	}
	if ($bytes + $length > $limit) {
		$error = 'media_download_file_too_large';
		return 0;
	}
	$offset = 0;
	while ($offset < $length) {
		$written = fwrite($handle, substr($chunk, $offset));
		if ($written === false || $written === 0) {
			$error = 'media_download_write_failed';
			return 0;
		}
		$offset += $written;
	}
	$bytes += $length;
	return $length;
}

/** @param string $url @return string */
function plugin_mastodon_media_origin($url) {
	$p = parse_url($url);
	if (!is_array($p) || empty($p ['host']) || empty($p ['scheme']) || isset($p ['user']) || isset($p ['pass']) || !in_array(strtolower($p ['scheme']), array('http', 'https'), true)) {
		return '';
	}
	return strtolower($p ['scheme'] . '://' . $p ['host']) . ':' . (isset($p ['port']) ? (int) $p ['port'] : (strtolower($p ['scheme']) === 'https' ? 443 : 80));
}

/** @param string $url @param string $location @return string */
function plugin_mastodon_media_redirect($url, $location) {
	if (preg_match('#^https?://#i', $location)) {
		return $location;
	}
	$p = parse_url($url);
	if (!is_array($p) || empty($p ['host']) || preg_match('/[\r\n]/', $location)) {
		return '';
	}
	if (strpos($location, '//') === 0) {
		return $p ['scheme'] . ':' . $location;
	}
	$base = $p ['scheme'] . '://' . $p ['host'] . (isset($p ['port']) ? ':' . $p ['port'] : '');
	$path = isset($p ['path']) ? $p ['path'] : '/';
	if (strpos($location, '?') === 0) {
		return $base . $path . $location;
	}
	return $base . (strpos($location, '/') === 0 ? $location : str_replace('\\', '/', dirname($path)) . '/' . $location);
}

/**
 * Download directly to a caller-owned staging file. Never return a binary body.
 * @param string $url
 * @param array<int|string, string> $headers
 * @param string $destination
 * @param string $type
 * @return array<string, mixed>
 */
function plugin_mastodon_download_to_file($url, $headers, $destination, $type) {
	if (!isset($GLOBALS ['plugin_mastodon_download_budget'])) {
		plugin_mastodon_download_budget_start();
	}
	$limit = plugin_mastodon_download_limit($type === 'image' ? 'image' : 'video');
	$result = array('ok' => false, 'code' => 0, 'headers' => array(), 'body' => '', 'path' => '', 'bytes' => 0, 'error' => 'media_download_failed');
	$initialOrigin = plugin_mastodon_media_origin($url);
	$deadline = microtime(true) + 180;
	for ($redirect = 0; $redirect <= 5; $redirect++) {
		$budget = &$GLOBALS ['plugin_mastodon_download_budget'];
		if ($initialOrigin === '' || plugin_mastodon_media_origin($url) === '') {
			$result ['error'] = 'media_download_invalid_url';
			break;
		}
		if ($budget ['requests'] >= plugin_mastodon_download_limit('requests') || $budget ['bytes'] >= plugin_mastodon_download_limit('total')) {
			$result ['error'] = 'media_download_total_budget_exhausted';
			break;
		}
		$timeout = (int) ceil($deadline - microtime(true));
		if ($timeout <= 0) {
			$result ['error'] = 'media_download_timeout';
			break;
		}
		$budget ['requests']++;
		$requestHeaders = $headers;
		if (plugin_mastodon_media_origin($url) !== $initialOrigin) {
			$requestHeaders = array_values(array_filter($requestHeaders, function ($h) {
				return !preg_match('/^(authorization|cookie):/i', $h);
			}));
		}
		$handle = @fopen($destination, 'wb');
		if (!$handle) {
			$result ['error'] = 'media_download_open_failed';
			break;
		}
		$bytes = 0;
		$error = '';
		$responseHeaders = array();
		$code = 0;
		$sink = function ($chunk) use ($handle, &$bytes, $limit, &$error) {
			return plugin_mastodon_download_write($handle, $chunk, $bytes, $limit, $error);
		};
		// Mocks use the same sink and limits; only the network boundary is replaced.
		if (isset($GLOBALS ['plugin_mastodon_test_http_responses']) || !empty($GLOBALS ['plugin_mastodon_test_http_no_network'])) {
			$mock = plugin_mastodon_http_request('GET', $url, $requestHeaders, '', '', $timeout);
			$code = (int) $mock ['code']; $responseHeaders = $mock ['headers'];
			if (!empty($mock ['ok'])) {
				$body = (string) $mock ['body'];
				for ($offset = 0; $offset < strlen($body); $offset += 65536) {
					if ($sink(substr($body, $offset, 65536)) === 0) {
						break;
					}
				}
			} elseif ($code < 300 || $code >= 400) {
				$error = 'media_download_http_' . $code;
			}
		} elseif (function_exists('curl_init') && empty($GLOBALS ['plugin_mastodon_test_download_stream'])) {
			$ch = curl_init($url);
			if ($ch === false) {
				fclose($handle);
				$result ['error'] = 'media_download_transport_failed';
				break;
				}
			curl_setopt_array($ch, array(
				CURLOPT_FOLLOWLOCATION => false, CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_TIMEOUT => $timeout,
				CURLOPT_HTTPHEADER => $requestHeaders, CURLOPT_USERAGENT => 'FlatPress-Mastodon/2.8',
				CURLOPT_HEADERFUNCTION => function ($curl, $line) use (&$responseHeaders, &$code) {
					if (preg_match('#^HTTP/\S+\s+(\d+)#', $line, $m)) {
						$code = (int) $m [1];
						$responseHeaders = array();
					}
					elseif (strpos($line, ':') !== false) {
						list($k, $v) = explode(':', $line, 2);
						$responseHeaders [strtolower(trim($k))] = trim($v);
					}
					return strlen($line);
				},
				CURLOPT_WRITEFUNCTION => function ($curl, $chunk) use ($sink, &$code) {
					return $code >= 200 && $code < 300 ? $sink($chunk) : 0;
				}
			));
			curl_exec($ch);
			if (curl_errno($ch) !== 0 && $error === '' && ($code < 300 || $code >= 400)) {
				$error = 'media_download_transport_failed';
			}
			if (!is_php85_plus()) {
				curl_close($ch);
			}
		} elseif (ini_get('allow_url_fopen')) {
			$context = stream_context_create(array('http' => array('method' => 'GET', 'header' => implode("\r\n", $requestHeaders), 'timeout' => min(15, $timeout), 'ignore_errors' => true, 'follow_location' => 0)));
			$stream = @fopen($url, 'rb', false, $context);
			if ($stream) {
				$meta = stream_get_meta_data($stream);
				$parsed = plugin_mastodon_parse_http_response_headers(isset($meta ['wrapper_data']) ? $meta ['wrapper_data'] : array());
				$code = $parsed ['code']; $responseHeaders = $parsed ['headers'];
				while ($code >= 200 && $code < 300 && !feof($stream)) {
					if (microtime(true) >= $deadline) {
						$error = 'media_download_timeout';
						break;
					}
					stream_set_timeout($stream, max(1, min(15, (int) ceil($deadline - microtime(true)))));
					// Normalize read failure to an empty chunk; both fail before EOF.
					$chunk = (string) fread($stream, 65536);
					if ($chunk === '' && !feof($stream)) {
						$error = 'media_download_read_failed';
						break;
					}
					if ($chunk !== '' && $sink($chunk) === 0) {
						break;
					}
				}
				fclose($stream);
			} else {
				$error = 'media_download_transport_failed';
			}
		} else {
			$error = 'media_download_no_transport';
		}
		fclose($handle);
		$result ['code'] = $code; $result ['headers'] = $responseHeaders; $result ['bytes'] = $bytes;
		if ($code >= 300 && $code < 400 && !empty($responseHeaders ['location']) && $error === '') {
			$url = plugin_mastodon_media_redirect($url, $responseHeaders ['location']); continue;
		}
		if ($error === '' && $code >= 200 && $code < 300 && $bytes > 0) {
			if (isset($responseHeaders ['content-length']) && ctype_digit((string) $responseHeaders ['content-length']) && (float) $responseHeaders ['content-length'] !== (float) $bytes) {
				$error = 'media_download_truncated';
			} else {
				$result ['ok'] = true;
				$result ['path'] = $destination;
				$result ['error'] = '';
				return $result;
			}
		}
		$result ['error'] = $error !== '' ? $error : 'media_download_http_' . $code;
		break;
	}
	if (is_file($destination)) {
		@unlink($destination);
	}
	return $result;
}

/**
 * Bounded ffprobe process. Missing tooling defers validation to Mastodon.
 * @param string $path
 * @return array<string, mixed>
 */
function plugin_mastodon_probe_video($path) {
	if (isset($GLOBALS ['plugin_mastodon_test_video_metadata'])) {
		return $GLOBALS ['plugin_mastodon_test_video_metadata'];
	}
	if (!function_exists('proc_open')) {
		return array('available' => false);
	}
	$binary = defined('PLUGIN_MASTODON_FFPROBE') ? (string) constant('PLUGIN_MASTODON_FFPROBE') : '';
	if (!defined('PLUGIN_MASTODON_FFPROBE')) {
		foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $directory) {
			$candidate = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . (DIRECTORY_SEPARATOR === '\\' ? 'ffprobe.exe' : 'ffprobe');
			if (is_file($candidate) && is_executable($candidate)) {
				$binary = $candidate;
				break;
			}
		}
	}
	if ($binary === '' || !is_file($binary)) {
		return array('available' => false);
	}
	$key = $path . ':' . (string) @filemtime($path) . ':' . (string) @filesize($path);
	$cached = plugin_mastodon_runtime_cache_get('video_probe', $key, $hit);
	if ($hit && is_array($cached)) {
		return $cached;
	}
	$command = (DIRECTORY_SEPARATOR === '\\' ? '' : 'exec ') . escapeshellarg($binary) . ' -v error -protocol_whitelist file,pipe -select_streams v:0 -show_entries stream=width,height,avg_frame_rate,r_frame_rate -of json ' . escapeshellarg($path);
	$probeDir = ABS_PATH . PLUGIN_MASTODON_STATE_DIR . 'tmp';
	if (!plugin_mastodon_media_prepare_directory($probeDir)) {
		return array('available' => true, 'error' => 'video_probe_io_failed');
	}
	$outPath = tempnam($probeDir, 'probe-out-');
	$errPath = tempnam($probeDir, 'probe-err-');
	if ($outPath === false || $errPath === false) {
		if ($outPath !== false) {
			@unlink($outPath);
		}
		if ($errPath !== false) {
			@unlink($errPath);
		}
		return array('available' => true, 'error' => 'video_probe_io_failed');
	}
	$pipes = array();
	$process = @proc_open($command, array(0 => array('pipe', 'r'), 1 => array('file', $outPath, 'wb'), 2 => array('file', $errPath, 'wb')), $pipes, null, null, array('bypass_shell' => true));
	if (!is_resource($process)) {
		@unlink($outPath);
		@unlink($errPath);
		return array('available' => false);
	}
	$start = microtime(true); $exit = -1; $limited = false;
	fclose($pipes [0]);
	do {
		clearstatcache(true, $outPath);
		clearstatcache(true, $errPath);
		if (microtime(true) - $start > 10 || filesize($outPath) > 65536 || filesize($errPath) > 65536) {
			$limited = true; proc_terminate($process);
			break;
		}
		$status = proc_get_status($process);
		if (!$status ['running']) {
			$exit = (int) $status ['exitcode'];
			break;
		}
		usleep(10000);
	} while (true);
	proc_close($process);
	$output = (string) file_get_contents($outPath, false, null, 0, 65537);
	@unlink($outPath);
	@unlink($errPath);
	if ($limited || strlen($output) > 65536) {
		return array('available' => true, 'error' => 'video_probe_budget_exhausted');
	}
	$data = json_decode($output, true);
	if ($exit === 127 || $exit === 9009) {
		return array('available' => false);
	}
	if ($exit !== 0) {
		return array('available' => true, 'error' => 'video_probe_failed');
	}
	$stream = isset($data ['streams'] [0]) && is_array($data ['streams'] [0]) ? $data ['streams'] [0] : array();
	$stream ['available'] = true;
	return plugin_mastodon_runtime_cache_set('video_probe', $key, $stream);
}

/** @param mixed $value @return float */
function plugin_mastodon_video_rate($value) {
	$parts = explode('/', (string) $value, 2);
	if (!is_numeric($parts [0])) {
		return 0.0;
	}
	return isset($parts [1]) ? (is_numeric($parts [1]) && (float) $parts [1] > 0 ? (float) $parts [0] / (float) $parts [1] : 0.0) : (float) $parts [0];
}

/** @param string $path @param array<string, mixed> $configuration @return string */
function plugin_mastodon_validate_video_metadata($path, $configuration) {
	$meta = plugin_mastodon_probe_video($path);
	if (empty($meta ['available'])) {
		plugin_mastodon_log('Video metadata unavailable; Mastodon must validate this upload.');
		return defined('PLUGIN_MASTODON_REQUIRE_VIDEO_METADATA') && constant('PLUGIN_MASTODON_REQUIRE_VIDEO_METADATA') ? 'video_metadata_unavailable' : '';
	}
	if (!empty($meta ['error'])) { return (string) $meta ['error']; }
	$width = isset($meta ['width']) ? (float) $meta ['width'] : 0;
	$height = isset($meta ['height']) ? (float) $meta ['height'] : 0;
	$fps = max(plugin_mastodon_video_rate(isset($meta ['avg_frame_rate']) ? $meta ['avg_frame_rate'] : 0), plugin_mastodon_video_rate(isset($meta ['r_frame_rate']) ? $meta ['r_frame_rate'] : 0));
	if ($width <= 0 || $height <= 0 || $fps <= 0 || !is_finite($fps)) {
		return 'video_metadata_invalid';
	}
	$media = isset($configuration ['media_attachments']) ? $configuration ['media_attachments'] : array();
	if (!empty($media ['video_matrix_limit']) && $width * $height > (float) $media ['video_matrix_limit']) {
		return 'video_matrix_limit_exceeded';
	}
	if (!empty($media ['video_frame_rate_limit']) && floor($fps) > (float) $media ['video_frame_rate_limit']) {
		return 'video_frame_rate_limit_exceeded';
	}
	return '';
}

/**
 * Refresh once per content run when the persisted snapshot is older than one hour.
 * Errors retain the previous snapshot and are retried after five minutes.
 * @param array<string, string> $options
 * @return void
 */
function plugin_mastodon_refresh_instance_if_due(&$options) {
	$now = time();
	$fetched = strtotime(isset($options ['instance_info_fetched_at']) ? $options ['instance_info_fetched_at'] : '');
	$failed = strtotime(isset($options ['instance_info_error_at']) ? $options ['instance_info_error_at'] : '');
	if (($fetched !== false && $fetched <= $now && $now - $fetched < 3600) || ($failed !== false && $failed <= $now && $now - $failed < 300)) {
		return;
	}
	$response = plugin_mastodon_refresh_instance_information($options);
	if (!empty($response ['ok'])) {
		$options ['instance_info_json'] = (string) json_encode($response ['json']);
		$options ['instance_info_url'] = $options ['instance_url'];
		$options ['instance_info_fetched_at'] = gmdate('c', $now);
		$options ['instance_info_error'] = ''; $options ['instance_info_error_at'] = '';
	} else {
		$options ['instance_info_error_at'] = gmdate('c', $now);
		$options ['instance_info_error'] = plugin_mastodon_response_error_message($response);
	}
}

/**
 * Parse PHP memory_limit without requiring PHP 8.2 ini_parse_quantity().
 * @param string $value
 * @return float|null -1 means unlimited; null means unknown/invalid.
 */
function plugin_mastodon_memory_limit_bytes($value) {
	$value = trim($value);
	if ($value === '-1') {
		return -1.0;
	}
	if (!preg_match('/^([0-9]+)\s*([KMG]?)$/i', $value, $matches)) {
		return null;
	}
	$multipliers = array('' => 1, 'K' => 1024, 'M' => 1048576, 'G' => 1073741824);
	$bytes = (float) $matches [1] * $multipliers [strtoupper($matches [2])];
	return is_finite($bytes) ? $bytes : null;
}

/**
 * Reserve room for body copies, the stream context, response and plugin work.
 * Optional arguments allow deterministic budget tests without allocating large files.
 * @param float|int $bytes
 * @param string|null $limit
 * @param int|null $usage
 * @return string
 */
function plugin_mastodon_multipart_memory_error($bytes, $limit = null, $usage = null) {
	$cap = max(1, (int) PLUGIN_MASTODON_MULTIPART_MAX_BYTES);
	if (!is_finite((float) $bytes) || $bytes < 0 || $bytes > $cap) {
		return 'multipart_body_limit_exceeded';
	}
	$limitBytes = plugin_mastodon_memory_limit_bytes($limit === null ? (string) ini_get('memory_limit') : $limit);
	if ($limitBytes === null) {
		return 'multipart_memory_limit_unknown';
	}
	if ($limitBytes < 0) {
		return '';
	}
	$usage = $usage === null ? memory_get_usage(true) : max(0, $usage);
	$reserve = max(8388608, (int) PLUGIN_MASTODON_MULTIPART_MEMORY_RESERVE);
	return 4.0 * $bytes + $reserve > $limitBytes - $usage ? 'multipart_memory_budget_exceeded' : '';
}

/**
 * Preflight the entire multipart body before reading file contents. Read bounded
 * chunks and recheck memory while building; reject changed/truncated files.
 * @param array<string, mixed> $fields
 * @param string $boundary
 * @return array{ok:bool,body:string,error:string}
 */
function plugin_mastodon_build_multipart_body($fields, $boundary) {
	$failure = array('ok' => false, 'body' => '', 'error' => 'multipart_file_unreadable');
	$parts = array();
	$closing = '--' . $boundary . "--\r\n";
	$total = (float) strlen($closing);
	foreach ($fields as $name => $value) {
		$path = ''; $text = ''; $size = 0;
		$header = '--' . $boundary . "\r\n" . 'Content-Disposition: form-data; name="' . addcslashes((string) $name, "\"\\") . '"';
		if (is_array($value) && !empty($value ['__file_path'])) {
			$path = (string) $value ['__file_path'];
			clearstatcache(true, $path);
			$size = @filesize($path);
			if (!is_file($path) || $size === false) {
				return $failure;
			}
			$fileName = isset($value ['__file_name']) ? (string) $value ['__file_name'] : basename($path);
			$mime = isset($value ['__mime_type']) ? (string) $value ['__mime_type'] : plugin_mastodon_media_guess_mime_type($path);
			$header .= '; filename="' . addcslashes($fileName, "\"\\") . '"' . "\r\nContent-Type: " . $mime;
		} else {
			$text = is_scalar($value) ? (string) $value : '';
			$size = strlen($text);
		}
		$header .= "\r\n\r\n";
		$total += strlen($header) + (float) $size + 2;
		$error = plugin_mastodon_multipart_memory_error($total);
		if ($error !== '') {
			$failure ['error'] = $error;
			return $failure;
		}
		$parts [] = array('header' => $header, 'path' => $path, 'text' => $text, 'size' => $size);
	}
	$error = plugin_mastodon_multipart_memory_error($total);
	if ($error !== '') {
		$failure ['error'] = $error;
		return $failure;
	}
	$body = '';
	foreach ($parts as $part) {
		$error = plugin_mastodon_multipart_memory_error($total);
		if ($error !== '') {
			$failure ['error'] = $error;
			return $failure;
		}
		$body .= $part ['header'];
		if ($part ['path'] !== '') {
			$handle = @fopen($part ['path'], 'rb');
			if (!$handle) {
				return $failure;
			}
			$stat = fstat($handle);
			if (!is_array($stat) || $stat ['size'] !== $part ['size'] || ($stat ['mode'] & 0170000) !== 0100000) {
				fclose($handle);
				$failure ['error'] = 'multipart_file_changed';
				return $failure;
			}
			$remaining = $part ['size'];
			while ($remaining > 0) {
				$error = plugin_mastodon_multipart_memory_error($total);
				if ($error !== '') {
					fclose($handle);
					$failure ['error'] = $error;
					return $failure;
				}
				$chunk = (string) fread($handle, min(65536, $remaining));
				if ($chunk === '') {
					fclose($handle);
					$failure ['error'] = 'multipart_file_changed';
					return $failure;
				}
				$body .= $chunk;
				$remaining -= strlen($chunk);
			}
			$extra = (string) fread($handle, 1);
			fclose($handle);
			if ($extra !== '') {
				$failure ['error'] = 'multipart_file_changed';
				return $failure;
			}
		} else {
			$body .= $part ['text'];
		}
		$body .= "\r\n";
	}
	$body .= $closing;
	$error = plugin_mastodon_multipart_memory_error(strlen($body));
	if ($error !== '') {
		$failure ['error'] = $error;
		return $failure;
	}
	return array('ok' => true, 'body' => $body, 'error' => '');
}
?>
