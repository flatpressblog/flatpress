<?php

class FPDB_QueryParams {

	var $id = null;

	var $d = null;

	var $m = null;

	var $y = null;

	var $start = 0;

	var $count = -1;

	var $random = 0;

	var $category = 0;

	var $exclude = null;

	var $page = 1;

	var $fullparse = false;

	var $comments = false;

	/**
	 * If true, only compute and expose comment count (no comment list parsing/iteration).
	 * Intended for entry streams where templates typically need only the count.
	 */
	var $commentcount = false;

	function __construct($params) {
		if (is_string($params)) {
			$this->parse_string($params);
		} elseif (is_array($params)) {
			$this->validate_array($params);
		} else {
			trigger_error("FPDB_QueryParams: parameters were not in a valid form", E_USER_ERROR);
		}
	}

	function pad_date($date) {
		if (is_numeric($date) && strlen($date) <= 2) {
			return str_pad($date, 2, '0', STR_PAD_LEFT);
		}

		return null;
	}

	function validate_array($params) {
		global $fp_config;

		if (isset($params ['id'])) {

			if (entry_exists($params ['id'])) {
				$this->id = $params ['id'];
			} else {
				// let it fail
				$this->count = 0;
				return;
			}
		}

		if (isset($params ['fullparse'])) {
			$this->fullparse = is_string($params ['fullparse']) ? ($params ['fullparse'] != 'false') : $params ['fullparse'];
		}

		if (isset($params ['y'])) {
			$this->y = $this->pad_date($params ['y']);

			if ($this->y && isset($params ['m'])) {
				$this->m = $this->pad_date($params ['m']);

				if ($this->m && isset($params ['d'])) {
					$this->d = $this->pad_date($params ['d']);
				}
			}
		}

		if (isset($params ['random']) && !$this->id) {
			$this->random = intval($params ['random']);
			$this->count = $this->random;
		}

		if (isset($params ['page'])) {
			$this->page = $params ['page'];
		} else {
			$this->page = 1;
		}

		if ($this->page < 1) {
			$this->page = 1;
		}

		if (isset($params ['count'])) {
			$this->count = intval($params ['count']);
		} else {
			$this->count = intval($fp_config ['general'] ['maxentries']);
		}

		$this->start = ($this->page - 1) * $this->count;

		if (isset($params ['start'])) {
			$this->start = intval($params ['start']);
		}

		if (isset($params ['category'])) {
			$this->category = intval($params ['category']);
		}

		if (isset($params ['comments'])) {
			$this->comments = true;
		}

		if (isset($params ['commentcount'])) {
			$this->commentcount = true;
		}

		/**
		 * Legacy default:
		 * Historically, fullparse implied that comments were loaded.
		 * Keep that behavior unless an explicit lightweight comment count
		 * was requested.
		 */
		if ($this->fullparse && !$this->comments && !$this->commentcount) {
			$this->comments = true;
		}

		if (isset($params ['exclude'])) {
			$this->exclude = intval($params ['exclude']);
		}
	}

	function parse_string($str) {
		$params = utils_kexplode(strtolower($str), ',:', false);
		$this->validate_array($params);
	}

}

class FPDB_Query {

	var $counter = -1;

	var $params = null;

	var $single = false;

	var $pointer = 0;

	/* pointer points always to NEXT element */
	var $processed = false;

	var $ID = 0;

	/* query id */
	var $lastentry = array(
		null,
		array()
	);

	var $localcache = array();

	var $nextid = '';

	var $previd = '';

	var $currentid = '';

	var $main_idx = null;

	var $secondary_idx = null;

	var $walker = null;

	var $prevkey = null;

	var $nextkey = null;

	var $comments = null;

	var $preventry = array();

	function __construct($params, $ID) {
		global $current_query;

		$this->params = new FPDB_QueryParams($params);
		$this->ID = $ID;

		if ($this->params->id || $this->params->random) {
			$this->single = true;
		}

		$GLOBALS ['current_query'] = &$this;
	}

