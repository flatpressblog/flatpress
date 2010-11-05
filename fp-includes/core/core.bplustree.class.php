<?php

/*
 *
 * PHP B+tree library
 * ==============
 *
 * (c)2008 E.Vacchi <real_nowhereman at users.sourceforge.net>
 * Based on the original work by Aaron Watters (bplustree.py)
 *
 * Classes: 
 *
 * - BPlustTree
 *   Mapping keys, integers
 *
 * - caching_BPT
 *   Subclass of BPlusTree, caching
 *   key,value pairs
 *   read-only: create using BPlusTree, read
 *   using caching_BPT
 *
 *
 * Usage
 * =====
 *
 * # creation
 * $f = fopen('myfile', 'w+');
 * $o = new BPlusTree($f, $seek_start, $node_size, $keylen);
 * $o->startup();
 *
 * $o->setitem('my-key', 123);
 * $o->setitem('my-key-2', 456);
 *
 * $o->delitem('my-key-2');
 *
 *
 * # read-only
 * $f = open('myfile', 'r');
 * $o = caching_BPT($f);
 * 
 * $o->open();
 * echo $o->getitem('my-key');
 *
 * Methods:
 *
 * void setitem($key, $val)
 * int getitem($key)
 * void delitem($key)
 * bool has_key($key)
 * object walker() : returns an iterator
 *
 *
 * Walking (iterate)
 * =================
 *
 * $walker = $tree->walker(
 * 		string $key_lower, bool $include_lower,
 * 		string $key_upper, bool $include_upper);
 * while ($walker->valid) {
 *	echo $walker->current_key(), 
 *	     $walker->current_value();
 *	$walker->next();
 * }
 * $walker->first(); #resets internal pointer
 *
 *
 * Internal FIFO
 * =============
 *
 * $tree->enable_fifo(); 
 * do_some_processing();
 * $tree->disable_fifo();
 *
 * This should make the processing (slightly) faster
 * when key accesses are localized. Don't use it in walking
 * (no need for it) or for single write operations.
 *
 * enable_fifo() takes an optional parameter $length 
 * (defaults to 33) remember that larger fifos will consume
 * more memory.
 *
 *
 * Other options
 * =============
 *
 * This PHP implementation slightly differs from the Python
 * one, because you can choose a constant (affecting all of the
 * instanced objects) defining the order relation of the keys.
 *
 * Usually you would like your keys to be sorted ascending
 * (SORT_ASC, the default), but sometimes you might want
 * to create a btree where keys are kept in reverse order.
 *
 * In this case, you just have to define somewhere in your code
 *
 *   define('BPT_SORT', SORT_DESC);
 *
 * and the include the library.
 * 
 * This somehow weird approach should however make the computation
 * slightly faster: the setting is evaluated only once,
 * when including the library; the compare routine is then defined 
 * accordingly, and never checked again; otherwise the compare
 * routine would have to check the setting each time it's called.
 *
 */ 


function d($s) {
	return;	// disable debug output
	if (is_array($s)) { $s = '{ '.implode(", ", $s) . ' }'; }

	$x = debug_backtrace();
	$f = @$x[1]['function'];
	$l = $x[0]['line'];

	echo "[{$f}:{$l}]\t", $s, "\n";
	#echo "---[{$x[2]['function']}:{$x[2]['line']}]\n";
}

error_reporting(E_ALL);


if (!defined('BPT_SORT')) {
/**
 * @const int type of sorting, defaults to SORT_ASC (ascending); 
 * SORT_DESC (descending) is also possibile
 */
define('BPT_SORT',  SORT_ASC);
}


/**
 * @const int no room error
 */
define('NOROOMERROR', -100);

/**
 * @const int null node
 */
define('BPT_NULL', 0);
/**
 * @const int null seek position
 */
define('BPT_NULLSEEK', 0);
/**
 * @const string magic string for bplustree
 */
define('BPT_VERSION_MAGIC', 'BPT01');


#define('BPT_INT_SIZE', 4);


/**
 * @const int root bit flag
 */
define('BPT_FLAG_ROOT_BIT', 1);
/**
 * @const int interior node flag 
 */
define('BPT_FLAG_INTERIOR', 2);
/**
 * @const int root flag, shorthand for BPT_FLAG_ROOT_BIT | BPT_FLAG_INTERIOR
 */
define('BPT_FLAG_ROOT', BPT_FLAG_ROOT_BIT | BPT_FLAG_INTERIOR);
/**
 * @const int free node flag
 */
define('BPT_FLAG_FREE', 4);
/**
 * @const int leaf flag
 */
define('BPT_FLAG_LEAF', 8);
/**
 * @const int leaf+root flag, shorthand for BPT_FLAG_ROOT_BIT | BPT_FLAG_LEAF
 */
define('BPT_FLAG_LEAFANDROOT', 	BPT_FLAG_ROOT_BIT | BPT_FLAG_LEAF);

/**
 * Abstraction for array of pairs 
 * (meaning with "pair" an array containing two elements)
 * works only read-only
 *
 */
class pairs {
	/**
	 * @var array of the first elements of each pair (private)
	 *
	 */
	var $a; 
	/**
	 * @var array of the second elements of each pair (private)
	 *
	 */
	var $b; 
	/**
	 * @var integer current size of the array of pairs
	 *
	 */
	var $count;
	
	/*
	 * Constructor
	 * @param array $a array of the first elements of each pair
	 * @parma array $b array of the second elements of each pair
	 *
	 */
	function pairs($a,  $b) { 
		if (($v=count($a))!=count($b)) 
			trigger_error("Size of params must match", E_USER_ERROR);
		$this->a=$a; $this->b=$b; 
		$this->count = $v;
	}
	/*
	 * returns a slice of the current Couplets object as a new Couplets object
	 * (works like array_slice())
	 * @param integer $offset offset from the start of the array (count starting from zero)
	 * @param integer|null $count number of elements to return starting from $offset
	 *
	 * @returns pairs object
	 *
	 */
	function &slice($offset, $count=null) {
		if (is_null($count)) $count = $this->count;

		$a = new pairs(
			array_slice($this->a, $offset, $count),
			array_slice($this->b, $offset, $count)
		);

		return $a;
	}
	/**
	 * inserts a pair ($a, $b) at the offset $offset eventually
	 * pushing other elements to the right
	 * @param int $offset offset at which insert
	 * @param mixed $a first element in the pair
	 * @param mixed $b second element in the pair
	 */
	function insert($offset, $a, $b) {
		array_splice($this->a, $offset, 0, $a);
		array_splice($this->b, $offset, 0, $b);
		$this->count++;
	}
	/**
	 * inserts a pair ($a, $b) in the sub-array of pairs
	 * between $lo and $hi, assuming the array is ordered,
	 * comparing only the first elements of each pair 
	 * (assumes there aren't duplicates)
	 * uses {@link BPT_keycmp} for comparing
	 *
	 * @param mixed $a first element of the pair
	 * @param mixed $b second element of the pair
	 * @param int	$lo starting offset of the sub-array
	 * @param int|nul $hi ending offset of the sub-array
	 */
	function insort($a, $b, $lo=0, $hi=null) {
		if (is_null($hi)) $hi=$this->count;
		$A = $this->a;
		$X = $a;
		while($lo<$hi) {
			$mid = (int)(($lo+$hi)/2);
			if (BPT_keycmp($X,$A[$mid])<0) $hi=$mid;
			else $lo=$mid+1;
		}
		$this->insert($lo, $a, $b);
	}
	/**
	 * removes the pair at the offset $offset
	 * @param int $offset offset of the pair targeted for deletion
	 */ 
	
	function remove($offset) {
		array_splice($this->a, $offset, 1);
		array_splice($this->b, $offset, 1);
		$this->count--;
	}
	/**
	 * append at the end of the current object the contents
	 * of another pairs object
	 *
	 * @param pairs $pairs a pair object of which the contents
	 * 		will be appended to this
	 */
	function append(&$pairs) {
		array_splice($this->a, $this->count, 0, $pairs->a);
		array_splice($this->b, $this->count, 0, $pairs->b);
		$this->count+=$pairs->count;
	}
	/**
	 * make the object fields read-only
	 */
	function __set($x,$y) {
		trigger_error("Can't edit pairs directly'", E_USER_ERROR);
	}

}


if (BPT_SORT == SORT_ASC) {
/**
 * compares key $a and $b using a less-than or greather-than relation
 * depending on {@link BPT_SORT} constants
 *
 * the function is very simple, returns strcmp($a,$b) or -strcmp($a,$b)
 * depending on the BPT_SORT constant: to be a little bit faster, no check is done
 * by the function itself; instead it is <strong>defined</strong> at load time, depending
 * on the value of the BPT_SORT constant
 *
 */
function BPT_keycmp($a,$b) {	return strcmp($a,$b);	}
} else {
function BPT_keycmp($a,$b) {	return -strcmp($a,$b);	}
}

/*
function _BPT_bisect($a, $x, $lo=0, $hi=null) {
	if (is_null($hi))
		$hi=count($a);
	while($lo<$hi && $a[$lo++]<$x) ;
	return $lo;
}
 */
