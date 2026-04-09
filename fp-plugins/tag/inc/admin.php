<?php
/**
 * This class manages all actions in the Admin Panel
 * that are useful for the tag plugin.
 */
class plugin_tag_admin {

	# See plugin_tag
	var $use_rewrite = false;

	# The tag database, null for now
	var $tagdb = null;

	# The tag's entry utilites, null for now
	var $entry = null;

	/**
	 * This is the content before that tags are stripped
	 * by the simple form functions.
	 *
	 * @var string
	 */
	var $simplecontent = '';

	var $draft = false;

	// more params is declared here
	var $simplebody = null;

	/**
	 * plugin_tag_admin
	 *
	 * It manages hooks.
	 *
	 * @param object $tagdb The tag database object
	 * @param object $entry The tag entry object
	 */
	function __construct($tagdb, $entry) {
		$this->tagdb = $tagdb;
		$this->entry = $entry;
		add_filter('publish_post', array(
			$this,
			'entry_save'
		), 5, 2);
		add_filter('delete_post', array(
			$this,
			'entry_delete'
		));
		add_filter('wp_footer', array(
			$this,
			'purge_cache'
		));

		if (!defined('MOD_ADMIN_PANEL') && version_compare(SYSTEM_VER, '0.1010', '>=') == 1) {
			return;
		}

		//add_filter('simple_edit_form', array(
		add_filter('simple_tag_form', array(
			$this,
			'simple'
		));
		add_filter('admin_entry_write_onsave', array(
			$this,
			'simpleadd'
		));
		add_filter('admin_entry_write_onsavecontinue', array(
			$this,
			'simpleadd'
		));
		add_filter('admin_entry_write_main', array(
			$this,
			'simpleremove'
		));
		add_filter('admin_entry_write_onsubmit', array(
			$this,
			'simpleremove'
		));
		add_action('wp_head', array(
			$this,
			'simplestyle'
		), 5);
		add_action('init', array(
			$this,
			'simpleajax'
		));
	}

	/**
	 * Deletes all related-entry cache files without scanning unrelated cache files.
	 *
	 * @return void
	 */
	function invalidateRelatedCache() {
		$files = glob(CACHE_DIR . 'tag-related-*.tmp');
		if (!is_array($files)) {
			return;
		}

		foreach ($files as $file) {
			if (is_file($file)) {
				@unlink($file);
			}
		}
	}

	/**
	 * entry_save
	 *
	 * Function called on entry_save hook.
	 * It updates the tags database
	 *
	 * @param string $id The entry id
	 * @param array $array The entry array
	 * @return bool Always true
	 */
	function entry_save($id, $array) {
		$toremove = array();

		// Get the tags of the entry
		$this->entry->tag_list($array ['content']);
		$tags = $this->entry->tags;

		// If the entry already exists...
		if (entry_exists($id)) {
			// Get old tags
			$entry = entry_parse($id);
			$this->entry->tag_list($entry ['content']);
			$old_tags = $this->entry->tags;
			// Get tags to remove
			$toremove = array_values(array_diff($old_tags, $tags));
			// Don't do anything with tags that there were already
			$tags = array_values(array_diff($tags, $old_tags));
		}

		// Add tags
		for($i = 0; $i < count($tags); $i++) {
			$file = $this->tagdb->tagfile($tags [$i]);
			$this->tagdb->open_file($file);
			$this->tagdb->files [$file] [$tags [$i]] [] = $id;
		}

		// Remove old tags
		for($i = 0; $i < count($toremove); $i++) {
			$file = $this->tagdb->tagfile($toremove [$i]);
			$f = $this->tagdb->open_file($file);

			if (!isset($f [$toremove [$i]])) {
				continue;
			}

			$k = array_search($id, $f [$toremove [$i]], true);
			if ($k !== false) {
				unset($f [$toremove [$i]] [$k]);
			}

			// If tag hasn't entries, remove it
			$f [$toremove [$i]] = array_values($f [$toremove [$i]]);
			if (!count($f [$toremove [$i]])) {
				unset($f [$toremove [$i]]);
			}

			$this->tagdb->files [$file] = $f;
		}

		// CRITICAL!! It saves the work
		$this->tagdb->save_all();

		// If we use rewrite, we have to rebuild cache.
		if ($this->use_rewrite) {
			$this->tagdb->rewriteCache(true);
		}

		// We don't make immediately the cache of widgets:
		// maybe the tag widget is not used anymore.
		if (file_exists(CACHE_DIR . 'tag-widget.tmp')) {
			@unlink(CACHE_DIR . 'tag-widget.tmp');
		}
		$this->invalidateRelatedCache();

		// Clean the cache of ajax
		if (file_exists(CACHE_DIR . 'tag-ajax.tmp')) {
			@unlink(CACHE_DIR . 'tag-ajax.tmp');
		}

		return true;
	}