	function prepare() {
		global $fpdb;

		$fpdb->init();

		$this->main_idx = &$fpdb->get_index($this->params->category);
		$entry_index = &$this->main_idx;

		$this->counter++;

		if (!$entry_index) {
			$this->params->start = 0;
			$this->params->count = 0;
			$this->pointer = 0;
			return;
		}

		if ($this->single || $this->params->random) {
			if ($this->params->random > 0) {
				$this->_get_random_id($entry_index);
			}
			$this->_prepare_single($entry_index);
		} else {
			$this->_prepare_list($entry_index);

			if ($this->params->exclude) {
				$o = &$fpdb->get_index($this->params->exclude);
				if ($o !== false) {
					$this->secondary_idx = &$o;
				}
			}
		}

		// force first entry to be loaded: updates count, pointer
		$this->peekEntry();
	}

	function _prepare_single(&$entry_index) {

		/*
		 * this should never happen
		 */
		if (!$this->params->id) {
			trigger_error("FPDB: no ID found for query {$this->ID}", E_USER_ERROR);
		}

		$qp = &$this->params;

		$time = entry_idtotime($qp->id);

		// let's get a preceding key in the order relation.
		// please notice this is hardcoded to $time+1, since
		// order of the indices is not configurable by the user
		$prevkey = entry_timetokey($time + 1);

		$key = entry_idtokey($qp->id);
		// print_r($key);

		// $key = entry_idtokey($qp->id);
		if (!($entry_index->has_key($key))) {
			// trigger_error("FPDB: no entry found for {$qp->id}", E_USER_WARNING);
			$qp->count = 0;
			return;
		}

		// if $includelower = 2 (second parameter) then assumes 'loose' inclusion
		// i.e. includes the first key $newkey such as $newkey <= $prevkey
		// also, if $prevkey != $newkey then $prevkey := $newkey

		$this->walker = &$entry_index->walker($prevkey, 2, null, null);

		// since we're searching for $prevkey, i.e. a key preceding the target $id
		// in the sequence, if $prevkey becomes equal to $key then it means
		// $key is the first post (the last in time)

		$qp->start = 0;
		$qp->count = 1;
		$this->pointer = 0;

		if ($prevkey == $key) {
			$this->prevkey = null;
			/** @phpstan-ignore-next-line */
			if ($this->walker->valid) {
				$this->walker->next();
				/** @phpstan-ignore-next-line */
				$this->nextkey = $this->walker->valid ? $this->walker->current_key() : null;
			}
		} else {
			$this->prevkey = $prevkey;
			if ($this->walker->valid) {
				$this->walker->next();
				/** @phpstan-ignore-next-line */
				if ($this->walker->valid) {
					$this->walker->next();
					/** @phpstan-ignore-next-line */
					$this->nextkey = $this->walker->valid ? $this->walker->current_key() : null;
				}
			}
		}
	}

	function _prepare_list(&$entry_index) {
		$qp = &$this->params;

		$entry_num = 0;

		if (!$qp->y) {
			// searches the whole index

			// $this->local_list = array_keys($entry_index);
			$firstid = null;

			$index_count = $entry_index->length();
			$this->walker = &$entry_index->walker($firstid);
		} else {
			// notice this won't work with cats (for now)

			$obj = new entry_archives($qp->y, $qp->m, $qp->d);

			$filteredkeys = $obj->getList();
			$index_count = $obj->getCount();

			if ($filteredkeys) {
				$error302var1 = entry_idtokey($filteredkeys [0]);

				$this->walker = $entry_index->walker($error302var1, true, entry_idtokey($filteredkeys [$index_count - 1]), true);
			}
		}

		if ($qp->count < 0) {

			// count<0 means 'all'
			$qp->count = $index_count;
		} elseif (($qp->start + $qp->count) > $index_count) {

			if ($index_count >= $qp->start) {
				$qp->count = $index_count - $qp->start;
			} else {
				$index_count = $qp->start = $qp->count = 0;
			}
		}
	}