/**
 * locate an element $x or the nearest bigger one
 * in the array $a, starting from offset $lo
 * and limiting to offset $hi, assuming that $a is 
 * ordered by the relation BPT_keycmp
 * 
 * @param mixed $a source array
 * @param mixed $x element to find
 * @param int $lo leftmost offset
 * @param int|null $hi rightmost offset
 *
 * @returns integer
 *
 */

function BPT_bisect($a, $x, $lo=0, $hi=null) {
	if (is_null($hi)) {
		$hi = count($a);
	}
	while ($lo < $hi) {
		$mid = (int)(($lo+$hi)/2);
		#if ($x < $a[$mid]) 
		if (BPT_keycmp($x,$a[$mid])<0)
			$hi = $mid;
		else
			$lo = $mid+1;
	}
	return $lo;
}

/*
function BPT_insort(&$a, $x, $lo=0, $hi=null) {
	if (is_null($hi))
		$hi = count($a);
	while ($lo<$hi) {
		$mid = (int) (($lo+$hi)/2);
		if ($x < $a[$mid])
			$hi = $mid;
		else 
			$lo = $mid+1;
	}
	array_splice($a, $lo, 0, array($x));
}
*/

/*
 * fifo of bplustree nodes
 */
class BPlusTree_Node_Fifo {
	/**
	 * @var array array of elements
	 */
	var $fifo = array();
	/**
	 * @var array dictionary (associative array) of elements
	 */
	var $fifo_dict = array();
	/**
	 * var int size of the fifo
	 */
	var $size;
	
	/**
	 * constructor
	 * @param int $size specifies size (defaults to 30)
	 */
	function BPlusTree_Node_Fifo($size=30) {
		$this->fifosize=$size;
	}

	/**
	 * flushes all of the contents of the fifo
	 * to disk
	 */
	function flush_fifo(){
		reset($this->fifo);
		while(list(,$node)=each($this->fifo)){
			if ($node->dirty) {
				$node->store(1);
			}
		}
		$this->fifo = array();
		$this->fifo_dict = array();
	}
}

/**
 * defines structure and methods of the node
 * of a bplustree
 */

class BPlusTree_Node {
       
	/**
	 * @var integer flags (defined as BPT_* constants)
	 * specifying the nature of the node (leaf, interior, and combos)
	 *
	 */
	var $flag;
  	/**
	 * @var integer number of child elements (or values, if a leaf)
	 *
	 */
	var $size;      
	/**
	 * @var int seek position in the file
	 *
	 */
	var $position;
	/**
	 * @var resource stream where to output the data 
	 * (typically a file open with fopen())
	 */
	var $infile;    
	/**
	 * @var int maximum lenght of a string key
	 */
	var $keylen;   
	/**
	 * @var array array of strings, containing keys, of size $size
	 */
	var $keys;
	/**
	 * @var array array of longs, of size $size+1
	 *	if leaf, elements in [0,$size] are the values of each key in $keys: 
	 *		at offset $size - ($size+1)-th element - there's the seek
	 *		position of the next leaf (or BPT_NULLSEEK if rightmost leaf)
	 *	
	 *	if interior, 
	 *
	 *		- offset 0 points to the child node where keys are
	 *		  are all LESS than those in this node (actually, to $keys[0]), 
	 *
	 *		- offset 1 points to the child node where keys are GREATER or EQUAL to $keys[0]
	 *		  but LESS than $keys[1],
	 *
	 *		- offset 2 points to the child node where keys are >= $keys[1] but < $keys[2], etc...
	 *
	 *
	 *	with LESS, GREATER we always mean by the relation {@link BPT_keycmp}
	 *
	 *		
	 */
	var $indices;  
        /**
	 * @var bool controls deferred writes (using fifo) 
	 *
	 */
	var $dirty= false;

	/**
	 * @var BPlusTree_Node_Fifo object of type {@link BPlusTree_Node_Fifo}
	 */
	var $fifo = null;
	/**
	 * @var int number of valid keys in $keys
	 */
	var $validkeys;
	

	/**
	 * constructor
	 * @param int $flag flag of current node	
	 * @param int $size size of node
	 * @param int $keylen max key length
	 * @param long $position seek position in file
	 * @param resource resource stream (opened file)
	 * @param BPlusTree_Node object from which cloning properties
	 */
	function BPlusTree_Node($flag, 
				$size, 
				$keylen,
				$position, 
				$infile, 
				$cloner = null) {
				
		$this->flag = $flag;

		if ($size < 0) {
			trigger_error('size must be positive', E_USER_ERROR);
		}

		$this->size = $size;

		$this->keylen = $keylen;
		$this->position = $position;
		$this->infile = $infile;
		// last (+1) is successor seek TODO move to its own!
		$this->indices = array_fill(0, $size+1, BPT_NULL);
		$this->keys = array_fill(0, $size, '');
		
		if (is_null($cloner)) {
			$this->storage = 2 +
			/* 2 chars for flag, validkeys */
			$size*4+4 + /* n 4B-long indices + 1 4B-long next pointer*/
			$size*$keylen ; /* n keylen-bytes long keys */
		} else {
			$this->storage	= $cloner->storage;
			$this->fifo	= $cloner->fifo;
		}

		if ($flag == BPT_FLAG_INTERIOR || $flag == BPT_FLAG_ROOT) {
			$this->validkeys = -1;
		} else {
			$this->validkeys = 0;
		}

	}

	/** 
	 * 
	 * reinitialize keys
	 *
	 */

	function clear() {
		$size = $this->size;
		// re-init keys

		$this->keys = array_fill(0, $size, '');
		$this->validkeys = 0;
		if (($this->flag & BPT_FLAG_INTERIOR) == BPT_FLAG_INTERIOR) {
			// re-init all indices
			$this->indices = array_fill(0, $size+1, BPT_NULL);
			$this->validkeys = -1;
		} else {
			$fwd = $this->indices[$size]; // forward pointer
			$this->indices = array_fill(0, $size, BPT_NULL);
			$this->keys = array_fill(0, $size, '');
			$this->indices[] = $fwd;
		}
	}
	
	/**
	 * returns clone of the obect at position $position
	 * @param long $position seek position
	 */
	function &getclone($position) {
		
		if ($this->fifo) {
			$dict =& $this->fifo->fifo_dict;
			if (isset($dict[$position])) {
				return $dict[$position];
			}
		}
		
		
		$o = new BPlusTree_Node(
				$this->flag, 
				$this->size, 
				$this->keylen, 
				$position, 
				$this->infile, 
				$this
		);
		return $o;
	}
	
 	/**
	 * put first index (seek position for less-than child)
	 *
	 * @param int $index seek position
	 */
	function putfirstindex($index) {
		if ($this->validkeys>=0)
			trigger_error("Can't putfirstindex on full node", E_USER_ERROR);
		$this->indices[0] = $index;
		$this->validkeys = 0;
	}

         /**
	  * links node $node to this node as a child, using key $key
	  * (this node must be interior)
	  * 
	  * @param string $key key string
	  * @param object $node node to link
	  *
	  */
	function putnode($key, &$node) {
		$position = $node->position;
		return $this->putposition($key, $position);
		# if ($x == NOROOMERROR) {print_r(debug_backtrace());fail();}
	}
  
	/*
	 * 
	 * links a seek position $position to the key $key
	 * 
	 * @param string $key key string
	 * @param int $position seek position (pointer to the new child node)
	 *
	 */

	function putposition($key, $position) {

		if (($this->flag & BPT_FLAG_INTERIOR) != BPT_FLAG_INTERIOR) {
			trigger_error("Can't insert into leaf node", E_USER_ERROR);
		}

		$validkeys = $this->validkeys;
		$last = $this->validkeys+1;

		if ($this->validkeys>=$this->size) {
			#trigger_error('No room error', E_USER_WARNING);
			return NOROOMERROR;
		}

		// store the key
		if ($validkeys<0) {  // no nodes currently
			d("no keys");
			$this->validkeys = 0;
			$this->indices[0] = $position;
		} else {
			// there are nodes
			$keys =& $this->keys;
			// is the key there already?
			if (in_array($key, $keys, true)) {
				if (array_search($key, $keys, true) < $validkeys)
					trigger_error("reinsert of node for existing key ($key)",
							E_USER_ERROR);
			}

			$place = BPT_bisect($keys, $key, 0, $validkeys);
			// insert at position $place
			array_splice($keys, $place, 0, $key);
			// delete last element
			unset($keys[$last]);
			$keys = array_values($keys); # reset array indices
			#array_splice($keys, $last, 1);

			// store the index
			$indices =& $this->indices;
			#echo "inserting $position before ", var_dump($indices,1), "\n";
			array_splice($indices, $place+1, 0, $position);
			unset($indices[$last+1]);
			$indices = array_values($indices);
			#array_splice($indices, $last+1, 1);
			$this->validkeys = $last;
		}
	}

	
	/**
	 * deletes from interior nodes
	 *
	 * @param string $key target key
	 */

