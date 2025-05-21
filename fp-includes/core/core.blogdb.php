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
	 * @param string $id string formatted like "prefixYYMMDD-HHMMSS.EXT"
	 * @return string
	 */
	function bdb_idtofile($id,$type=null) {
	
		$fname = $id.EXT;
		
		$date = date_from_id($id);
		
		if (!$date)
			return false;
		
		$path = CONTENT_DIR . $date['y'] . '/' . $date['m'] . '/';
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
	function bdb_idfromtime($type, $timestamp=null) {
		if (!$timestamp)
			$timestamp=time();
			
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
	 * - VERSION	:	SimplePHPBlog or compatible blogs' version identifier string
	 * - SUBJECT 	:	Subject of the entry
	 * - CONTENT	:	Content of the entry
	 * - DATE		:	UNIX filestamp to format by {@link date_format()}.
	 * 
	 * comments usually provide also 
	 * - NAME 	:	author name
	 * - EMAIL	:	author email (if any)
	 * - URL	:	author website url (if any)
	 *
	 * A common usage of the function could be
	 * <code>
	 * <?php
	 * $entry = bdb_parse_entry(bdb_filetoid($myid));
	 * ?>
	 * </code>
	 *
	 * @param string $file filepath of the blogdb entry
	 * @return string
	 *
	 * @todo validate returned id
	 */
	function bdb_parse_entry($id, $type=null) {
	
		if (file_exists($id))
			$file = $id;
		else
			$file = bdb_idtofile($id, $type);
			
		
		if (file_exists($file)) {
			$contents = io_load_file($file);
			// TODO: here we must add compatibility to encoding conversion!
			// if "dumb" (legacy :D) mode is enabled (set to true in default.php, then we set parsing
			// to ignore array key case (defaults to true i.e. check them to be uppercase or failing otherwise
			$entry = utils_kexplode($contents, '|', !DUMB_MODE_ENABLED);
		
			return $entry;
		} else return false; 
		
	}

?>