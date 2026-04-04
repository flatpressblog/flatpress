<?php
class plugin_tag_init {

	// The tag_db object, null for now
	var $tag_db = null;

	// walker_array: the array used from the fake walker
	var $walker_array = array();

	// How many entries there are...
	var $fpdb_len = 0;

	// Is the walker still valid?
	var $valid = false;

	/**
	 * plugin_tag_init
	 *
	 * This function is the constructor of the init_class.
	 * It adds some callbacks to init hook and to
	 * prettyurls_unhandled_url hook.
	 *
	 * @param object $tag_db An object of the tag database
	 */
	function __construct(&$tag_db) {
		add_filter('init', array(&$this, 'hook'), 25);
		add_filter('prettyurls_unhandled_url', array(&$this, 'rewrite'), 1);
		$this->tag_db = &$tag_db;
	}

	/**
	 * hook
	 *
	 * This function is called by init hook.
	 * It checks for the tag param and it creates
	 * a fake cat (whose id is -255) with a fake walker.
	 */
	function hook() {
		global $fpdb, $fp_params;
		if (empty($fp_params ['tag'])) {
			// We stop here
			return true;
		}
		$tag = $fp_params ['tag'];

		// Load the database
		$file = $this->tag_db->open_file($this->tag_db->tagfile($tag));

		if (!isset($file [$tag]) || count($file [$tag]) === 0) {
			// No entries or no tag: 404 error
			$fp_params ['entry'] = 'entry000000-000000';
			return false;
		}

		// Sets the entries to $this->walker_array (with ids shifted
		// with keys) and sort them
		$this->walker_array = array_map('entry_idtokey', $file [$tag]);
		rsort($this->walker_array);

		$l = count($this->walker_array);

		if(isset($fp_params ['count'])) {
			$fp_params ['count'] = $fp_params ['count'] > $l ? $l : $fp_params ['count'];
		}

		// Create the fake walker and the fake cat
		$fp_params ['cat'] =- 0xFF;
		$fpdb->_indexer [-0xFF] = &$this;
		$this->fpdb_len = $l;
		reset($this->walker_array);

		// Solve problems with Frontpage plugin
		if (isset($fp_params ['not']) && @!constant('PLUGIN_TAG_ALLOW_NOT')) {
			unset($fp_params ['not']);
		}

		// We have to do it the dirty PrettyURLs paging
		if (class_exists('Plugin_PrettyURLs')) {
			global $plugin_prettyurls;
			$puBackup = $plugin_prettyurls;
			$plugin_prettyurls = new tag_prettyurls($puBackup);
			$plugin_prettyurls->tag_link = apply_filters('tag_link', $fp_params ['tag']);
		}

		return true;
	}

	/**
	 * current_key
	 *
	 * This function is used from the walker.
	 *
	 * @return string|false The key of the current entry
	 */
	function current_key() {
		return current($this->walker_array);
	}

	/**
	 * next
	 *
	 * This function is used from the walker.
	 *
	 * @return string|false The key of the next entry in the list
	 */
	function next() {
		$n = next($this->walker_array);
		if ($n === false) {
			$this->valid = false;
		}
		return $n;
	}

	/**
	 * length
	 *
	 * This function is used from the walker.
	 *
	 * @return int How many entries there are in the tag walker
	 */
	function length() {
		return $this->fpdb_len;
	}

	/**
	 * walker
	 *
	 * This function is used from FPDB.
	 *
	 * @return object $this by reference
	 */
	function &walker() {
		$this->valid = true;
		return $this;
	}

	/**
	 * rewrite
	 *
	 * This function is used as callback of hook
	 * prettyurls_unhandled_url.
	 *
	 * It also creates a patch for PrettyURLs:
	 * page link doesn't work so good.
	 *
	 * @param string $url The unhandled URL
	 * @return string The cleaned URL
	 */
	function rewrite($url) {
		global $fp_params;

		// If it's PrettyURLs...
		if (class_exists('Plugin_PrettyURLs')) {
			// PU unset all params
			system_init_action_params();
			// Try to get URL from plugin
			global $plugin_prettyurls;
			if (is_callable(array($plugin_prettyurls, 'get_url'))) {
				$url = $plugin_prettyurls->get_url();
			} else {
				if (!empty($_SERVER ['PATH_INFO'])) {
					$url = $_SERVER ['PATH_INFO'];
				} elseif(!empty($_SERVER ['REQUEST_URI'])) {
					$url = $_SERVER ['REQUEST_URI'];
					$url = substr($url, strlen(BLOG_ROOT) -1);
					$qs = strpos($url, '?');
					$url = $qs === false ? $url : substr($url, 0, $qs);
					$an = strpos($url, '#');
					$url = $an === false ? $url : substr($url, 0, $an);
				} elseif (!empty($_GET ['u'])) {
					$url = $_GET ['u'];
				}
			}
		}

		$e = explode('/', $url);

		if (count($e) == 0) {
			// The work is finished here ;)
			return $url;
		}

		$e1 = array_shift($e);
		if ($e1 == '' && count($e) > 0) {
			$e1 = array_shift($e);
		}

		// If the first word isn't tag, it isn't a work of us.
		if ($e1 != 'tag') {
			return $url;
		}

		// Get the tag
		$tag = array_shift($e);
		// Get the sanitized tags (for urls...)
		$sanitized = $this->tag_db->rewriteCache();
		$tag = strtolower(rawurlencode($tag)); // Fix for arabian chars
		if (!isset($sanitized [$tag])) {
			return $url;
		}
		$fp_params ['tag'] =  $sanitized [$tag];

		if (isset($fp_params ['entry'])) {
			// The entry000000-000000
			unset($fp_params ['entry']);
		}

		if (count($e) == 0) {
			// Ok, no feed or page. Let's stop here ;)
			return '';
		}

		if (isset($e [0]) && $e [0] == 'feed') {
			array_shift($e);
			$fp_params ['feed'] = (!empty($e [0])) ? array_shift($e) : 'rss2';
		}

		if (isset($e [0], $e [1]) && $e [0] == 'page' && is_numeric($e [1])) {
			array_shift($e);
			$fp_params ['paged'] = array_shift($e);
		}

		// What remain of the url
		return implode('/', $e);
	}
}
?>