	function delnode($key) {
		// {{{
		if (($this->flag & BPT_FLAG_INTERIOR) != BPT_FLAG_INTERIOR) {
			trigger_error("Can't delete node from leaf node");
		}
		if ($this->validkeys < 0) {
			trigger_error("No such key (empty)");
		}

		$validkeys = $this->validkeys;
		$indices =& $this->indices;
		$keys =& $this->keys;
		if (is_null($key)) {
			$place = 0;
			$indexplace = 0;
		} else {
			$place = array_search($key, $keys, true);
			$indexplace = $place+1;
		}
		
		#unset($indices[$indexplace]);
		array_splice($indices, $indexplace, 1);
		$indices[] = BPT_NULLSEEK;
                #$indices = array_values($indices);

		#unset($keys[$place]);
		array_splice($keys, $place, 1);
		$keys[] = '';
		#$keys = array_values($keys);

		$this->validkeys = $validkeys - 1;
	}
	// }}}

	/**
	 * slices the $this->keys array to the number of valid keys 
	 * in $this->validkeys
	 *
	 * @returns array array of valid keys
	 */

	function get_keys() {
		$validkeys = $this->validkeys;
		if ($validkeys<=0) {
			return array();
		} 
		
		return array_slice($this->keys, 0, $validkeys);
	}
	
	
	/*
	 * mimics python's map(None, a, b)
	 * returns the list of (a,b) pairs 
	 * where a is in list $a and b is in list $b
	 *
	 *
	 
	function _oldpairs($a, $b) {
		$c = array();
		reset($a);
		reset($b);
		while((list(,$v1) = each($a)) &&
			(list(,$v2) = each($b))) {
			$c[] = array($v1, $v2);
		}
		return $c;
	}
	 */

	
	/**
	 * mimic's python's map(None, a, b); 
	 * a, b must be of the same size
	 *
	 * @param array $a first array
	 * @param array $b second array
	 *
	 * @returns object {@link pairs}
	 */
	function &_pairs($a, $b) {
		$x = new pairs($a,$b);
		return $x;

	}


	/**
	 * returns an object containing pairs (key, index) 
	 * for all of the valid keys and indices
	 * 
	 * @param string $leftmost leftmost key corresponding 
	 *			to first index (seek) in interior nodes; ignored in leaves
	 *
	 * @returns object pairs
	 *
	 */
	function keys_indices($leftmost) {
		$keys = $this->get_keys();
		if (($this->flag & BPT_FLAG_INTERIOR) == BPT_FLAG_INTERIOR) {
			// interior nodes start with 
			// the pointer to the "less than key[0]" subtree:
			// we need pairs (key, indices) so we add the leftmost key
			// on top
			array_unshift($keys, $leftmost);
		}
		$indices = array_slice($this->indices, 0, count($keys));
		return $this->_pairs($keys, $indices);
	}


	/**
	 * returns child, searching for $key in an interior node
	 *
	 * @param string $key target $key
	 * @returns object BPlusTree_Node
	 *
	 */
	function &getnode($key) {
		if (($this->flag & BPT_FLAG_INTERIOR) != BPT_FLAG_INTERIOR) {
			trigger_error("cannot getnode from leaf node", E_USER_ERROR);
		}
		if (is_null($key))
			$index = 0;
		else 
			$index = array_search($key, $this->keys, true)+1;

		$place = $this->indices[$index];
		if ($place<0) {
			debug_print_backtrace();
			trigger_error("Invalid position! ($place, $key)", E_USER_ERROR);
		}

		// fifo
		
		$fifo =& $this->fifo;
		if ($fifo) {
			$ff =& $fifo->fifo;
			$fd =& $fifo->fifo_dict;
			if (isset($fd[$place])) {
				$node =& $fd[$place];
				#unset($ff[$place]);
				$idx = array_search($node, $ff, true);
				array_splice($ff, $idx, 1);
				array_unshift($ff, $node);
				return $node;
			}
		}
		
		$node =& $this->getclone($place);
		$node =& $node->materialize();
		return $node;
	}

	/***** leaf mode operations *****/

	/**
	 * if leaf returns the next leaf on the right
	 *
	 */

	function &next() {
		if (($this->flag & BPT_LEAF_FLAG) != BPT_FLAG_LEAF) {
			trigger_error("cannot get next for non-leaf", E_USER_ERROR);
		}
		$place = $this->indices[$this->size];
		if ($place == BPT_NULLSEEK)
			return null;
		else {
			$node =& $this->getclone($place);
			$node =& $node->materialize();
			return $node;
		}
			
	}

	/*
	function &prev() {
 		if (($this->flag & BPT_LEAF_FLAG) != BPT_FLAG_LEAF) {
			trigger_error("cannot get next for non-leaf", E_USER_ERROR);
		}
		$place = $this->prev;
		if ($place == BPT_NULLSEEK)
			return null;
		else {
			$node =& $this->getclone($place);
			$node =& $node->materialize();
			return $node;
		}
        	
	}
	 */

	/**
	 * put ($key, $val) in a leaf
	 *
	 * @param string $key target string
	 * @param int	$val value for $key
	 */
	function putvalue($key, $val) {
		if (!is_string($key))
			trigger_error("$key must be string", E_USER_ERROR);

		if (($this->flag & BPT_FLAG_LEAF) != BPT_FLAG_LEAF) {
			#print_r($this);
			trigger_error("cannot get next for non-leaf ($key)", E_USER_ERROR);
		}
		$validkeys = $this->validkeys;
		$indices =& $this->indices;
		$keys =& $this->keys;

		if ($validkeys<=0) { // empty
			// first entry
			$indices[0] = $val;
			$keys[0] = $key;
			$this->validkeys = 1;
		} else {
			$place = null;
			if (in_array($key, $keys, true)) {
				$place = array_search($key, $keys, true);
				if ($place >= $validkeys) {
					$place = null;
				}
			}
			if (!is_null($place)) {
				$keys[$place] = $key;
				$indices[$place] = $val;
			} else {
				
				if ($validkeys >= $this->size) {
					#trigger_error("no room", E_USER_WARNING);
					return NOROOMERROR;
				}

				$place = BPT_bisect($keys, $key, 0, $validkeys);
				$last = $validkeys+1;
			
				# del keys[validkeys]
				# del indices[validkeys]
				#array_splice($keys, $validkeys, 1);
				unset($keys[$validkeys]);
				$keys = array_values($keys);
				#array_splice($indices, $validkeys, 1);
				unset($indices[$validkeys]);
				$indices = array_values($indices);

				array_splice($keys, $place, 0, $key);
				array_splice($indices, $place, 0, $val);
				
                                #echo implode(', ', $keys), " ::: $place \n";
				$this->validkeys = $last;

			}
		}
	}

	/**
	 * for each $key, $index in $keys_indices 
	 * put the correspoding values (assumes this is a leaf)
	 *
	 * @param object $keys_indices object of type {@link pairs}
	 */
	function put_all_values($keys_indices) {
		$this->clear();
		$indices =& $this->indices;
		$keys =& $this->keys;
		$length = $this->validkeys = $keys_indices->count;#count($keys_indices);
		if ($length > $this->size)
			trigger_error("bad length $length", E_USER_ERROR);

		for ($i=0; $i<$length; $i++) {
		       #list($keys[$i], $indices[$i]) = $keys_indices[$i];
		       $keys[$i] = $keys_indices->a[$i];
		       $indices[$i] = $keys_indices->b[$i];
		}
	}


	/**
	 * for each $key, $index in $keys_indices 
	 * put the correspoding seek positions (assumes this is an interior node)
	 *
	 * @param int $first_position leftmost pointer (to less-than child)
	 * @param object $keys_indices object of type {@link pairs}
	 * 
	 */

        function put_all_positions($first_position, $keys_positions) {
		$this->clear();
		$indices =& $this->indices;
		$keys =& $this->keys;
		$length = $this->validkeys = $keys_positions->count;#count($keys_positions);
		if ($length > $this->size) {
			trigger_error("bad length $length", E_USER_ERROR);
		}
		$indices[0] = $first_position;
		for ($i=0; $i<$length; $i++) {
			#list($keys[$i], $indices[$i+1]) = $keys_positions[$i];
			$keys[$i] = $keys_positions->a[$i]; 
			$indices[$i+1] = $keys_positions->b[$i];
		}
	} 


	/**
	 * assuming this is a leaf, returns value for $key
	 * @param $key string target key
	 * @returns int|false corresponding integer or false if key is missing
	 *
	 */
	function getvalue(&$key, $loose=false) {

		#d(implode(",",$this->keys));
		#$place = array_search($key, $this->keys);
		$place = BPT_bisect($this->keys, $key, 0, $this->validkeys);
		if ($this->keys[$place-1] == $key) {
			return $this->indices[$place-1];
		} else {
			if ($loose) {
				if ($place>1) $place--;
				$key = $this->keys[$place];
				return $this->indices[$place];
			}
			trigger_error("key '$key' not found", E_USER_WARNING);
			return false;
		}
			
	}

