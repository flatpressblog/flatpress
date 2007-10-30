<?php

	class FPDB_QueryParams {
		
		var $id	= null;
		var $d		= null;
		var $m		= null;
		var $y		= null;
		var $start	= 0;
		var $count	= -1;
		var $random = 0;
		var $category = 0;
		var $page	= 1;
		var $fullparse = false;
		
		function FPDB_QueryParams($params) {
		
			if (is_string($params)) {
				$this->parse_string($params);
			} elseif (is_array($params)) {
				$this->validate_array($params);
			} else { 
				trigger_error("FPDB_QueryParams: parameters were not in a valid form", 
								E_USER_ERROR);
			}
			
		}
		
		function pad_date($date) {
				
			if (is_numeric($date) && strlen($date)<=2){
				return str_pad($date,2,'0', STR_PAD_LEFT);
			}
			
			return null;
		
		}
		
		function validate_array($params) {
		
			global $fp_config;
			
			if (isset($params['id'])) {
			
				if (entry_exists($params['id'])) {
					$this->id = $params['id'];
					
				}
				
			}
			
			if (isset($params['fullparse'])) {
				$this->fullparse = 
						is_string($params['fullparse'])?
						($params['fullparse'] != 'false')
						:
						$params['fullparse'];
			}
			
		
			if (isset($params['y'])) {
				$this->y = $this->pad_date($params['y']);
			
				if ($this->y && isset($params['m'])) {
					$this->m = $this->pad_date($params['m']);
			
					if ($this->m && isset($params['d'])) {
						$this->d = $this->pad_date($params['d']);
					}
					
				}
				
			}
			
			if (isset($params['random'])) {
				$this->random = intval($params['random']);
				$this->count = $this->random;
			}

			if (isset($params['page'])) {
				$this->page = $params['page'];
			} else {
				$this->page = 1;
			}
				
			if ($this->page<1) {
				$this->page=1;
			}
			
			if (isset($params['count'])) {
				$this->count = intval($params['count']);
			} else {
				$this->count = intval($fp_config['general']['maxentries']);
			}
			
			
			$this->start = ($this->page - 1) * $this->count;
			
			
			if (isset($params['start'])) {
				$this->start = intval($params['start']);
			}
			
			
			if (isset($params['category'])) {
				$this->category = intval($params['category']);
			}			
			
		}
		
		function parse_string($str) {
			$params = utils_kexplode(strtolower($str), ',:', false);
			$this->validate_array($params);
		}
		
	}

	class FPDB_Query {
	
		var $counter 	= -1;
		var $params 	= null;
		var $single 	= false;
		var $pointer 	= 1;		/* pointer points always to NEXT element */
		var $processed 	= false;
		var $ID 		= 0;		/* query id */
		
		
		var $lastentry  = array(null, array()); 
		var $nextid		= '';
		var $previd		= '';
		var $currentid	= '';		
	
		function FPDB_Query($params, $ID) {
			
			global $current_query;
			
			
			$this->params =& new FPDB_QueryParams($params);
			$this->ID = $ID;
			
			if ($this->params->id) {
				$this->single = true;
			}
			
			$GLOBALS['current_query'] =& $this;
			
		}
		
		function prepare() {
		
			global $fpdb;
			
			
			$fpdb->init();
			
			$entry_index = $fpdb->entry_index;
			
			if ($this->single) {
				$this->_prepare_single($entry_index);
			} else {
				$this->_prepare_list($entry_index);
				if ($this->params->random>0) {
					$this->_randomize_list();
				}
			}
			
			$this->counter++;
			
		
		}
		
		function _prepare_single(&$entry_index) {
		
			/*
			 * this should never happen
			 */
			if (!$this->params->id) 
				trigger_error("FPDB: no ID found for query {$this->ID}", E_USER_ERROR);
						
			$qp =& $this->params;
							
			if (!isset($entry_index[$qp->id])){
				trigger_error("FPDB: no entry found for {$qp->id}", E_USER_WARNING);
				return;
			}
			if ($this->counter < 0) { 
			
				$idlist = array_keys($entry_index);
				$fliplist = array_flip($idlist);
				
				$this->local_index =& $entry_index;
				$this->local_list =& $idlist;
				
				$qp->start = $fliplist[$qp->id];
				$qp->count = 1;
		
			
			}
			
			$this->pointer = $qp->start;
			
		}
		
		function _prepare_list(&$entry_index) {
			//global $blog_config;
			$qp =& $this->params;
			
			$entry_num = 0;
			
			if (!$qp->y){
			
				$this->local_list = array_keys($entry_index);
				$this->local_index =& $entry_index;
				
				/* @todo MUST CACHE THIS COUNT! (MUST STRUCT CACHE)*/
				$index_count = count($entry_index);
				
			} else {
						
				$obj =& new entry_archives($qp->y, $qp->m, $qp->d); 
				$filteredkeys = $obj->getList();
				
				$index_count = $obj->getCount();
			
				$this->local_list =& $filteredkeys;
				
			}
			
			if ($qp->count < 0) {
				$qp->count = $index_count;
			} elseif (($qp->start + $qp->count) > $index_count) {
				if ($index_count > 0)
					$qp->count = $index_count - $qp->start;
				else
					$index_count = $qp->start = $qp->count = 0;
			}
		
			$this->pointer = $qp->start;
			
			
			if ($qp->category==0)
				return;
				
			
			/* category */
			/* this just SUCKS. need a separate cache... */
			
			$relations = entry_categories_get('rels');
			
			if (!isset($relations[$qp->category]))
				return;
			
			$catrel = $relations[$qp->category];
			
			/* need to search for one more to know if we need a nextpage link */
			$fill = $qp->start + $qp->count + 1; 
			$i = 0;
			$tmp = array();
			
			while ($i <= $fill && (list($K, $V) = each($this->local_list))) {
				
				if (array_intersect($catrel, $this->local_index[$V]['categories'])) {
					// in_array($qp->category, $this->local_index[$V]['categories']))
					$tmp[] =& $this->local_list[$K];
					
					$i++;
					
				}
				
			}
			
			$this->local_list =& $tmp;
			
			if ($qp->start + $qp->count > $i) {
				$qp->count = $i - $qp->start;
			}
			
		}
		
		function _randomize_list() {
			$qp =& $this->params;
			
			$i = $qp->random - 1;
			$nums = array_keys($this->local_list);
			
			
			if ($qp->random == 1) {
				$i = mt_rand(0, end($nums));
				$this->single = true;
				$qp->id = $this->local_list[ $i ];
				$this->_prepare_single($this->local_index);
				return;
			}
			
			shuffle($nums);
			
			$newlocal = array();
			do {
				$newlocal[ $i ] = $this->local_list[ $nums[$i] ]; 
			} while($i--);
			
			$this->local_list = $newlocal;
			
			if ($qp->count > $qp->random) {
				$qp->count = $qp->random;
			}
		}
		
		/* reading functions */
		
		function hasMore() {
		
			
			$GLOBALS['current_query'] =& $this;
			
			
			if ($this->counter < 0)
				$this->prepare();
			
			return $this->pointer < $this->params->start + $this->params->count;
		}
		
		function &peekEntry() {
		
			global $post;
		
			$qp =& $this->params;
			
			
			if ($this->counter < 0)
				$this->prepare();

			
			$this->_fillPrevId();
			$this->_fillNextId();
						
			$id = $this->_fillCurrentId();
	
			
			if ($qp->fullparse && $this->counter <= 0) {
			
				$cont = array();
				
				$cont = entry_parse($id);
				if ($cont) {
					$this->comments =& new FPDB_CommentList($id, comment_getlist($id));
				
					$cont['comments'] = $this->comments->getCount();
			
					/* index is updated with full-parsed entry */				
					$this->local_index[$id] = $cont;
				}
				
			} else {
				
				$cont = $this->local_index[$id];
				
			}
			
			
			
			$post = $cont;
			$post['id'] = $id;
			
			$var = array(&$id, &$cont);
			return $var;
		
		}
		
		function &getEntry() {
			
			if (!$this->hasMore())
				return false;
			
			$var =& $this->peekEntry();
			
			$this->lastentry = $var;
			
			$this->pointer++;
			
			return $var;		
		}
		
		function getLastEntry() {
			return $this->lastentry;
		}
		
		function hasComments() {
			return $this->comments->getCount();
		}
		

		
		function _getOffsetId($offset, $assume_pointer=null) {
			if (is_int($assume_pointer))
				$i = $assume_pointer + $offset;
			else
				$i = $this->pointer + $offset;						
			return isset($this->local_list [ $i ])? $this->local_list [ $i ] : false;
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
			
			if ($this->single){
				$id = $this->_getOffsetId(-1, $this->params->start);
				
				if ($id)
					$label = $this->local_index[$id]['subject'];
				else
					return false;
					
				return array($label, $id);
				 
			}
			
			if ($this->params->page) {
				if ($this->_getOffsetId(0, ($this->params->start + $this->params->count)))
					return array($GLOBALS['lang']['main']['nextpage'], $this->params->page + 1);
				
			}
			
			
		}
		
		function getPrevPage() {
		
			if ($this->single) {
				$id = $this->_getOffsetId(1, $this->params->start);
				
				if ($id)
					$label = $this->local_index[$id]['subject'];
				else
					return false;
				
				return array($label, $id);
				
			}
			
			if ($this->params->page > 1) {
				return array($GLOBALS['lang']['main']['prevpage'], $this->params->page - 1);
			}
			
		}
		
		
	}
	
	
	
	class FPDB_CommentList {
	
		var $count = 0;
		var $list = array();
		var $current = '';
		var $entryid = '';
		
		function FPDB_CommentList($ID, $array) {
			
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
			
			if (!$this->hasMore())
				return false;
		
			list($k,$id) = each($this->list);
			
			$comment = comment_parse($this->entryid, $id);
			$couplet = array(&$id, &$comment);
			return $couplet;
			
		}
		
	}

	class FPDB {
		
		var $_indexer = null;
		var $queries = array();
		
	
		function FPDB() {
			// constructor
		}
		
		function init() {
			if (!$this->_indexer) {
				$this->_indexer =& new entry_indexer();
				$this->_categories = entry_categories_get();
				$obj =& $this->_indexer;
				$this->entry_index =& $obj->getList();
		
			}
		}
		
		function reset($queryId=null) {
			
			switch ($queryId) {
				case null: 	$this->_query = array(); break;
				default:	unset($this->_query[$queryId]);
			}
			
		
		}
		
		/**
		 * function query
		 * @param mixed params
		 * $params may be an associative array or a query string with the following syntax:
		 * 'key:val,key:val,key:val';
		 * example: <code>$params = 'start:0,count:5';<br />
		 *			is a convenient way to express 
		 *			$params = array('start'=>0,'count'=>5);</code>
		 *
		 * Valid parameters:
		 * 
		 * start	(int) first entry to show (counting from 0 
		 *			to the total number of entries).
		 *			Defaults to 0.
		 *
		 * count	(int) offset from start (e.g. for the first 5 entries, 
		 *			you'll have start=0 and count=5).
		 *			Defaults to $blog_config['MAXENTRIES'] 
		 *
		 * page		(int) page number (counting from 1 to 
		 *			n=ceil(num_entries/maxentries_setting))
		 *			This is a shortcut for setting both start and count 
		 *			and overrides them, if they're set too
		 * 
		 * id		(string) entry or static page id
		 *
		 * get		(string) 'entry' or 'static' defaults to 'entry'. <-- not anymore
		 *
		 * y		(string) two digit year (06 means 2006)
		 * m		(string) two digit month (06 means June); 'y' must be set
		 * d		(string) two digit for day; 'y' and 'm' must be set
		 *
		 *
		 * fullparse	(bool) non-full-parsed entries get their values 
		 *				right from the indexed list (or <em>cache</em>).
		 *				These values are 'subject', 'content' and 'categories'. 
		 *				If you need any of the other values, you'll need to
		 *				set fullparse=true; defaults to false.
		 *
		 */			 
		
		function query($params=array()) {
			
			static $queryId=-1;
			$queryId++;
			 
			$this->queries[$queryId] =& new FPDB_Query($params, $queryId);
				
			
			$this->init();	
						
			return $queryId;
			
		
		}
		
		function doquery($queryId=0) {
		
			if (isset($this->queries[$queryId])) {
				$q =& $this->queries[$queryId];
			} else {
				return false;
				trigger_error("FPDB: no such query ID ($queryId)", E_USER_WARNING);
			}
			
			if (!$q) 
				return false;
				
			//$this->init();
			
			/**
				 @todo return true/false
			 */
			return $q->prepare($this->entry_index);
		}
		
		// "get" functions. todo: move out?
		
		function &getQuery($queryId=0) {
			$o = null;
			if (isset($this->queries[$queryId]))
				$o =& $this->queries[$queryId];
			return $o;
		}
	}

	
	// SMARTY FUNCTIONS ----------------------------------------------------
	
	
	function smarty_block_entries($params, $content, &$smarty, &$repeat) {
		global $fpdb;
		
		return $content;
		
		$show = false;
		
		$smarty->assign('prev_entry_day', '');
		
		if ($repeat) {
		
			if (isset($params['alwaysshow']) && $params['alwaysshow']) {
				//$fpdb->doquery();
				$repeat = false;
				return $content;
			}
			
			//$show = @$fpdb->doquery();
		
		} else {
			
			if (!isset($fpdb->queries[0]->comments) || !$fpdb->queries[0]->comments) 
				$fpdb->reset(0);
				$show = true;
			
		}
		
		$show = true;
		
		
		
		
		if ($show)
			return $content;
		
	}
			
	
	function smarty_block_entry($params, $content, &$smarty, &$repeat) {
		global $fpdb;
		
		// clean old variables
		
		$smarty->assign(array(	'subject'=>'',
					'content'=>'',
					'categories' =>array(),
					'filed_under'=>'',
					'date'=>'',
					'author'=>'',
					'version'=>'',
					'ip-address'=>''
					)
				);
		
		
		if (isset($params['content']) && is_array($params['content']) && $params['content']) {
			//foreach ($params['entry'] as $k => $val)
			$smarty->assign(array_change_key_case($params['content'], CASE_LOWER));
			return $content;
		}
		
		if (isset($params['alwaysshow']) && $params['alwaysshow']) {
			return $content;
		}
		
		$q =& $fpdb->getQuery();
		
		if($repeat=$q->hasMore()) {
			
			
			$couplet =& $q->getEntry() ;
			
			$id =& $couplet[0];
			$entry =& $couplet[1];
			
			if (THEME_LEGACY_MODE) {
				$entry = theme_entry_filters($entry, $id);
			}
			
			
			foreach($entry as $k=>$v) 
				$smarty->assign_by_ref($k, $entry[$k]);
			
			$smarty->assign_by_ref('id', $id);
			
			
			do_action('entry_block');
			
		}
		

		return $content;
		
	}
	
	
	function smarty_block_comment($params, $content, &$smarty, &$repeat) {
		global $fpdb;
		
		// clean old variables
		
		$smarty->assign(array(
					'subject'=>'',
					'content'=>'',
					'date'=>'',
					'name'=>'',
					'url'=>'',
					'email'=>'',
					'version'=>'',
					'ip-address'=>'',
					'loggedin'=>'',
					)
				);
		
		$q =& $fpdb->getQuery();
		
		if($repeat=$q->comments->hasMore()) {
			
			$couplet =& $q->comments->getComment();
			
			$id =& $couplet[0];
			$comment =& $couplet[1];

			
			foreach($comment as $k=>$v) {
				$kk = str_replace('-', '_', $k);
				$smarty->assign_by_ref($kk, $comment[$k]);
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
		
			$q =& $fpdb->getQuery();
			$show = $q->comments->getCount();
			$smarty->assign('entryid', $q->comments->entryid);
			
					
			if ($show) {
				
				return $content;
			} else {
			
				$repeat = false;
			}

	}
	
	
	function smarty_function_nextpage($params) {
		
		list ($caption, $link) = get_nextpage_link();
		
		if (!$link)
			return;
		
		if (isset($params['admin'])) {
			$qstr=strstr($link, '?');
			$link = BLOG_BASEURL . 'admin.php' . $qstr;
		}
		
		
		return "<div class=\"alignright\"><a href=\"$link\">$caption</a></div>";
		
	}

	function smarty_function_prevpage($params) {

		list($caption, $link) = get_prevpage_link();
		
		if (!$link)
			return;
		
		if (isset($params['admin'])) {
			$qstr=strstr($link, '?');
			$link = BLOG_BASEURL .'admin.php' . $qstr;
		}
		
		
		return "<div class=\"alignleft\"><a href=\"$link\">$caption</a></div>";


	}

	$_FP_SMARTY->register_block('comment','smarty_block_comment');
	$_FP_SMARTY->register_block('comments','smarty_block_comments');
	$_FP_SMARTY->register_block('comment_block','smarty_block_comments');
	
	$_FP_SMARTY->register_block('entries','smarty_block_entries');
	$_FP_SMARTY->register_block('entry_block','smarty_block_entries');
	
	$_FP_SMARTY->register_block('entry','smarty_block_entry');
	
	$_FP_SMARTY->register_function('nextpage','smarty_function_nextpage');
	$_FP_SMARTY->register_function('prevpage','smarty_function_prevpage');

?>
