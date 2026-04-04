<?php
/**
 * plugin_tag_entry
 *
 * The entry utilities of the plugin tag.
 */
class plugin_tag_entry {

	/**
	 * Where tags are saved.
	 * This variable must always exists.
	 *
	 * @var array
	 */
	var $tags = array();

	/**
	 * This is the cache of entry by id.
	 *
	 * @var array
	 */
	var $_cache = array();

	/**
	 * Merge the tags or delete the current tags?
	 *
	 * @var boolean
	 */
	var $merge = false;

	/**
	 * The tagdb instance.
	 * Used to count entries
	 * that have a certain tag.
	 *
	 * @var object
	 */
	var $tagdb = null;

	/**
	 * The constructor.
	 * It doesn't do so much.
	 *
	 * @param object $tagdb: The tagdb instance
	 */
	function __construct(&$tagdb) {
		global $smarty;

		// To list tags in the template
		$smarty->assign('tags', $this->tags);
		$smarty->registerPlugin('modifier', 'tagplugin_list', array(
			&$this,
			'smarty_modifier'
		));

		// Save the tagdb instance
		$this->tagdb = &$tagdb;

		// Do the tag list
		add_filter('the_content', array(
			&$this,
			'tag_list'
		), 0);

		// The automatic bottom list
		if (PLUGIN_TAG_BL) {
			add_filter('the_content', array(
				&$this,
				'tag_bottomlist'
			), 50);
		}
	}

	/**
	 * do_bbcode
	 *
	 * This function is used as callback from the BBCode class
	 * to save tags from an entry.
	 *
	 * @param string $action: the action (see the BBCode class manual)
	 * @param array $attributes: ??? (see the BBCode class manual)
	 * @param string $content: what is content of [tag][/tag]
	 * @param array $params: ??? (see the BBCode class manual)
	 * @param ??? $node_obhect: ??? (see the BBCode class manual)
	 * @returns void string
	 */
	function do_bbcode($action, $attributes, $content, $params, $node_object) {
		if ($action == 'validate') {
			return true;
		}

		if (empty($content)) {
			return '';
		}

		$t = explode(',', $content);
		$t = array_map('trim', $t);

		if ($this->merge) {
			$t = array_merge((array) $this->tags, $t);
		}

		$t = array_unique($t);
		$this->tags = $t;

		return '';
	}

	/**
	 * load_bbcode
	 *
	 * This function create a new instance of StringParser_BBCode
	 * and add to it the 'tag' tag.
	 *
	 * returns object: the StringParser_BBCode instance
	 */
	function load_bbcode() {
		$tag_bbcode = new StringParser_BBCode();
		$tag_bbcode->addCode('tag', 'callback_replace', array(
			&$this,
			'do_bbcode'
		), array(
			'usecontent_param' => array(
				'default'
			)
		), 'inline', array(
			'listitem',
			'block',
			'inline'
		), array());
		$tag_bbcode->setCodeFlag('tag', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);
		return $tag_bbcode;
	}

	/**
	 * tag_list
	 *
	 * It makes the list of tag from an entry.
	 *
	 * @param string $content: the content of the entry
	 * @returns string: $content without tags
	 */
	function tag_list($content) {
		# Clean old tags
		$this->tags = array();
		# Enable tag merge
		$this->merge = true;
		# Load the tag parser and parse them
		$tag_bbcode = $this->load_bbcode();
		if (false !== $post = $tag_bbcode->parse($content)) {
			$content = $post;
		}
		# Disable tag merge
		$this->merge = false;
		# Return the modified content
		return $content;
	}

	/**
	 * smarty_modifier
	 *
	 * This is the modifier that is used to auto-list tag (with link) in
	 * the templates.
	 *
	 * @param array $array: the tags of the post ({$tags} in smarty)
	 * @param string $glue: how to join tags [default: , ]
	 * @param string $default: if there aren't tags...
	 * @returns: The tag list or $default
	 */
	function smarty_modifier($array, $glue = ', ', $default = 'No Tag') {
		// If there aren't tags, let's return $default
		if (!is_array($array) || !count($array)) {
			return $default;
		}

		$links = array();

		// Load lang
		$plang = lang_load('plugin:tag');
		$plang = $plang ['plugin'] ['tag'];

		foreach ($array as $tag) {
			$tagLabel = wp_specialchars($tag, true);
			$entries = $this->tagdb->taggedEntries($tag);
			$count = count($entries);
			$titleadd = ($count === 1) ? $plang ['oneentry'] : $count . $plang ['entries'];
			$titleadd = '(' . $titleadd . ')';

			if ($count > 0) {
				$link = apply_filters('tag_link', $tag);
				$links [] = '
								<a href="' . $link . '" title="' . $tagLabel . ' ' . $titleadd . '">' . $tagLabel . '</a>';
			} else {
				$links [] = $tagLabel;
			}
		}

		return implode($glue, $links);
	}

	/**
	 * tag_bottomlist
	 *
	 * This function adds the tag list at the entry bottom.
	 *
	 * @param $content: The original content of the entry
	 * @return string: The modified $content
	 */
	function tag_bottomlist($content) {
		# If there aren't tags
		if (empty($this->tags)) {
			return $content;
		}

		# Load lang
		$taglang = lang_load('plugin:tag');
		$taglang = $taglang ['plugin'] ['tag'];

		// Use the fontawesome bibiothek if function exist
		if (function_exists('plugin_webfonts_head')) {
			$taglist = '<i class="fa-solid fa-tags" aria-hidden="true"></i>';
		} else {
			$taglist = $taglang ['tag_s'];
		}

		$taglist .= $this->smarty_modifier($this->tags);
		$content .= '
							<div class="plugin_tag_list">' . $taglist . '
							</div>';
		return $content;
	}

	/**
	 * This function is similar to tag_list but it loads the entry
	 * by his ID and then it returns the tags.
	 *
	 * @param string $id: The entry ID
	 * @param boolean $force: Must I ignore cache?
	 * @return array: The tags
	 */
	function entryTags($id, $force = false) {
		if (!$force && isset($this->_cache [$id])) {
			return $this->_cache [$id];
		}

		$entry = entry_parse($id);
		if (!isset($entry ['content'])) {
			return array();
		}

		$before = $this->tags;
		$this->tag_list($entry ['content']);
		$this->_cache [$id] = $this->tags;
		$tags = $this->tags;
		$this->tags = $before;

		return $tags;
	}

}
?>