	/**
	 * if leaf, creates a neighbor for this node: a new leaf
	 * linked to this
	 *
	 * @param int $position seek position for the new neighborù
	 * @returns object BPlusTree_Node
	 *
	 */
	function &newneighbour($position) {
		if (($this->flag & BPT_FLAG_LEAF) != BPT_FLAG_LEAF)
			trigger_error('cannot make leaf neighbour for non-leaf');

		// create clone
		$neighbour =& $this->getclone($position);
		$size = $this->size;
		$indices =& $this->indices;

		// linking siblings
		$neighbour->indices[$size] = $indices[$size];
		$indices[$size] = $position;
		return $neighbour;
	}

	/**
	 * if leaf, returns the leaf next to this
	 * @return object BPlusTree_Node
	 */
	function &nextneighbour() {
 		if (($this->flag & BPT_FLAG_LEAF) != BPT_FLAG_LEAF)
			trigger_error('cannot get leaf neighbour for non-leaf');

		$size = $this->size;
		$position = $this->indices[$size];
		if ($position == BPT_NULLSEEK) {
			$neighbour = null;
		} else {
			$neighbour = $this->getclone($position);
			$neighbour = $neighbour->materialize();
		}

		return $neighbour;
        
	}
	
	/*
	function &prevneighbour() {
	 	if (($this->flag & BPT_FLAG_LEAF) != BPT_FLAG_LEAF)
			trigger_error('cannot get leaf neighbour for non-leaf');

		#$size = $this->size;
		$position = $this->prev; # $this->indices[$size];
		if ($position == BPT_NULLSEEK) {
			return null;
		} else {
			$neighbour = $this->getclone($position);
			$neighbour = $neighbour->materialize();
			return $neighbour;
		}
	
	}*/

	/**
	 * if leaf, deletes neighbor on the right, and re-link
	 * with the following
	 *
	 * @param object $next target for deletion
	 * @param free $free seek position of last free node in free list
	 *
	 * @returns int new free position
	 */
	function delnext(&$next, $free) {
		d("delnext called:");
		#print_r($this);
		$size = $this->size;
		if ($this->indices[$size]!=$next->position) {
			trigger_error("invalid next pointer ".
			"{$this->indices[$size]}!={$next->position})", E_USER_ERROR);
		}
		$this->indices[$size] = $next->indices[$size];
		return $next->free($free);
	}

	/**
	 * if leaf, deletes corresponding value
	 *
	 * @param string $key target key
	 */
 	function delvalue($key) {
		$keys =& $this->keys;
		$indices =& $this->indices;
		if (!in_array($key, $keys, true)) {
			d($keys);
			trigger_error ("missing key, can't delete", E_USER_ERROR);
		}
		$place = array_search($key, $keys, true);
		$validkeys = $this->validkeys;
		$prev = $validkeys-1;

		# delete
		array_splice($keys, $place, 1);
		array_splice($indices, $place, 1);
		#unset($keys[$place]);
		#$keys[]='';
		#$keys = array_values($keys);
		#unset($indices[$place]);
		#$indices[] = BPT_NULL;
		#$indices = array_values($indices);
		
		# insert NULLs/empties
		array_splice($keys, $prev, 0, '');
		array_splice($indices, $prev, 0, BPT_NULL);
		
		$this->validkeys=$prev;//validkeys-1

	}
 
	/*
	 * add self to free list, retunr position as new free position 
	 *
	 * @param int $freenodeposition current last free node
	 *
	 */
	function free($freenodeposition) {
		$this->flag = BPT_FLAG_FREE;
		$this->indices[0] = $freenodeposition;
		$this->store();
		return $this->position;
	}
	
	/*
	 * assuming self is head of free list,
	 * pop self off freelist, return next free position;
	 * does not update file
	 *
	 * @param integer $flag flag for new node
	 * @return object new node
	 *
	function unfree($flag) {
		$next = $this->indices[0];
		$this->flag = $flag;
		$this->validkeys = 0;
		$this->indices[0] = BPT_NULLSEEK;
		$this->clear();
		return $next;
	}
	 */

	/**
         * get free node of same shape as self from $this->file;
	 * make one if none exist;
	 * assume $freeposition is seek position of next free node
	 *
	 * @param	int	  $freeposition seek position of next freenode
	 * @param	callback  $freenode_callback is specified it is a func to call
	 * 	       			with a new free list head, if needed  
	 *
	 * @returns array(&$node, $newfreeposition)
	 *
	 *
	 * 
	 *
	 */
	function getfreenode($freeposition, $freenode_callback=null) {
		d("GETTING FREE AT $freeposition");
		if ($freeposition == BPT_NULLSEEK) {
			$file = $this->infile;
     		   	fseek($file, 0, SEEK_END);
			$position = ftell($file);
			d("ALLOCATING SPACE...");
			$thenode =& $this->getclone($position);
			$thenode->store();
			return array(&$thenode, BPT_NULLSEEK);
		} else {
			$position = $freeposition;
			$thenode = $this->getclone($position);
			// get old node
			$thenode = $thenode->materialize();
			// ptr to next
			$next = $thenode->indices[0];
			if (!is_null($freenode_callback)) {
				call_user_func($freenode_callback, $next);
			}

			$thenode->BplusTree_Node(
				$this->flag, 
				$this->size, 
				$this->keylen, 
				$position, 
				$this->infile
			);

			$thenode->store(); // save reinit'ed node
                 	return array(&$thenode, $next);
		}
	}


	/**
	 *
	 * write this to file
	 *
	 * @param bool $force forces write back if fifo is enabled, defaults to false
	 *
	 */

	function store($force = false) {
	// {{{
		$position = $this->position;
		if (is_null($position))
			trigger_error("position cannot be null",E_USER_ERROR);
		
		$fifo =& $this->fifo;
		if (!$force && $fifo) {
			$fd =& $fifo->fifo_dict;
			if (isset($fd[$this->position]) && $fd[$position] === $this) {
				$this->dirty = true;
				return; // defer processing
			}
		}
		
		$f = $this->infile;
		fseek($f, $position);
		$data = $this->linearize();
		fwrite($f, $data);
		$last = ftell($f);
		$this->dirty = false;
		
		if (!$force && $this->fifo) {
			$this->add_to_fifo();
		}
		
		return $last;

	}
	//}}}
	
	/**
	 * load node from file
	 *
	 * @returns object BPlusTree_Node
	 *
	 */
	function &materialize() {
		$position = $this->position;
		
		if ($this->fifo) {
			$fifo	=& $this->fifo;
			$dict	=& $fifo->fifo_dict;
			$ff	=& $fifo->fifo;
			if (isset($dict[$position])) {
				$node =& $dict[$position];
				if ($node !== $ff[0]) {
					$nidx = array_search($node, $ff, true);
					unset($ff[$nidx]);
					array_unshift($ff, $node);
				}
				return $node;
			}
		}
		
                $f = $this->infile;
		fseek($f, $position);
		$data = fread($f, $this->storage);
		$this->delinearize($data);
		
		
		if ($this->fifo) {
			$this->add_to_fifo();
		}
		


		return $this;
	}

	/**
	 * @returns string binary string encoding this node
	 */
	function linearize() {
		$params = array(
			'C2L'.($this->size+1),
			$this->flag,
			$this->validkeys
		);

		foreach($this->indices as $i)
			$params[] = $i;

	        $s = call_user_func_array('pack', $params);


		$x = '';
		for($i = 0; $i<$this->validkeys; $i++) {
			$k = $this->keys[$i];
			if (strlen($k)>$this->keylen)
				trigger_error("Invalid keylen for '$k'", E_USER_ERROR);
			$x .= str_pad($k, $this->keylen, chr(0));
		}
		
		$x = str_pad($x, $this->size*$this->keylen, chr(0));


		$s .= $x;
		$l = strlen($s);
		if (strlen($s) != $this->storage) {
			trigger_error("bad storage $l != {$this->storage}", E_USER_ERROR);
		}
	
		return $s;
	}

	/**
	 * get properties of this node from the string $s encoded via {@link BPlusTree_Node::linearize}
	 *
	 * @param string $s binary string
	 *
	 */
	
	function delinearize($s) {    
        //{{{
		if (strlen($s)!=$this->storage)
			trigger_error("bad storage", E_USER_ERROR);


		$x = 'Cflag/Cvalidkeys/';
		$n = $this->size+1;
		for ($i = 0; $i<$n; $i++) {
			$x .= "lindices{$i}/";
		}
		$arr = unpack($x, $s);

                $this->flag = $arr['flag'];
		$this->validkeys = $arr['validkeys'];

		for ($i = 0; $i<$n; $i++) {
			$this->indices[$i] = $arr["indices{$i}"];
		}
		
                for ($i = 0, $j = ($n*4+2); $i<$this->validkeys; $i++, $j+=$this->keylen) {
			
	        	$this->keys[$i] = rtrim(substr($s, $j, $this->keylen));
		}
 
	}
	//}}}

