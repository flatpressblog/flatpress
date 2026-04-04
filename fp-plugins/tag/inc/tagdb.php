<?php
/**
 * tag_lister class
 *
 * This class is used to make the list
 * of the files in the PLUGIN_TAG_DIR
 * without the extension (.txt)
 *
 * See core.filesystem.php
 */
class tag_lister extends fs_filelister {

	/**
	 * The tag list.
	 *
	 * @var array
	 */
	var $taglist = array();

	/**
	 * Constructor: it calls the constructor of parent class
	 * and it sets the directory to list.
	 */
	function __construct() {
		parent::__construct(PLUGIN_TAG_DIR);
	}

	/**
	 * Filter files (accept only *.txt files)
	 *
	 * @param string $directory: The directory of the file to check
	 * @param string $file: The file name
	 * @return integer: See fs_filelister class
	 */
	function _checkFile($directory, $file) {
		$f = $directory . "/" . $file;
		if (fnmatch('*.txt', $f)) {
			array_push($this->_list, basename($f, '.txt'));
		}
		return 0;
	}

	/**
	 * Make the list of tags.
	 *
	 * @param boolean $force: I must list or I can use cache?
	 */
	function makeTagList($force = false) {
		// Already done? Return old list!
		if (!$force && !empty($this->taglist)) {
			return $this->taglist;
		}

		// Empty? Make (again) the list
		if (empty($this->_list)) {
			$lister = new tag_lister();
			$this->_list = $lister->_list;
		}

		// Still empty? Return void array
		if (empty($this->_list)) {
			return array();
		}

		$tags = array();
		foreach ($this->_list as $file) {
			$tag = array();
			include PLUGIN_TAG_DIR . $file . '.txt';

			// Don't use array_merge because it resets the keys!
			foreach ($tag as $key => $value) {
				$tags [$key] = $value;
			}
		}

		$this->taglist = $tags;
		return $tags;
	}
}

/**
 * plugin_tag_db
 *
 * This is the database of the tags.
 */
class plugin_tag_db {
	/**
	 * This is the file cache.
	 *
	 * @var array
	 */
	var $files = array();

	/**
	 * This is the cache of sanitized tags for URL rewriting
	 *
	 * @var array
	 */
	var $rewriteCachesan = array();

	# File IO
	/**
	 * open_file
	 *
	 * This function open a file of the tag database.
	 *
	 * @param string $file: the file, without .txt
	 * @param boolean $force: force to re-open? [false]
	 * returns array: The file
	 */
	function open_file($file, $force = false) {
		// Do the double check with the name
		$file = $this->tagfile($file);

		// Already opened? Return it!
		if (isset($this->files [$file]) && !$force) {
			return $this->files [$file];
		}

		$realfile = PLUGIN_TAG_DIR . $file . '.txt';

		$tag = array();
		if (file_exists($realfile)) {
			include $realfile;
		}

		$this->files [$file] = is_array($tag) ? $tag : array();

		return $this->files [$file];
	}

	/**
	 * save_file
	 *
	 * Save an opened file.
	 *
	 * @param string $file: The file without .txt
	 * @returns boolean: Does it succeed in saving the file?
	 */
	function save_file($file) {
		// Double check the file name
		$file = $this->tagfile($file);

		// Not exists? Can't save!
		if (!isset($this->files [$file])) {
			return false;
		}

		$realfile = PLUGIN_TAG_DIR . $file . '.txt';
		$tag = $this->files [$file];

		return system_save($realfile, compact('tag'));
	}

	/**
	 * save_all
	 *
	 * Save ALL opened files.
	 *
	 * @returns boolean: does it succeed in all saves?
	 */
	function save_all() {
		if (!count($this->files)) {
			return true;
		}

		$r = true;

		foreach ($this->files as $k => $v) {
			$single = $this->save_file($k);
			$r = $r && $single;
		}

		return $r;
	}

	/**
	 * makeCache
	 *
	 * This function make the cache of the tags.
	 *
	 * @param object &$entry_cl: The entry class (by reference)
	 * @returns boolean: does it succeed in making cache?
	 */
	function makeCache(&$entry_cl) {
		// List entries
		$o = new FPDB_Query(array('start' => 0, 'count' => -1, 'fullparse' => true, 'comments' => false), null);

		// Parse every entry to get tags
		$list = array();
		while ($o->hasMore()) {
			$earray = $o->getEntry();
			$entry_cl->tag_list($earray [1] ['content']);

			if (!count($entry_cl->tags)) {
				# No tags
				continue;
			}

			foreach ($entry_cl->tags as $tag) {
				$list [$tag] [] = $earray [0];
			}
		}

		// Make the Tag pugin directory
		@fs_mkdir(PLUGIN_TAG_DIR);

		// No tags? We've finished here ;-)
		if (!count($list)) {
			return true;
		}
		$string = '';
		// Save every tag
		foreach ($list as $tag => $entries) {
			$string .= $tag . " => " . $this->tagfile($tag) . " \n";
			$this->files [$this->tagfile($tag)] [$tag] = $entries;
		}

		return $this->save_all();
	}

	/**
	 * Return the file ID for a tag.
	 *
	 * @param string $tag: The tag
	 * @return string: The file ID
	 */
	function tagfile($tag) {
		$file = substr($tag, 0, 1);

		if (preg_match('/^[a-z]/i', $tag)) {
			$file = strtolower($file);
		} elseif (is_numeric($file)) {
			$file = 'd'; // as digits
		} else {
			$file = 's'; // as symbols
		}

		return $file;
	}

	# Manage rewritten URLs.
	/**
	 * rewriteCache
	 *
	 * This function makes the cache for URL-Rewriting
	 * and returns it.
	 *
	 * @param boolean $force: Force to re-make cache? [false]
	 * @returns array: The cache
	 */
	function rewriteCache($force = false) {
		// Already done? Return it!
		if (!empty($this->rewriteCachesan) && !$force) {
			return $this->rewriteCachesan;
		}

		// File exists? Load it and return it!
		if (file_exists(CACHE_DIR . 'tag-rewrite.tmp') && !$force) {
			$sanitized = array();
			include CACHE_DIR . 'tag-rewrite.tmp';
			if (is_array($sanitized)) {
				$this->rewriteCachesan = $sanitized;
				return $sanitized;
			}
		}

		// Otherwise, let's create the cache
		$lister = new tag_lister();
		$list = $lister->_list;
		if (!count($list)) {
			return array();
		}

		$tags = array();
		foreach ($list as $file) {
			$tag = array();
			include PLUGIN_TAG_DIR . $file . '.txt';
			$tags = array_merge($tags, array_keys($tag));
		}
		if (!count($tags)) {
			return array();
		}

		$sanitized = array();
		foreach ($tags as $tag) {
			$san_tag = sanitize_title($tag);
			$a = $san_tag;

			// Leave doubles
			$i = 2;
			while (isset($sanitized[$a])) {
				$a = $san_tag . '-' . $i;
				$i++;
			}

			$sanitized [$a] = $tag;
		}

		// Save
		$this->rewriteCachesan = $sanitized;
		system_save(CACHE_DIR . 'tag-rewrite.tmp', array('sanitized' => $sanitized));

		return $sanitized;
	}

	/**
	 * This function returns all entries that have a tag.
	 *
	 * @param string $tag: The tag you want to have the list
	 * @return array: The entries that have this tag
	 */
	function taggedEntries($tag) {
		$file = $this->open_file($this->tagfile($tag));

		if (!is_array($file)) {
			return array();
		}

		if (isset($file [$tag]) && is_array($file [$tag])) {
			return $file [$tag];
		}

		return array();
	}
}
?>