	function _get_random_id(&$entry_index) {
		$qp = &$this->params;
		$now = time();

		$first = '999999999999';
		$last = '000000000000';
		$entry_index->getitem($first, true);
		$entry_index->getitem($last, true);

		$t1 = entry_keytotime($first);
		$t2 = entry_keytotime($last);

		// mktime() with 2-digit years, so ensure min<=max
		$min = min($t1, $t2);
		$max = max($t1, $t2);

		// If index is empty or bounds are equal, bail out safely
		if ($min === false || $max === false) {
			return false;
		}
		$t = ($min === $max) ? $min : mt_rand($min, $max);

		$random_key = entry_timetokey($t);
		$entry_index->getitem($random_key, true);

		$qp->id = entry_keytoid($random_key);
	}

	/**
	 * Checks whether further entries in the current window are readable
	 * without moving the pointer or parsing.
	 *
	 * Logic:
	 * - If pointer >= start+count ⇒ false.
	 * - If $this->params->id is set, only the window is checked;
	 *   existence/parsing is handled by peekEntry().
	 * - Otherwise: a walker must exist and be valid.
	 *
	 * Runtime: O(1).
	 * @return bool
	 */
	function hasMore() {
		$GLOBALS ['current_query'] = &$this;

		// If system init has not been done (filling pointer variable etc.)
		if ($this->counter < 0) {
			$this->prepare();
		}

		// true if still within range and source has data
		if ($this->pointer >= $this->params->start + $this->params->count) {
			return false;
		}
		// id-queries don't rely on walker validity
		if ($this->params->id) {
			return true;
		}
		return ($this->walker && $this->walker->valid);
	}

	/**
	 * @return array{0:string|false,1:array|false} Reference.
	 */
	function &peekEntry() {
		global $post;

		$qp = &$this->params;
		static $RET_EMPTY = array(
			false,
			false
		);
		$return = $RET_EMPTY;

		if ($this->counter < 0) {
			$this->prepare();
		}

		if ($this->pointer == $this->params->start + $this->params->count) {
			return $RET_EMPTY;
		}

		if ($qp->id) {
			$idx = $this->main_idx;
			$key = entry_idtokey($qp->id);

			$v = $idx->getitem($key);
			if ($qp->fullparse) {

				$entry = null;
				if (isset($this->localcache [$qp->id])) {
					$entry = $this->localcache [$qp->id];
				} else {
					$entry = entry_parse($qp->id);
					if ($entry) {
						$this->localcache [$qp->id] = $entry;
					}
				}

				if ($entry && $qp->comments) {
					$this->comments = new FPDB_CommentList($qp->id, comment_getlist($qp->id));
					$entry ['comments'] = $this->comments->getCount();
				} elseif ($entry && $qp->commentcount) {
					$entry ['comments'] = comment_getcount($qp->id);
				}

				$post = $entry;

				if (!is_array($entry)) {
					return $RET_EMPTY;
				}
			} else {
				$entry = array(
					'subject' => $v
				);
				$post = $entry;
			}

			$return = array(
				$qp->id,
				$entry
			);
			return $return;
		}

		if (!$this->walker) {
			$false = array(
				false,
				false
			);
			return $false;
		}

		// search first eligible post
		while ($this->walker->valid && $this->pointer < $qp->start) {

			$this->previd = $this->currentid;
			$key = $this->walker->current_key();
			$id = $this->currentid = entry_keytoid($key);

			if ($this->single) {
				$this->preventry = array(
					'subject' => $this->walker->current_value()
				);
			}

			$this->walker->next();
			$this->pointer++;
		}

		// if there is a secondary (not) idx
		if ($this->secondary_idx) {

			// skips posts until we find one which is not in the secondary idx
			while ($this->walker->valid && ($key = $this->walker->current_key()) && $this->secondary_idx->has_key($key)) {
				$this->walker->next();
				// $qp->count--;
			}
		}

		if (!$this->walker->valid) {
			return $RET_EMPTY;
		}

		// pointer == start

		$prevcurr = $this->currentid;
		$id = $this->currentid = entry_keytoid($this->walker->current_key());
		if ($id != $prevcurr) {
			$this->previd = $prevcurr;
		}

		if ($qp->fullparse && $this->counter <= 0) {

			// full parse: reads the whole array from file
			$cont = null;
			if (isset($this->localcache [$id])) {
				$cont = $this->localcache [$id];
			} else {
				$cont = entry_parse($id);
				if (is_array($cont)) {
					$this->localcache [$id] = $cont;
				}
			}
		} else {

			// only title
			$cont = array(
				'subject' => $this->walker->current_value(),
				'date' => entry_idtotime($id)
			);
		}

		if (!is_array($cont)) {
			return $RET_EMPTY;
		}

		if ($qp->comments) {
			$this->comments = new FPDB_CommentList($id, comment_getlist($id));
			$cont ['comments'] = $this->comments->getCount();
		} elseif ($qp->commentcount) {
			$cont ['comments'] = comment_getcount($id);
		}

		$post = $cont;
		$post ['id'] = $id;

		$var = array(
			&$id,
			&$cont
		);
		return $var;
	}