	// foo dump
	/**
	 *
	 * prints a dump of the tree on scree
	 * @param string $indent custom indentation
	 *
	 */
	function dump($indent='') {
         //{{{               
		$flag = $this->flag;
		if ($flag == BPT_FLAG_FREE) {
			echo "free->", $this->position, "\n";
			$nextp = $this->indices[0];
			if ($nextp!=BPT_NULLSEEK) {
				$next =& $this->getclone($nextp);
				$next =& $next->materialize();
				$next->dump();
			} else {
				echo "!last\n";
			}
			return;
		}
		$nextindent = $indent . "  ";
		echo $indent;
		switch ($flag) {
			case BPT_FLAG_ROOT: echo "root"; break;
			case BPT_FLAG_INTERIOR: echo "interior"; break;
			case BPT_FLAG_LEAF: echo "leaf"; break;
			case BPT_FLAG_LEAFANDROOT: echo "root&leaf"; break;
			default : echo "invalid flag??? ", $flag;
		}

       		echo "($flag) ";
		echo " ", $this->position, " valid=", $this->validkeys, "\n";
		echo $indent, "keys {", implode(', ', $this->keys), "}\n";
		echo $indent, "seeks {", implode(", ", $this->indices),"}\n";
		if (($flag & BPT_FLAG_INTERIOR) == BPT_FLAG_INTERIOR) {
			reset($this->indices);
			while(list(,$i) = each($this->indices)) {
				if ($i!=BPT_NULLSEEK) {
					// interior
					$n =& $this->getclone($i);
					$n =& $n->materialize();
					$n->dump($nextindent);
				} else {
					//leaf
					continue;
				}
			}
		}
		echo $indent, "*****\n";

	}//}}}*/

	
	/**
	 * adds this node to fifo
	 */
	function add_to_fifo() {
		$fifo	=& $this->fifo;
		$ff	=& $fifo->fifo;
		$dict	=& $fifo->fifo_dict;

		$position = $this->position;
		if(isset($dict[$position])) {
			$old =& $dict[$position];
			unset($dict[$position]);
			# ff.remove(old)
			array_splice($ff, array_search($old, $ff, true), 1);
		}
		$dict[$this->position] =& $this;
		array_splice($ff, 0, 0, array(&$this));
		if (count($ff)>$this->fifo->fifosize) {
			$lastidx = count($ff)-1;
			$last = $ff[$lastidx];
			unset($ff[$lastidx]);
			unset($dict[$last->position]);
			if ($last->dirty) {
				$last->store(true);
			}
		}
		$is_o=true;
		while((list(,$v)=each($ff)) && $is_o=is_object($v));
		if (!$is_o) {trigger_error('ERR', E_USER_ERROR);}
	}

	/**
	 * @param int $size defaults to 33
	 *
	 */

	function enable_fifo($size = 33) {
		if ($size<5 || $size>1000000) {
			trigger_error("size not valid $size");
		}
		$this->fifo = new BPlusTree_Node_Fifo($size);
	}

	/**
	 * disables fifo (first flushes to disk)
	 *
	 */

	function disable_fifo() {
		if ($this->fifo) {
			$this->fifo->flush_fifo();
			$this->fifo = null;
		}
	}
	

}

/**
 * main class BPlusTree
 * creates a B+Tree with string keys and integer values
 *
 * public methods are only {@link BPlusTree::getitem}
 * {@link BPlusTree::setitem} {@link BPlusTree::delitem}
 * {@link BPlusTree::walker}
 *
 *
 */

class BPlusTree {          
	/**
	 * @var int number of values
	 */
	var $length = null;
	/**
	 * @var bool used for deferred writes (if fifo is enabled
	 */
	var $dirty = false;
	# var $headerformat = "%10d %10d %10d %10d %10d\n";
	/**
	 * @var int seek position of root in file
	 */
	var $root_seek = BPT_NULLSEEK;
	/**
	 * @var int seek position of the start of the freelist
	 *
	 */
	var $free = BPT_NULLSEEK;
	/**
	 * @var object BPlusTree_Node root node
	 */
	var $root = null; /*  */
	/**
	 * @var int length of the file header in bytes
	 */
	var $headersize;
	/**
	 * @var bool true if fifo is enabled
	 */
	var $fifo_enabled = false;

	/**
	 * constructor
	 * @param resource $infile resource of open file
	 * @param int	$position offset from the beginning of the file (usually 0)
	 * @param int	$nodesize size of the node
	 * @param int	$keylen maximum lenght of a key in bytes (unicode extended chars evaluate to two chars)
	 */

	function BPlusTree($infile, $pos=null, $nodesize=null, $keylen=10) {
		if (!is_null($keylen) && $keylen<=2) {
			trigger_error("$keylen must be greater than 2", E_USER_ERROR);
		}
		$this->root_seek = BPT_NULLSEEK;
		$this->free = BPT_NULLSEEK;
		$this->root = null;
		$this->file = $infile;
		#if ($nodesize<6) trigger_error("nodesize must be >= 6", E_USER_ERROR);
		$this->nodesize = $nodesize;
		$this->keylen = $keylen;
		if (is_null($pos)) {
			$pos = 0;
		}
		$this->position = $pos;
		$this->headersize = 4*4+6; /* 4 4-byte longs, 1 char, 5-byte magic string*/
	}

	/**
	 * returns an iterator for the tree
	 * @param string $keylower key lower limit of the iterator
	 * @param bool|int	$includelower if true $keylower is included in the iterator;
	 * 			if $includelower > 1 then 'loose' search is assumed:
	 * 			the tree will be walked starting from
	 * 			the key $k in the tree such as $k <= $keylower
	 * 			and such as there are NO other keys $k' 
	 * 			such as $k < $k' <= $keylower
	 * @param string $keyupper key upper bound of the iterator
	 * @param bool   $includeupper if true $keyupper is included in the iterator
	 */
	function &walker(
		&$keylower,
		$includelower	=null,
		$keyupper	=null,
		$includeupper	=null
		) {
		
				$o = new BPlusWalker($this, $keylower, $includelower, $keyupper, $includeupper);
				return $o;

	}
	
	/**
	 * @returns array array of properties of this object
	 */
        function init_params() {
		return array(
		  	$this->file,
			$this->position,
			$this->nodesize,
			$this->keylen
		);
	}

	/**
	 * @returns object BPlusTree_Node of the root
	 */
	function get_root() {
		return $this->root;
	}  

	/**
	 * updates the head of the freelist and writes back to file
	 * @param int $position seek position of the head of the freelist
	 */	
	function update_freelist($pos) {
		if ($this->free!=$pos) {
			$this->free = $pos;
			$this->reset_header();
		}
	}

	/**
	 * action to perform to setup a bplustree, header is reset, length truncated
	 * and a new root node is created
	 */
	function startup() {
		if (is_null($this->nodesize) || is_null($this->keylen)) {
	       		trigger_error("cannot initialize without nodesize, keylen specified\n") ;
		}
		$this->length = 0;
		$this->root_seek = 22; //pack('a5LCL3',...)
		$this->reset_header();
		$file = $this->file;
		fseek($file, 0, SEEK_END);
		$this->root = new BplusTree_Node(
			BPT_FLAG_LEAFANDROOT, 
			$this->nodesize, $this->keylen, $this->root_seek, $file
		);
		$this->root->store();
	}

	/** 
	 * reload the bplustree from file and setup for use
	 */

	function open() {
		$file = $this->file;
		if ($this->get_parameters()===false)
			return false;
		$this->root = new BplusTree_Node(
			BPT_FLAG_LEAFANDROOT, 
			$this->nodesize, 
			$this->keylen, 
			$this->root_seek, 
			$file
		);
		$this->root =& $this->root->materialize();
		return true;
	}

	/**
	 * enable fifo
	 * @param int $size defaults to 33
	 */

	function enable_fifo($size=33) {
		$this->fifo_enabled = true;
		$this->root->enable_fifo($size);
	}

	/**
	 * disables fifo (writes back header to file if needed)
	 *
	 */
	function disable_fifo() {
		$this->fifo_enabled = false;
		if ($this->dirty) {
			$this->reset_header();
			$this->dirty = false;
		}
		$this->root->disable_fifo();
	}

	/**
	 *
	 * @returns string header string
	 */

	function _makeheader() {
 	      	return pack('a5LCL3', BPT_VERSION_MAGIC, 
		$this->length,  $this->keylen, 
		$this->nodesize, $this->root_seek, $this->free); 
	}

	/**
	 * writes back header to file (if fifo is enabled write is deferred until
	 * fifo is again disabled
	 */
	function reset_header() {
		
		if ($this->fifo_enabled) {
			$this->dirty = true;
			d("[FIFO]: deferring header reset");
			return;
		}
 	       	$file = $this->file;
		fseek($file, $this->position);
 
		$s = $this->_makeheader();

		fwrite($file, $s);
	}
	
	/**
	 * reads back properties/parameters of this tree from file;
	 * raises an error if version magic is wrong
	 *
	 * @returns bool false on failure, true on success
	 */
	function get_parameters() {
	       	$file = $this->file;
		fseek($file, $this->position);
		$data = fread($file, $this->headersize);
		$hdr = unpack('a5magic/Llength/Ckeylen/Lnodesize/Lroot_seek/Lfree', $data);
		if ($hdr['magic']!=BPT_VERSION_MAGIC) {
			trigger_error("Version magic mismatch ({$hdr['magic']}!="
				.BPT_VERSION_MAGIC.')', E_USER_WARNING);
			return false;
		}
		$this->length = $hdr['length'];
		$this->keylen = $hdr['keylen'];
		$this->nodesize = $hdr['nodesize'];
		$this->root_seek = $hdr['root_seek'];
		$this->free = $hdr['free'];
		return true;
	}

