<?php
	/**
	 * Blogdb lib
	 * provides access to the blog. 
	 *
	 * @author NoWhereMan <nowhereman@phreaker.net>
	 */

	/**
	 * entry id prefix and identifier
	 */
	define('BDB_ENTRY', 'entry');
	/**
	 * comment id prefix and identifier
	 */
	define('BDB_COMMENT', 'comment');

	/**
	 * default file extension
	 */
	define('EXT', '.txt');

	/**
	 * function bdb_idtofile
	 *
	 * <p>Takes the id $id and returns a filepath</p>
	 *
	 * @param string $id ID formatted like "prefixYYMMDD-HHMMSS.EXT"
	 * @param string|null $type Optional type (BDB_ENTRY, BDB_COMMENT)
	 * @return string|false Path to the file, or false if ID is invalid
	 */
	function bdb_idtofile($id, $type = null) {

		$fname = $id . EXT;

		$date = date_from_id($id);

		if (!$date) {
			return false;
		}

		$path = CONTENT_DIR . $date ['y'] . '/' . $date ['m'] . '/';
		if ($type == null || $type == BDB_ENTRY) {
			$path .= $fname;
		} elseif ($type == BDB_COMMENT) {
			$path .= $id . '/comments/';
		}

		return $path;

	}


	/**
	 * function bdb_idfromtime
	 *
	 * <p>Returns a well formatted id for entry type specified in $type 
	 * and date eventually specified in $date; </p>
	 *
	 * @param string $type one of the BDB_ constants
	 * @param int $timestamp UNIX timestamp
	 * @return string
	 */
	function bdb_idfromtime($type, $timestamp = null) {
		if (!$timestamp) {
			$timestamp = time();
		}

		/*if (!ctype_digit($timestamp)) {
			trigger_error("bdb_idfromtime():
			$timestamp Not a valid timestamp", E_USER_WARNING);
		}*/
		return $type . date('ymd-His', $timestamp);
	}


	/**
	 * function bdb_filetoid
	 *
	 * <p>Cosmetic wrapper to basename($file, EXT)</p>
	 *
	 * @param string $file filepath of the blogdb entry
	 * @return string
	 *
	 * @todo validate returned id
	 */
	function bdb_filetoid($file) {

		return basename($file, EXT);

	}


	/**
	 * function bdb_parse_entry
	 *
	 * <p>Parses the entry file passed as parameter; returns an associative array
	 * of the file content</p>
	 * Tipically, entry arrays are usually made of these keys
	 * - VERSION:       SimplePHPBlog or compatible blogs' version identifier string
	 * - SUBJECT:       Subject of the entry
	 * - CONTENT:       Content of the entry
	 * - DATE:          UNIX filestamp to format by {@link date_format()}.
	 * 
	 * comments usually provide also
	 * - NAME:          author name
	 * - EMAIL:         author email (if any)
	 * - URL:           author website url (if any)
	 *
	 * A common usage of the function could be
	 * <code>
	 * <?php
	 * $entry = bdb_parse_entry(bdb_filetoid($myid));
	 * ?>
	 * </code>
	 *
	 * @param string $id ID or file path to parse
	 * @param string|null $type Optional type (e.g., BDB_ENTRY, BDB_COMMENT)
	 * @return array|false Parsed key-value pairs, or false if file not found
	 */
	function bdb_parse_entry($id, $type = null) {

		static $__bdb_entry_cache = array();

		if (file_exists($id)) {
			$file = $id;
		} else {
			$file = bdb_idtofile($id, $type);
		}

		if (isset($__bdb_entry_cache [$file])) {
			return $__bdb_entry_cache [$file];
		}

		// Optional APCu cache of parsed entry (shared hot cache across requests)
		$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
		if ($apcu_on) {
			clearstatcache(true, $file);
			$mt = @filemtime($file);
			if ($mt !== false) {
				$sz = (int) @filesize($file);
				$akey = 'fp:entry:parsed:' . basename($file) . ':' . $mt . ':' . $sz;
				$ahit = false;
				$aval = apcu_get($akey, $ahit);
				if ($ahit && is_array($aval)) {
					$__bdb_entry_cache [$file] = $aval;
					return $aval;
				}
			}
		}

		if (file_exists($file)) {
			$contents = io_load_file($file);

			// TODO: here we must add compatibility to encoding conversion!
			// Legacy mode: ignore array key case when DUMB_MODE_ENABLED is false (default)
			// if "dumb" (legacy :D) mode is enabled (set to true in default.php, then we set parsing
			// to ignore array key case (defaults to true i.e. check them to be uppercase or failing otherwise

			/** @var bool $ignoreCase */
			$ignoreCase = !(defined('DUMB_MODE_ENABLED') && call_user_func('constant', 'DUMB_MODE_ENABLED'));

			$entry = utils_kexplode($contents, '|', $ignoreCase);

			// Existing ID or file name (without extension)
			$rawId = isset($entry ['id']) && $entry ['id'] !== '' ? (string)$entry ['id'] : (string)pathinfo($file, PATHINFO_FILENAME);
			$normId = preg_replace('/[^A-Za-z0-9_-]/', '-', $rawId);
			$normId = trim($normId, '-_');
			if ($normId === '' || $normId === null) {
				// Exactly matching FlatPress entry name?
				$base = (string)pathinfo($file, PATHINFO_FILENAME);
				if (preg_match('/^entry(\d{6}-\d{6})$/i', $base, $m)) {
					$normId = 'entry' . strtolower($m [1]);
				} else {
					// Timestamp fragment somewhere in the name?
					if (preg_match('/(\d{6})[-_](\d{6})/', $base, $t)) {
						$normId = 'entry' . strtolower($t [1] . '-' . $t [2]);
					} else {
						// Generate from file time (FlatPress convention)
						clearstatcache(true, $file);
						$mt = @filemtime($file);
						if ($mt !== false) {
							$normId = 'entry' . gmdate('ymd-His', $mt);
						} else {
							// Last resort: stable hash (extreme special cases)
							$normId = 'entry-' . substr(sha1($file), 0, 12);
						}
					}
				}
			}

			// Lowercase letters (IDs lowercase in themes/URLs)
			$entry ['id'] = strtolower($normId);

			$__bdb_entry_cache [$file] = $entry;

			// Fill APCu parsed-entry cache (bounded TTL)
			if ($apcu_on && isset($mt) && $mt !== false) {
				$sz = isset($sz) ? $sz : (int) @filesize($file);
				$akey = 'fp:entry:parsed:' . basename($file) . ':' . $mt . ':' . $sz;
				$ttl = max(0, (int) ($_ENV ['FP_APCU_ENTRY_TTL'] ?? 600));
				apcu_set($akey, $entry, $ttl);
			}

			return $entry;
		} else {
			return false;
		}

	}

?>