	function &getEntry() {
		if (!$this->hasMore()) {
			return false;
		}

		$var = &$this->peekEntry();
		$this->lastentry = $var;

		$this->walker->next();
		$this->pointer++;

		return $var;
	}

	function getLastEntry() {
		return $this->lastentry;
	}

	function hasComments() {
		return $this->comments->getCount();
	}

	function _getOffsetId($offset, $assume_pointer = null) {
		if (is_int($assume_pointer)) {
			$i = $assume_pointer + $offset;
		} else {
			$i = $this->pointer + $offset;
		}
		return isset($this->local_list [$i]) ? $this->local_list [$i] : false;
	}

	function _fillCurrentId() {
		return $this->currentid = $this->_getOffsetId(0);
	}

	function _fillNextId() {
		return $this->nextid = $this->_getOffsetId(1);
	}

	function _fillPrevId() {
		return $this->previd = $this->_getOffsetId(-1);
	}

	function getCurrentId() {
		return $this->currentid;
	}

	function getNextId() {
		return $this->nextid;
	}

	function getPrevId() {
		return $this->previd;
	}

	function getNextPage() {
		if ($this->single) {
			$key = $this->nextkey;
			if (!$key) {
				return array(
					null,
					null
				);
			} else {
				$val = $this->main_idx->getitem($key);
				return array(
					$val,
					entry_keytoid($key)
				);
			}
		}

		if ($this->params->page) {
			// if ($this->_getOffsetId(0, ($this->params->start + $this->params->count)))
			if ($this->walker && $this->walker->valid) {
				return array(
					$GLOBALS ['lang'] ['main'] ['nextpage'],
					$this->params->page + 1
				);
			}
		}
	}

	function getPrevPage() {
		if ($this->single) {
			$key = $this->prevkey;
			if (!$key) {
				return array(
					null,
					null
				);
			} else {
				$val = $this->main_idx->getitem($key);
				return array(
					$val,
					entry_keytoid($key)
				);
			}
		}

		if ($this->params->page > 1) {
			return array(
				$GLOBALS ['lang'] ['main'] ['prevpage'],
				$this->params->page - 1
			);
		}
	}

}

class FPDB_CommentList {

	var $count = 0;

	var $list = array();

	var $current = '';

	var $entryid = '';

	function __construct($ID, $array) {
		if (is_array($array)) {
			$this->list = $array;
			$this->count = count($array);
			$this->entryid = $ID;
		}
	}

	function getCount() {
		return $this->count;
	}

	function hasMore() {
		if ($this->count) {
			return current($this->list) !== false;
		}

		return false;
	}

	function &getComment() {
		if (!$this->hasMore()) {
			return false;
		}

		$id = array_shift($this->list);

		$comment = comment_parse($this->entryid, $id);
		$couplet = array(
			&$id,
			&$comment
		);
		return $couplet;
	}

}