	/**
	 * @returns length of the tree (number of values)
	 */
        function length() {
		if (is_null($this->length)) {
			if (false===$this->get_parameters()) return false;
		}
		return $this->length;
	}

	/**
	 * @param string &$key key to find.
	 * @param bool $loose if true searches the tree for the "nearest" key to $key; 
	 * 		
	 * @returns int associated value
	 * 
	 */
	function getitem(&$key, $loose=false) {
		if (is_null($this->root))
			trigger_error("not open!", E_USER_ERROR);
		return $this->find($key, $this->root, $loose);
	}
	
	/**
	 * traverses tree starting from $node, searching for $key
	 * @param string $key target key
	 * @param object BPlusTree_Node starting node
	 *
	 * @returns int|bool value at the leaf node containing key or false if key is missing
	 *
	 */
	function find(&$key, &$node, $loose=false) {
		
		while (($node->flag & BPT_FLAG_INTERIOR) == BPT_FLAG_INTERIOR) {

			$thesekeys = $node->keys;
			$validkeys = $node->validkeys;

			#d(array_slice($thesekeys, 0, $validkeys));
			
			$place = BPT_bisect($thesekeys, $key, 0, $validkeys);
			if ($place>=$validkeys || BPT_keycmp($thesekeys[$place],$key)>0) {
			#$thesekeys[$place]>$key) {
				if ($place == 0)
					$nodekey = null;
				else
					$nodekey=$thesekeys[$place-1];
			} else {
				$nodekey = $key;
			}


			$node =& $node->getnode($nodekey);
		}

		return $node->getvalue($key, $loose);
	}

	/**
	 * @param $key target key
	 * @returns bool false if key does not exists, true otherwise
	 */
	function has_key(&$key, $loose=false) {
		if (@$this->getitem($key, $loose)!==false) {
			return true;
		} else {
			return false;
		}
	}
	

	/**
	 * sets an item in the tree with key $key and value $val
	 *
	 * @param string $key
	 * @param integer $val (internally stored as a 4byte long: keep it in mind!)
	 *
	 *
	 */

	function setitem($key, $val) {
		if (!is_numeric($val))
			trigger_error("Second parameter must be numeric", E_USER_ERROR);
		$curr_length = $this->length;
		$root =& $this->root;
		if (is_null($root)) trigger_error("not open", E_USER_ERROR);
		if (!is_string($key)) trigger_error("$key must be string", E_USER_ERROR);
		if (strlen($key)>$this->keylen) 
			trigger_error("$key is too long: MAX is {$this->keylen}", E_USER_ERROR);
		
		
		d( "STARTING FROM ROOT..." );

		$test1 = $this->set($key, $val, $this->root);
		if (!is_null($test1)) {
			d("SPLITTING ROOT");

			// getting new rightmost interior node
			list($leftmost, $node) = $test1;
			#print_r($test1);
			d("LEFTMOST [$leftmost]");

			// getting new non-leaf root
			list($newroot, $this->free) = $root->getfreenode($this->free);
			$newroot->flag = BPT_FLAG_ROOT;

			/*
			if ($root->flag == BPT_FLAG_LEAFANDROOT) {
				$root->flag = BPT_FLAG_LEAF;
			} else {
				$root->flag = BPT_FLAG_INTERIOR;
			}*/
			
			// zero-ing root flag (makes an interior or leaf node 
			// respectively from a normal root or a leaf-root)
			$root->flag &= ~BPT_FLAG_ROOT_BIT;

			$newroot->clear();
			$newroot->putfirstindex($root->position);
			$newroot->putnode($leftmost, $node);
			$this->root =& $newroot;
			$this->root_seek = $newroot->position;
			$newroot->store();
			$root->store();
			$this->reset_header();
                        d("root split.");
		} else {
			if ($this->length!=$curr_length) {
				// length changed: updating header
				$this->reset_header();
			}
		}
	}


	/**
	 * traverses subtree starting at $node, searching a place for $key
	 * and associates $val; split nodes if needed
	 *
	 * This function is not meant to be called outside the class, it is a 
	 * support method for {@link BPlusTree::setitem}
	 *
	 * @param string $key
	 * @param int	 $val value associated to $key
	 * @param object BPlusTree_Node starting node
	 *
	 * @returns array|null a pair (leftmost, newnode) where "leftmost" is  
	 * 			the leftmost key in newnode, and newnode is the split node;
	 *			returns null if no split took place
	 */

       function set($key, $val, &$node) {
       //{{{
		$keys =& $node->keys;
		$validkeys = $node->validkeys;
                if (($node->flag & BPT_FLAG_INTERIOR) == BPT_FLAG_INTERIOR) {
			d("NON LEAF: FIND DESCENDANT");
			// non-leaf: find descendant to insert
                        d($keys);
			$place = BPT_bisect($keys, $key, 0, $validkeys);
			
			if ($place >= $validkeys || BPT_keycmp($keys[$place],$key)>=0) {
				#$keys[$place]>=$key) {	
				// insert at previous node
				$index = $place;
			} else { 
				$index = $place +1 ;
			}

			if ($index == 0)
				$nodekey = null;
			else 
				$nodekey =$keys[$place-1];
				
			$nextnode =$node->getnode($nodekey);
			$test = $this->set($key, $val, $nextnode);
			// split ?
			if (!is_null($test)) {
				list($leftmost, $insertnode) = $test;
				
				// TRY
				$TRY = $node->putnode($leftmost, $insertnode);
				if ($TRY == NOROOMERROR) {

					d( "$key::SPLIT!" );
					// EXCEPT

					$insertindex = $insertnode->position;
		
					list($newnode, $this->free) = 
						$node->getfreenode(
							$this->free,
							array(&$this, 'update_freelist')
						);
					
					$newnode->flag = BPT_FLAG_INTERIOR;

					$ki = $node->keys_indices("dummy");

					#list($dummy, $firstindex) = $ki[0]; #each($ki);
					$firstindex = $ki->b[0];

					#$ki = array_slice($ki, 1);
					$ki->remove(0);
					#print_r($ki);
					// insert new pair
					#BPT_insort($ki, array($leftmost, $insertindex));
					$ki->insort($leftmost, $insertindex);

					$newleftmost = $this->divide_entries(
							$firstindex, 
							$node, 
							$newnode, 
							$ki
					);

					$node->store();
					$newnode->store();
					return array($newleftmost, &$newnode);

				} else {                                     

					d( "$key::NO SPLIT" );
					d($node->keys);
					$node->store();
					return null; // no split
				}
			}
		} else {
			// leaf
			d("FOUND LEAF:");
			d($keys);
			if (!in_array($key, $keys, true) 
					|| array_search($key, $keys, true) >= $validkeys) {
				$newlength = $this->length +1;
			} else {
				$newlength = $this->length;
			}

			d("[LEAF] TRYING TO PUT $key=>$val");
			if ($node->putvalue($key, $val)==NOROOMERROR) {
				d("GOT NOROOMERROR");
				
				$ki = $node->keys_indices("dummy");
				#BPT_insort($ki, array($key, $val));
				$ki->insort($key, $val);
				list($newnode, $this->free) = 
					$node->getfreenode(
						$this->free, 
						array(&$this, 'update_freelist')
					);
				d("CREATE NEW NEIGHBOUR");
				$newnode =& $node->newneighbour($newnode->position);
				$newnode->flag = BPT_FLAG_LEAF;
				$newleftmost = $this->divide_entries(0, $node, $newnode, $ki);
				$node->store();
				#print_r($node);
				#print_r($newnode);
				$newnode->store();
				$this->length = $newlength;
				return array($newleftmost, &$newnode);
			} else {
				d("STORING NODE [{$node->position}]") ;
				d($node->keys);

				$node->store();
				$this->length = $newlength;
				return null;
			}
		}
	}
	//}}}