	/**
	 * entry_delete
	 *
	 * This function is called by hook entry_delete.
	 * It updates the tag database
	 *
	 * @param string $id The id of the entry that is being deleted
	 * @return bool Always true
	 */
	function entry_delete($id) {
		// D'oh! We need to find in the database because entry doesn't exist anymore!
		// So ugly function, a linear parse of all tags! Oh my...
		$lister = new tag_lister();
		$list = $lister->_list;
		if (!count($list)) {
			// There is no tag, let's stop here
			return true;
		}

		// Very similar to entry_save
		foreach ($list as $file) {
			$f = $this->tagdb->open_file($file);
			if (!count($f)) {
				continue;
			}
			foreach ($f as $tag => $entries) {
				$k = array_keys($entries, $id);
				if (@isset($entries [$k [0]])) {
					unset($this->tagdb->files [$file] [$tag] [$k [0]]);
				}
				if (count($this->tagdb->files [$file] [$tag]) == 0) {
					unset($this->tagdb->files [$file] [$tag]);
				}
			}
		}

		$this->tagdb->save_all();

		if ($this->use_rewrite) {
			$this->tagdb->rewriteCache(true);
		}

		// See at lines 127-128
		if (file_exists(CACHE_DIR . 'tag-widget.tmp')) {
			@unlink(CACHE_DIR . 'tag-widget.tmp');
		}
		$this->invalidateRelatedCache();

		return true;
	}

	/**
	 * purge_cache
	 *
	 * This function is called on hook wp_footer.
	 * It remake the cache of the tags when you delete
	 * the cache of templates.
	 */
	function purge_cache() {
		if (PLUGIN_TAG_NOCACHE) {
			return false;
		}

		if (empty($_GET ['do'])) {
			return false;
		}

		// Just if we are in mantain panel
		if (ADMIN_PANEL == 'maintain' && $_GET ['do'] == 'purgetplcache') {
			fs_delete_recursive(PLUGIN_TAG_DIR);
			if (!is_dir(PLUGIN_TAG_DIR)) {
				@fs_mkdir(PLUGIN_TAG_DIR);
			}
			// Rebuild cache
			$this->tagdb->makeCache($this->entry);
			return true;
		}

		return false;
	}

	/**
	 * This function is the callback for simple_edit_form hook.
	 */
	function simple() {
		global $lang, $smarty;
		if (!isset($lang ['admin'] ['plugin'] ['tag'])) {
			$lang = lang_load('plugin:tag');
		}

		$post = $smarty->getTemplateVars('post');
		if (!is_array($post)) {
			$post = array();
		}

		$content = '';
		if (!empty($this->simplebody) && is_string($this->simplebody)) {
			$content = $this->simplebody;
		} elseif (isset($post ['content']) && is_string($post ['content'])) {
			$content = $post ['content'];
		}

		$tags = array();
		if ($content !== '') {
			$this->entry->tag_list($content);
			$tags = $this->entry->tags;
		}
		if (!empty($_POST ['taginput'])) {
			$tags = array_merge((array) $tags, explode(',', (string) $_POST ['taginput']));
		}
		$tags = array_map('trim', $tags);
		$tags = array_values(array_unique(array_filter($tags, 'strlen')));
		$tagsimple = implode(', ', $tags);

		$tagPluginUrl = plugin_geturl('tag');
		$tagScriptUrl = function_exists('utils_asset_ver')
			? utils_asset_ver($tagPluginUrl . 'res/tag.js', SYSTEM_VER)
			: $tagPluginUrl . 'res/tag.js';

		$smarty->assign('taglang', $lang ['admin'] ['plugin'] ['tag']);
		$smarty->assign('tags_simple', $tagsimple);
		$smarty->assign('tag_remove_text', (string) $lang ['admin'] ['plugin'] ['tag'] ['remove']);
		$smarty->assign('tag_ajax_url', (string) (BLOG_BASEURL . 'admin.php?ajaxtag=list'));
		$smarty->assign('tag_script_url', $tagScriptUrl);
		$smarty->display('plugin:tag/tagsimple');

		return true;
	}