class FPDB {

	var $_indexer = array();

	var $_categories = array();

	var $queries = array();

	var $_query = null;

	var $entry_index = array();

	function __construct() {
		// constructor
	}

	function init() {
		// if (!$this->_indexer) {
		// $this->_indexer = new entry_indexer();
		$this->_categories = entry_categories_get();
		// $obj =& $this->_indexer;
		// $this->entry_index = $obj->getList();

		// }
	}

	function &get_index($cat_id = 0) {
		if (!isset($this->_indexer [$cat_id])) {
			$this->_indexer [$cat_id] = &entry_cached_index($cat_id);
		}
		return $this->_indexer [$cat_id];
	}

	function reset($queryId = null) {
		switch ($queryId) {
			case null:
				$this->_query = array();
				break;
			default:
				unset($this->_query [$queryId]);
		}
	}

	/**
	 * Runs a query on the database.
	 *
	 * @param mixed $params The parameters of the query. May be an associative array or a query string:
	 *                      'key:val,key:val,key:val'
	 *                      Example: $params = 'start:0,count:5';
	 *                      Equivalent to: array('start' => 0, 'count' => 5)
	 *                      Valid parameters:
	 *                      - start (int): First entry to show (from 0)
	 *                      - count (int): Number of entries to fetch
	 *                      - page (int): Page number (overrides start and count)
	 *                      - id (string): Entry or static page ID
	 *                      - get (string): 'entry' or 'static' (default 'entry')
	 *                      - y (string): Year (two digits)
	 *                      - m (string): Month (two digits), requires 'y'
	 *                      - d (string): Day (two digits), requires 'y' and 'm'
	 *                      - exclude (int): Exclude category ID (experimental)
	 *                      - fullparse (bool): Whether to fully parse entries (default false)
	 * @return int Query ID result of the query.
	 */
	 function query($params = array()) {
		static $queryId = -1;
		$queryId++;

		$this->queries [$queryId] = new FPDB_Query($params, $queryId);

		$this->init();

		return $queryId;
	}

	function doquery($queryId = 0) {
		if (isset($this->queries [$queryId])) {
			$q = &$this->queries [$queryId];
		} else {
			return false;
			// trigger_error("FPDB: no such query ID (" . $queryId . ")", E_USER_WARNING);
		}

		if (!$q) {
			return false;
		}

		// $this->init();

		/**
		 *
		 * @todo return true/false
		 */
		return $q->prepare($this->entry_index);
	}

	// "get" functions. todo: move out?
	function &getQuery($queryId = 0) {
		$o = null;
		if (isset($this->queries [$queryId])) {
			$o = &$this->queries [$queryId];
		}
		return $o;
	}

}

class FPDB_transaction {

	var $_index = null;

	var $_offset = 0;

	var $_nodesize = 30;

	var $_keysize = 12;

	var $_cachefile = '';

	var $_chunksize = 0;

	var $_tree = array();

	function __construct($id_cat = 0) {
		$this->_index = INDEX_DIR . 'index-' . $id_cat;

		$this->_tree = caching_SBPT(fopen($this->_cachefile . '.dat', 'r'), fopen($this->_cachefile . '.strings.dat', 'r'), $this->_offset, $this->_chunksize, $this->_keysize);
	}

}

// SMARTY FUNCTIONS ----------------------------------------------------
function smarty_block_entries($params, $content, &$smarty, &$repeat) {
	global $fpdb;

	return $content;
}