	/**
	 *
	 * removes key from tree at node $node;
	 * triggers an error if $key does not exists
	 *
	 * not meant to be called outside the class, it is a support method
	 * for {@link BPlusTree::delitem}
	 *
	 * @param $key target key
	 * @param $node node from which start
	 *
	 * @returns array a pair(&$leftmost, $size): if leftmost changes it is a string with the new leftmost
	 * 		of $node otherwise returns array(null, $size)- caller will restructure node, if needed
	 * 		size is the new size of $node
	 *
	 */
	function remove($key, &$node, $NESTING=0) {
		$newnodekey = null;
		d("NESTING LEVEL $NESTING");
		d("($NESTING) current size = {$this->nodesize}");

                // first of all we check if it is non-leaf
		if (($node->flag & BPT_FLAG_INTERIOR) == BPT_FLAG_INTERIOR) {
			// non-leaf
			
			$keys =& $node->keys;
			$validkeys =$node->validkeys;
			$place = BPT_bisect($keys, $key, 0, $validkeys);

			if ($place>=$validkeys || BPT_keycmp($keys[$place],$key)>=0) {
				#$keys[$place]>=$key) {
				// delete occurs before $place
				// (remember that indices are [i_0,i_1,...,i_n]
				// where i_0 points to the node where all keys are < K_search
				// and i_1 points to the node where keys are k_1<=K_search<k_2)
				$index = $place;
			} else {
				// delete occurs in $place (k_i <= K_search < k_(i+1) )
				$index = $place + 1;
			}

			if ($index==0) {
				$nodekey = null;
			} else {
				$nodekey = $keys[$place-1];
			}

			// get child node
			$nextnode =& $node->getnode($nodekey);

			// RECURSION! remove from nextnode;
			// returns new leftmost if changed, otherwise null, 
			// and new size of the child node
			list($lm, $size) = $this->remove($key, $nextnode, $NESTING+1);

			// check now for size of nodesize: is it too small?
			// (less than half)
			$nodesize = $this->nodesize;
			$half = (int)($nodesize/2);

			# if($size==0) trigger_error("SIZE==0", E_USER_WARNING);

			if ($size < $half) {
				d("($NESTING) node too small ($size<$nodesize/2), redistribute children");

				// node is too small, need to redistribute
				// children

				if (is_null($nodekey) && $validkeys == 0) {
					#print_r($node);
					trigger_error(
						"invalid node, only one child",
						E_USER_ERROR
					);
				}

				
				if ($place >= $validkeys) {
					// final node in row, get previous
					$rightnode =& $nextnode;
					$rightkey  = $nodekey;
					if ($validkeys<=1) {
						$leftkey = null;
					} else {
						$leftkey = $keys[$place-2];
					}
					$leftnode =& $node->getnode($leftkey);
				} else {
					// non-final, get next
					$leftnode =& $nextnode;
					$leftkey = $nodekey;

					if ($index == 0) {
						$rightkey = $keys[0];
					} else {
						$rightkey = $keys[$place];
					}
					$rightnode = $node->getnode($rightkey);
				}
				// get all keys and indices
				$rightki = $rightnode->keys_indices($rightkey);
				$leftki = $leftnode->keys_indices($leftkey);
				
				#$ki = array_merge($leftki, $rightki);
				$leftki->append($rightki);
				$ki =& $leftki;
				
				#array_splice ($leftki, count($leftki), 0, $rightki);

				$lki = $ki->count;#count($ki);

				// merging?
				if (($lki>$nodesize) || (
					($leftnode->flag & BPT_FLAG_LEAF)!=BPT_FLAG_LEAF
					&&
					($lki>=$nodesize)
				)) {
					// redistribute
					#list($newleftkey, $firstindex) = $ki[0];
					$newleftkey = $ki->a[0];
					$firstindex = $ki->b[0];
					if (is_null($leftkey)) {
						$newleftkey = $lm;
					}
					if (($leftnode->flag&BPT_FLAG_LEAF)!=BPT_FLAG_LEAF) {
						// kill first pair
						#$ki = array_slice($ki, 1);
						$ki->remove(0);
					}
					$newrightkey = $this->divide_entries(
						$firstindex,
						$leftnode,
						$rightnode,
						$ki
					);

					// delete, reinsert right
					$node->delnode($rightkey);
					$node->putnode($newrightkey, $rightnode);

					// same for left if first changed
					if (!is_null($leftkey) && $leftkey!=$newleftkey) {
						$node->delnode($leftkey);
						$node->putnode($newleftkey, $leftnode);
					}
					$node->store();
					$leftnode->store();
					$rightnode->store();
				} else {
					d("($NESTING) node too small, need merge left<-right");
					// merge into left, free right
					d($leftnode->keys);
					d($leftnode->indices);
					d($rightnode->indices);
					#list($newleftkey, $firstindex) = $ki[0];
					$newleftkey = $ki->a[0];
					$firstindex = $ki->b[0];
					
					if (($leftnode->flag&BPT_FLAG_LEAF)!=BPT_FLAG_LEAF) {
						$leftnode->put_all_positions(
							$firstindex,
							$ki->slice(1)
							#array_slice($ki, 1)
						);
					} else {
						$leftnode->put_all_values($ki);
					}

					if ($rightnode->flag==BPT_FLAG_LEAF) {
						$this->free = $leftnode->delnext(
							$rightnode, $this->free
						);
					} else {
						$this->free = $rightnode->free($this->free);
					}
					if (!is_null($leftkey) && $newleftkey!=$leftkey) {
						d("$newleftkey!=$leftkey");
						$node->delnode($leftkey);
						$node->putnode($newleftkey, $leftnode);
					}
					$node->delnode($rightkey);
					$node->store();
					$leftnode->store();
                                        d('redist:');
					d($node->keys);
					d($leftnode->keys);

					$this->reset_header();
				}
				if (is_null($leftkey))
					$newnodekey = $lm;
			} else {
				// no restructuring,
				// update leftmost if needed
				if (is_null($nodekey)) {
					// we changed leftmost child,
					// we return a new leftmost key to update parent
					// ($lm is null if no update is needed)
					$newnodekey = $lm;
				} elseif(!is_null($lm)) {
					// child's leftmost has changed:
					// delete old reference
					$node->delnode($nodekey);
					// change reference with new key
					$node->putnode($lm, $nextnode);
				}
			} // end restructuring if

		} else {
			//leaf, base case: just delete.	
			if ($node->validkeys<1) {
				// only for empty root
				trigger_error("No such key $key", E_USER_ERROR);
			}
			$first=$node->keys[0];
			d($node->keys);
			$node->delvalue($key);
			d($node->keys);
			$rest = $node->keys[0];
			if ($first!=$rest) {
				$newnodekey = $rest;
			}
			$node->store();
			$this->length--;

			d("NEWNODEKEY: $newnodekey");
			d("VALIDKEYS: {$node->validkeys}");
		}
		d($node->keys);
		return array($newnodekey, $node->validkeys);
	}


	/**
	 *
	 * equally divides $entries ("array" of pairs (key,index) - 
	 * implemented with a pair object) between two nodes $node1 and $node2
	 *
	 * @param int $firstindex if interior node, leftmost index (pointer to less-than sub-tree) for $node1
	 * @param object $node1 BPlusTree_Node first destination node
	 * @param object $node2 BplusTree_Node second destination node
	 * @param object $entries {@link pairs} object
	 *
	 * @returns string leftmost key of $node1
	 */

	function divide_entries($firstindex, &$node1, &$node2, &$entries) {
//{{{
        	#$middle = (int)(count($entries)/2);
		$middle = ceil($entries->count/2);
		d("divide entries at $middle");

       		#$left = array_slice($entries, 0, $middle);
       		#$right = array_slice($entries, $middle);
		$left = $entries->slice(0, $middle);
		$right = $entries->slice($middle);


 		if (($node1->flag & BPT_FLAG_INTERIOR) == BPT_FLAG_INTERIOR) {
		       	d("DIVIDING INTERIOR\n");
	       		#list($leftmost, $midindex) = $right[0];
			$leftmost = $right->a[0];
			$midindex = $right->b[0];

			$node1->put_all_positions($firstindex, $left);
			#$node2->put_all_positions($midindex, array_slice($right, 1));
			$node2->put_all_positions($midindex, $right->slice(1));
			d($node1->keys);
			d($node2->keys);
			if (in_array(
				array_fill(0,$node1->size,''), 
				array($node1->keys,$node2->keys), true)
			) {
				trigger_error("splitting an empty node!", E_USER_ERROR);
			}
 			return $leftmost;
		} else {
		       	d("DIVIDING non-INTERIOR");
			$node1->put_all_values($left);
			$node2->put_all_values($right);
			d($node1->keys);
			d($node2->keys);
			// returns right leftmost
			#return $right[0][0];
			return $right->a[0];
		}
	}
// }}} 


	/**
	 * delete item $key
	 * @param string $key the key to delete
	 *
	 */

	function delitem($key) {
		$root = $this->root;
		$currentlength = $this->length;

		$this->remove($key, $root, $NESTING=0);

		if ($root->flag == BPT_FLAG_ROOT) {
			
			$validkeys = $root->validkeys;

			if ($validkeys <1) {
				
				
				if ($validkeys<0) {
					trigger_error(
						"invalid empty non-leaf root",
						E_USER_ERROR
					);
				}


				$this->root =& $root->getnode(null);
				$newroot =& $this->root;
				$this->root_seek = $newroot->position;
				$this->free = $root->free($this->free);
				$this->reset_header();
				/*
				if ($newroot->flag == BPT_FLAG_LEAF) {
					$newroot->flag = BPT_FLAG_LEAFANDROOT;
				} else {
					$newroot->flag = BPT_FLAG_ROOT;
				}
				*/
				$newroot->flag |= BPT_FLAG_ROOT_BIT;
				$newroot->store();
			} elseif ($this->length != $currentlength) {
				$this->reset_header();
			}
		} elseif($root->flag != BPT_FLAG_LEAFANDROOT) {
			trigger_error("invalid flag for root", E_USER_ERROR);
		} elseif ($this->length != $currentlength) {
			$this->reset_header();
		}
	}