	/**
	 * This function prints the stylesheet of the template
	 */
	function simplestyle() {
		if (ADMIN_PANEL == 'entry' && ADMIN_PANEL_ACTION == 'write') {
			$h = plugin_geturl('tag') . 'res/admin.css';
			if (function_exists('utils_asset_ver')) {
				$h = utils_asset_ver($h, SYSTEM_VER);
			}
			echo '<link rel="stylesheet" type="text/css" href="' . $h . "\">\n";
		}
	}

	/**
	 * This function reads the tags to the textarea.
	 */
	function simpleadd() {
		if (!isset($_POST ['taginput'], $_POST ['content'])) {
			return;
		}

		$tags = (string) $_POST ['taginput'];
		if (substr($tags, -1) == ',') {
			$tags = substr($tags, 0, -1);
		}
		$tags = explode(',', $tags);
		$tags = array_filter(array_map('trim', $tags), 'strlen');

		$cont = (string) $_POST ['content'];
		$cont = $this->entry->tag_list($cont);
		$tags = array_merge($tags, $this->entry->tags);
		$tags = array_values(array_unique(array_filter(array_map('trim', $tags), 'strlen')));

		$cont = trim($cont);
		if (count($tags) > 0) {
			$cont .= "\n[tag]" . implode(', ', $tags) . "[/tag]";
		}

		$_POST ['content'] = $cont;
	}

	/**
	 * This function removes [tag] from the content.
	 *
	 * @return void
	 */
	function simpleremove() {
		global $smarty;

		$post = $smarty->getTemplateVars('post');
		if (!is_array($post) || !isset($post ['content']) || !is_string($post ['content']) || $post ['content'] === '') {
			return;
		}

		$this->simplebody = $post ['content'];
		$post ['content'] = preg_replace('/\[tag\](.*?)\[\/tag\]/is', '', $post ['content']);
		$smarty->assign('post', $post);
	}

	/**
	 * This function handles the ajax function of the plugin.
	 */
	function simpleajax() {
		if (!isset($_GET ['ajaxtag']) || $_GET ['ajaxtag'] != 'list') {
			return;
		}

		if (empty($_GET ['tag'])) {
			die('');
		}

		$f = CACHE_DIR . 'tag-ajax.tmp';
		$cache = array();
		$tags = array();
		$tagsLower = array();
		if (is_file($f) && (time() - filemtime($f)) < 3600) {
			$cache = system_load_php_array($f, 'cache');
			if (isset($cache ['tags']) && is_array($cache ['tags'])) {
				$tags = $cache ['tags'];
			}
			if (isset($cache ['tags_lc']) && is_array($cache ['tags_lc'])) {
				$tagsLower = $cache ['tags_lc'];
			}
		}
		if (count($tags) === 0) {
			$lister = new tag_lister();
			$tags = array_keys($lister->makeTagList());
			natcasesort($tags);
			$tags = array_values($tags);
		}
		if (count($tagsLower) !== count($tags)) {
			$tagsLower = array_map('strtolower', $tags);
		}
		system_save($f, array(
			'cache' => array(
				'tags' => $tags,
				'tags_lc' => $tagsLower,
			)
		));

		$suggs = array();
		$tag = strtolower(trim((string) $_GET ['tag']));
		$tagLength = strlen($tag);
		foreach ($tagsLower as $key => $val) {
			if ($tag === substr($val, 0, $tagLength)) {
				$tmp = wp_specialchars($tags [$key]);
				$suggs [] = '<b>' . substr($tmp, 0, $tagLength) . '</b>' . substr($tmp, $tagLength);
				if (count($suggs) >= 10) {
					break;
				}
			}
		}

		$sugghtml = '';
		if (count($suggs) > 0) {
			$sugghtml = '<ul>';
			foreach ($suggs as $sugg) {
				$sugghtml .= '<li>' . $sugg . "</li>
";
			}
			$sugghtml .= '</ul>';
		}

		die($sugghtml);
	}

}
?>
