<?php
/**
 * tag_prettyurls
 *
 * This class is a patch for PrettyURLs.
 * Infact PrettyURLs has problems with pages.
 *
 */
class tag_prettyurls {

	// Generic delegator to avoid hard failures if PrettyURLs adds new methods
	public function __call($name, $arguments) {
		if (isset($this->original) && is_object($this->original) && method_exists($this->original, $name)) {
			return call_user_func_array(array($this->original, $name), $arguments);
		}
		// Safe no-op fallback: return first arg if present
		return $arguments [0] ?? null;
	}

	// The "original" PrettyURLs
	var $original = null;

	// The baseurl
	var $baseurl = null;

	// The tag_link; set by the init_tag class.
	var $tag_link = '';

	// more params is declared here
	var $fp_params = null;

	/**
	 * tag_prettyurls
	 *
	 * The constructor.
	 * Saves the "original" PrettyURLs and the baseurl
	 *
	 * @param Plugin_PrettyURLs $original The original PrettyURLs instance
	 */
	function __construct(&$original) {
		$this->original = &$original;
		$this->baseurl = property_exists($original, 'baseurl') ? $original->baseurl : null;
	}

	// The functions called by the hooks.
	// They just call "original" functions
	function permalink($str, $id) {
		return $this->original->permalink($str, $id);
	}

	function commentlink($str, $id) {
		return $this->original->commentlink($str, $id);
	}

	function feedlink($str, $type) {
		return $this->original->feedlink($str, $type);
	}

	function commentsfeedlink($str, $type, $id) {
		return $this->original->commentsfeedlink($str, $type, $id);
	}

	function staticlink($str, $id) {
		return $this->original->staticlink($str, $id);
	}

	function categorylink($str, $catid) {
		return $this->original->categorylink($str, $catid);
	}

	function yearlink($str, $y) {
		return $this->original->yearlink($str, $y);
	}

	function monthlink($str, $y, $m) {
		return $this->original->monthlink($str, $y, $m);
	}

	function daylink($str, $y, $m, $d) {
		return $this->original->daylink($str, $y, $m, $d);
	}

	/**
	 * nextprevlink
	 *
	 * This function is the only function that is a bit changed.
	 *
	 * It just adds tag/$tagname/
	 *
	 * @param string $nextprev NextPage or PrevPage: it depends on the callback to call
	 * @param int $v +1 or -1: the number to sum to the current page
	 * @return array The array with the link and the text (Next/Prev Page)
	 */
	function nextprevlink($nextprev, $v) {
		global $fp_params, $fpdb;
		$this->fp_params = &$fp_params;
		if (!empty($fp_params ['tag'])) {
			if (empty($this->tag_link)) {
				return array();
			}
			$l = $this->tag_link;
			// Code by plugin.prettyurls.php, by NoWhereMan
				$q = &$fpdb->getQuery();
				$method = 'get' . $nextprev;
				if (!is_object($q) || !is_callable(array($q, $method))) {
					return array();
				}
				list($caption, $id) = call_user_func(array($q, $method));
				// No next/prev page => do not render empty links ("ghost buttons")
				if (!$id) {
					return array();
				}
				// For list views FPDB_Query returns the target page number as $id
				$page = (int) $id;
				if ($page > 0) {
					$l .= 'page/' . $page . '/';
				}
				return array($caption, $l);
			// End Code by PrettyURLs
		} else {
			// If it's not the tag, let's call the "original" function
			return $this->original->nextprevlink($nextprev, $v);
		}
	}
}
?>