	function _dump() {
			$free =& $this->root->getclone($this->free);
			for ($i=$this->headersize; 
				!feof($this->file); 
				fseek($this->file, $i), $i+=$free->storage) {
				$s = fread($this->file, $free->storage);
				$free->delinearize($s);
				#print_r($free);
			}
	}

	/**
	 * dumps contents of the tree to screen
	 */

	function dump() {
       		$this->root->dump() ;
		if ($this->free != BPT_NULLSEEK) {
			$free =& $this->root->getclone($this->free);
			$free =& $free->materialize();
			$free->dump();
		}
	}

 
}

class BPlusWalker {
	
	var $tree;
	var $keylower;
	var $includelower;
	var $keyupper;
	var $includeupper;


	function BPlusWalker(
			&$tree, 
			&$keylower, 
			$includelower=null, 
			$keyupper=null, 
			$includeupper=null){
		
		$this->tree =& $tree;
		$this->keylower = $keylower;
		$this->includelower = $includelower;
		$this->keyupper = $keyupper;
		$this->includeupper = $includeupper;
		if ($this->tree->get_root()==null) {
			$this->tree->open();
		}
		$node = $this->tree->get_root();
		while(BPT_FLAG_INTERIOR == ($node->flag & BPT_FLAG_INTERIOR)) {
			
			if (is_null($keylower)) {
				$nkey = null;
			} else {
				$keys = $node->get_keys();
				$n_keys = count($keys);
				$place = BPT_bisect($keys, $keylower);
				if ($place==0) {
					$nkey = null;
				} elseif ($place>$n_keys) {
					$nkey = $keys[$n_keys-1];
				} else {
					$nkey = $keys[$place-1];
				}
			}

			$node =& $node->getnode($nkey);
		}

		$this->startnode =& $node;
		$this->node =& $node;
		
		$this->node_index = null;
		$this->valid = 0;
		$this->first();
		$keylower = $this->keylower;
	}

	function first() {
		$this->node =& $this->startnode;
		$node =& $this->node;

		$keys =& $node->keys;
		$keylower = $this->keylower;
		$keyupper = $this->keyupper;
		$validkeys= $node->validkeys;
		$this->valid=0;
		if ($keylower==null) {
			$this->node_index = 0;
			$this->valid=1;
		} elseif (in_array($keylower, $keys, true) && $this->includelower) {
			$this->node_index = array_search($keylower, $keys, true);
			$index = $this->node_index;
			if ($index<$validkeys) {
				$this->valid = 1;
			}
		}
		if (!$this->valid) {
			$place = BPT_bisect($keys, $keylower, 0, $validkeys);
			if ($place < $validkeys || ($place==$validkeys && $this->includelower>1)) {
				if ($place > 0)
					$index = $place - 1;
				else	$index = $place;
					
				$this->node_index = $index;
				$testk = $keys[$index];
				/*
				if ($testk>$keylower ||
					($this->includelower && $testk==$keylower)) {
					$this->valid = true;
				} else {
					$this->valid = false;
				}
				*/
				$this->valid = BPT_keycmp($testk,$keylower)<0||#$testk>$keylower ||
					($this->includelower && ($this->includelower>1 || $testk==$keylower) );


				$this->keylower = $testk;

			} else {
				$next =& $node->nextneighbour();
				if (!is_null($next)) {
					$this->startnode =& $next;
					$this->first();
					return;
				} else {
					$this->valid = 0;
				}
			}
			if ($this->valid && !is_null($keyupper)) {
				$key = $this->current_key();
				$this->valid= (
				BPT_keycmp($key,$keyupper)<0 #$key<$keyupper
				||($this->includeupper && $key==$keyupper));
			}
		}
	}

	function current_key() {
		if ($this->valid) return $this->node->keys[$this->node_index];
		else trigger_error("WALKER: Not a valid index ({$this->node_index})");
	}

	function current_value() {
		if ($this->valid) return $this->node->indices[$this->node_index];
		else trigger_error("WALKER: Not a valid index ({$this->node_index})");
	}

	function current() {
		if ($this->valid) {
			return array(
				$this->node->keys[$this->node_index],
				$this->node->indices[$this->node_index]
			);
		} else {
		       	trigger_error("WALKER: Not a valid index ({$this->node_index})"); 
		}
	}

	function next() {
		$nextp = $this->node_index+1;
		$node =& $this->node;
		if ($nextp>=$node->validkeys) {
			$next =& $node->nextneighbour();
			if (is_null($next)) {
				$this->valid = 0;
				return;
			}
			$this->node =& $next;
			$node =& $next;
			$nextp = 0;
		}
		if($node->validkeys <= $nextp) {
			$this->valid = 0;
		} else {
			$testkey = $node->keys[$nextp];
			$keyupper = $this->keyupper;
			$this->valid =( is_null($keyupper) ||
				BPT_keycmp($testkey,$keyupper)<0||
				#$testkey < $keyupper ||
				($this->includeupper && $testkey == $keyupper) );
			if ($this->valid) $this->node_index = $nextp;
		}

		return $this->valid;

	}

}

class caching_BPT extends BPlusTree {

	var $cache = array();

	function getitem(&$key, $loose=false) {
		if (isset($this->cache[$key])) 
			return $this->cache[$key];
		else { 
			$this->cache[$key] = parent::getitem($key, $loose); 
			return $this->cache[$key];
		}
	}

	function resetcache() {
		$this->cache = array();
	}

	function nope() {
		trigger_error("operation not permitted in caching_BPT", E_USER_WARNING);
	}

	function setitem($key, $val) { $this->nope(); }

	function delitem($key) { $this->nope(); }
	
}

class SBPlusTree extends BPlusTree {
	
	var $maxstring; var $stringfile;
	
	function SBPlusTree($infile, $stringfile, 
				$maxstring = 256, 
				$pos=null, $nodesize=null, $keylen=null) {
        	parent::BPlusTree($infile, $pos, $nodesize, $keylen);
		$this->stringfile = $stringfile;
		$this->maxstring = $maxstring;
	}

	function startup() {
		fwrite($this->stringfile, 'BPTSTRINGS');
		return parent::startup();
	}

	function getstring($seek) {
		fseek($this->stringfile, $seek);
		$s = fread($this->stringfile, $this->maxstring);
		return rtrim($s);
	}

	function setstring($s, $key) {
		$seek = $this->has_key($key);
		if (!is_numeric($seek)) {
			fseek($this->stringfile, 0, SEEK_END);
			$seek = ftell($this->stringfile);
		} else {
			fseek($this->stringfile, $seek);
		}
		// nul-pad string
		if (strlen($s>$this->maxstring))
			$x = substr($s, 0, $this->maxstring);
		$x = str_pad($s, $this->maxstring, chr(0));
		fwrite($this->stringfile, $x);
		return $seek;
	}

	function getitem(&$key, $loose=false) {
		$seek = $this->has_key($key, $loose);
		return is_numeric($seek)? $this->getstring($seek) : false;
	}

	/**
	 * @param $key target key
	 * @returns int seek point if key exists, 0 otherwise
	 */
	function has_key(&$key, $loose=false) {
			return @parent::getitem($key, $loose);
	}

	function setitem($key, $val) {
		$seek = $this->setstring($val, $key);
		parent::setitem($key, $seek);
		return $seek;
	}

	function &walker(
		&$keylower,
		$includelower	=null,
		$keyupper	=null,
		$includeupper	=null
		) {
			$o = new SBPlusWalker($this, $keylower, $includelower, $keyupper, $includeupper);
			return $o;
	} 
}

class SBPlusWalker extends BPlusWalker {

	function current_value() {
		$id = parent::current_value();
		return $this->tree->getstring($id);
	}

}

class caching_SBPT extends SBPlusTree {

	var $cache = array();

	function caching_SBPT($infile, $stringfile, 
				$maxstring = 256, 
				$pos=null, $nodesize=null, $keylen=null) {

		parent::SBPlusTree($infile, $stringfile, 
				$maxstring, 
				$pos, $nodesize, $keylen);
	}

	function getitem(&$key, $loose=false) {
		if (isset($this->cache[$key]))
			return $this->cache[$key];
		else {
			$item = parent::getitem($key, $loose);
			$this->cache[$key] = $item;
			return $item;
		}
	}

	function resetcache() {
		$this->cache = array();
	}

	function nope() {
		trigger_error("operation not permitted in caching_BPT", E_USER_WARNING);
	}

	function setitem($key, $val) { $this->nope(); }

	function delitem($key) { $this->nope(); }
	
}

class BPlusUtils {

	function recopy_bplus($fromfile, $tofile, $class='BPlusTree') {
		$fromtree = new $class($fromfile);
		$fromtree->open;
		list($f, $p, $n, $k) = $fromtree->init_params();
		$totree	  = new $class($tofile, $p, $n, $k);
		$totree->startup();
		return BPlusUtils::recopy_tree($fromtree,$totree);
	}

	function recopy_tree($fromtree, $totree) {
		list($f, $p, $n, $k) = $totree->init_params();
		// .... 
	}

}


