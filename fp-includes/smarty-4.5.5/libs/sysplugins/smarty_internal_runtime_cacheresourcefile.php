<?php
/**
 * Smarty cache resource file clear method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */

/**
 * Smarty Internal Runtime Cache Resource File Class
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 */
class Smarty_Internal_Runtime_CacheResourceFile
{
    /**
     * Empty cache for a specific template
     *
     * @param Smarty  $smarty
     * @param string  $resource_name template name
     * @param string  $cache_id      cache id
     * @param string  $compile_id    compile id
     * @param integer $exp_time      expiration time (number of seconds, not timestamp)
     *
     * @return integer number of cache files deleted
     */
    public function clear(Smarty $smarty, $resource_name, $cache_id, $compile_id, $exp_time)
    {
        $_cache_id = $cache_id !== '' && $cache_id !== null ? preg_replace('![^\w\|]+!', '_', $cache_id) : null;
        $_compile_id = $compile_id !== '' && $compile_id !== null ? preg_replace('![^\w]+!', '_', $compile_id) : null;
        $_dir_sep = $smarty->use_sub_dirs ? '/' : '^';
        $_compile_id_offset = $smarty->use_sub_dirs ? 3 : 0;
        $_dir = $smarty->getCacheDir();
        if ($_dir === '/') { //We should never want to delete this!
            return 0;
        }
        $_dir_length = strlen($_dir);
        if (isset($_cache_id)) {
            $_cache_id_parts = explode('|', $_cache_id);
            $_cache_id_parts_count = count($_cache_id_parts);
            if ($smarty->use_sub_dirs) {
                foreach ($_cache_id_parts as $id_part) {
                    $_dir .= $id_part . '/';
                }
            }
        }
        if ($resource_name === '' || $resource_name === null) {
            return 0;
        }
        $_save_stat = $smarty->caching;
        $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
        $tpl = new $smarty->template_class($resource_name, $smarty);
        $smarty->caching = $_save_stat;
        // remove from template cache
        $tpl->source;
        if ($tpl->source->exists) {
            $_resourcename_parts = basename(str_replace('^', '/', $tpl->cached->filepath));
        } else {
            return 0;
        }
        $_count = 0;
        $_time = time();
        if (file_exists($_dir)) {
            $_cacheDirs = new RecursiveDirectoryIterator($_dir);
            $_cache = new RecursiveIteratorIterator($_cacheDirs, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($_cache as $_file) {
                if (substr(basename($_file->getPathname()), 0, 1) === '.') {
                    continue;
                }
                $_filepath = (string)$_file;
                // directory ?
                if ($_file->isDir()) {
                    if (!$_cache->isDot()) {
                        // delete folder if empty
                        @rmdir($_file->getPathname());
                    }
                } else {
                    // delete only php files
                    if (substr($_filepath, -4) !== '.php') {
                        continue;
                    }
                    $_parts = explode($_dir_sep, str_replace('\\', '/', substr($_filepath, $_dir_length)));
                    $_parts_count = count($_parts);
                    // check name
                    if ($_parts[$_parts_count - 1] !== $_resourcename_parts) {
                        continue;
                    }
                    // check compile id
                    if (isset($_compile_id) && (!isset($_parts[ $_parts_count - 2 - $_compile_id_offset ]) || $_parts[ $_parts_count - 2 - $_compile_id_offset ] !== $_compile_id)) {
                        continue;
                    }
                    // check cache id
                    if (isset($_cache_id)) {
                        // count of cache id parts
                        $_parts_count = (isset($_compile_id)) ? $_parts_count - 2 - $_compile_id_offset :
                            $_parts_count - 1 - $_compile_id_offset;
                        if ($_parts_count < $_cache_id_parts_count) {
                            continue;
                        }
                        for ($i = 0; $i < $_cache_id_parts_count; $i++) {
                            if ($_parts[ $i ] !== $_cache_id_parts[ $i ]) {
                                continue 2;
                            }
                        }
                    }
                    if (is_file($_filepath)) {
                        // expired ?
                        if ($exp_time < 0) {
                            preg_match('#\'cache_lifetime\' =>\s*(\d*)#', file_get_contents($_filepath), $match);
                            if (isset($match[1]) && $_time < (filemtime($_filepath) + (int)$match[1])) {
                                continue;
                            }
                        } else {
                            if ($_time - filemtime($_filepath) < $exp_time) {
                                continue;
                            }
                        }
                        $_count += @unlink($_filepath) ? 1 : 0;
                        if (function_exists('opcache_invalidate')
                            && (!function_exists('ini_get') || strlen(ini_get("opcache.restrict_api")) < 1)
                        ) {
                            opcache_invalidate($_filepath, true);
                        } elseif (function_exists('apc_delete_file')) {
                            apc_delete_file($_filepath);
                        }
                    }
                }
            }
        }
        return $_count;
    }
}