function smarty_block_entry($params, $content, &$smarty, &$repeat) {
	global $fpdb;

	// clean old variables

	$smarty->assign(array(
		'subject' => '',
		'content' => '',
		'categories' => array(),
		'filed_under' => '',
		'date' => '',
		'author' => '',
		'version' => '',
		'ip-address' => ''
	));

	if (isset($params ['content']) && is_array($params ['content']) && $params ['content']) {
		// foreach ($params['entry'] as $k => $val)
		$smarty->assign($params ['content']);
		return $content;
	}

	if (isset($params ['alwaysshow']) && $params ['alwaysshow']) {
		return $content;
	}

	$q = &$fpdb->getQuery();

	if ($repeat = $q->hasMore()) {

		$couplet = &$q->getEntry();

		$id = &$couplet [0];
		$entry = &$couplet [1];

		if (THEME_LEGACY_MODE) {
			$entry = theme_entry_filters($entry, $id);
		}

		foreach ($entry as $k => $v) {
			$smarty->assign($k, $entry [$k]);
		}

		$smarty->assign('id', $id);

		if (array_key_exists('categories', $entry)) {
			$smarty->assign('entry_commslock', in_array('commslock', $entry ['categories']));
		}

		do_action('entry_block', $id);
	}

	return $content;
}

function smarty_block_comment($params, $content, &$smarty, &$repeat) {
	global $fpdb;

	// clean old variables

	$smarty->assign(array(
		'subject' => '',
		'content' => '',
		'date' => '',
		'name' => '',
		'url' => '',
		'email' => '',
		'version' => '',
		'ip-address' => '',
		'loggedin' => ''
	));

	$q = &$fpdb->getQuery();
	if (!isset($q->comments) || !is_object($q->comments)) {
		$repeat = false;
		return $content;
	}

	if ($repeat = $q->comments->hasMore()) {

		$couplet = &$q->comments->getComment();

		$id = &$couplet [0];
		$comment = &$couplet [1];

		foreach ($comment as $k => $v) {
			$kk = str_replace('-', '_', $k);
			$smarty->assign($kk, $comment [$k]);
		}

		if (THEME_LEGACY_MODE) {
			$comment = theme_comments_filters($comment, $id);
		}

		$smarty->assign('id', $id);
	}

	return $content;
}

function smarty_block_comments($params, $content, &$smarty, &$repeat) {
	global $fpdb;

	$q = &$fpdb->getQuery();

	if (!isset($q->comments) || !is_object($q->comments)) {
		$repeat = false;
		return $content;
	}
	$show = $q->comments->getCount();
	$smarty->assign('entryid', $q->comments->entryid);

	if ($show) {

		return $content;
	} else {

		$repeat = false;
	}
}

function smarty_function_nextpage($params) {
	$nextPageLink = get_nextpage_link();
	if (empty($nextPageLink)) {
		return;
	}
	list ($caption, $link) = $nextPageLink;

	if (!$link) {
		return;
	}

	if (isset($params ['admin'])) {
		$qstr = strstr($link, '?');
		$link = BLOG_BASEURL . 'admin.php' . $qstr;
	}

	return "<div class=\"alignright\"><a href=\"" . $link . "\">" . $caption . "</a></div>";
}

function smarty_function_prevpage($params) {
	$prevPageLink = get_prevpage_link();
	if (empty($prevPageLink)) {
		return;
	}
	list ($caption, $link) = $prevPageLink;

	if (!$link) {
		return;
	}

	if (isset($params ['admin'])) {
		$qstr = strstr($link, '?');
		$link = BLOG_BASEURL . 'admin.php' . $qstr;
	}

	return "<div class=\"alignleft\"><a href=\"" . $link . "\">" . $caption . "</a></div>";
}

$smarty->registerPlugin('block', 'comment', 'smarty_block_comment');
$smarty->registerPlugin('block', 'comments', 'smarty_block_comments');
$smarty->registerPlugin('block', 'comment_block', 'smarty_block_comments');

$smarty->registerPlugin('block', 'entries', 'smarty_block_entries');
$smarty->registerPlugin('block', 'entry_block', 'smarty_block_entries');

$smarty->registerPlugin('block', 'entry', 'smarty_block_entry');

$smarty->registerPlugin('function', 'nextpage', 'smarty_function_nextpage');
$smarty->registerPlugin('function', 'prevpage', 'smarty_function_prevpage');

?>
