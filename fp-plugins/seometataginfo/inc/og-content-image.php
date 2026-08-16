<?php
/**
 * SEO Meta Tag Info compatibility facade for FlatPress content-media discovery.
 *
 * Generic image discovery lives in core.contentmedia.php so sitemap generation
 * and SEO metadata use exactly the same original-image, gallery and ReadMore
 * semantics.
 */

if (!function_exists('content_media_empty_image_meta')) {
	$coreMediaFile = defined('INCLUDES_DIR') ? INCLUDES_DIR . 'core.contentmedia.php' : dirname(__DIR__, 3) . '/fp-includes/core/core.contentmedia.php';
	require_once $coreMediaFile;
}

function seometataginfo_content_empty_image_meta() {
	return content_media_empty_image_meta();
}

function seometataginfo_content_normalize_image_alt($value) {
	return content_media_normalize_image_alt($value);
}

function seometataginfo_content_unset_global($key) {
	content_media_unset_global($key);
}

function seometataginfo_content_probe_media_callback($tag, $action, $attributes) {
	return content_media_probe_media_callback($tag, $action, $attributes);
}

function seometataginfo_content_probe_img($action, $attributes, $content, $params, $node_object) {
	return content_media_probe_img($action, $attributes, $content, $params, $node_object);
}

function seometataginfo_content_probe_photoswipeimage($action, $attributes, $content, $params, $node_object) {
	return content_media_probe_photoswipeimage($action, $attributes, $content, $params, $node_object);
}

function seometataginfo_content_probe_gallery($action, $attributes, $content, $params, $node_object) {
	return content_media_probe_gallery($action, $attributes, $content, $params, $node_object);
}

function seometataginfo_content_probe_photoswipegallery($action, $attributes, $content, $params, $node_object) {
	return content_media_probe_photoswipegallery($action, $attributes, $content, $params, $node_object);
}

function seometataginfo_content_probe_replace_code($parser, $tag, $callback) {
	return content_media_probe_replace_code($parser, $tag, $callback);
}

function seometataginfo_content_probe_media($content) {
	return content_media_probe_media($content);
}

function seometataginfo_content_remote_image_meta($url) {
	return content_media_remote_image_meta($url);
}

function seometataginfo_content_path_is_within($path, $root) {
	return content_media_path_is_within($path, $root);
}

function seometataginfo_content_normalize_local_image_path($source) {
	return content_media_normalize_local_image_path($source);
}

function seometataginfo_content_local_image_meta($source, $baseUrl) {
	return content_media_local_image_meta($source, $baseUrl);
}

function seometataginfo_content_image_meta($source, $baseUrl) {
	return content_media_image_meta($source, $baseUrl);
}

function seometataginfo_content_gallery_meta($source, $baseUrl) {
	return content_media_gallery_meta($source, $baseUrl);
}

function seometataginfo_content_resolve_token($token, $baseUrl) {
	return content_media_resolve_token($token, $baseUrl);
}

function seometataginfo_find_first_content_image_meta($content, $baseUrl, $applyReadMore) {
	return content_media_find_first_image_meta($content, $baseUrl, $applyReadMore);
}

function seometataginfo_get_current_static_content() {
	return content_media_get_current_static_content();
}

function seometataginfo_get_stream_query_params($query) {
	return content_media_get_stream_query_params($query);
}

function seometataginfo_get_stream_content_image_meta($baseUrl) {
	return content_media_get_stream_image_meta($baseUrl);
}

function seometataginfo_get_requested_content_og_image_info($baseUrl) {
	$result = array(
		'requested' => false,
		'image_info' => array()
	);

	$parameter = seometataginfo_get_query_parameter(SEOMETA_OGIMAGE_SOURCE_QUERY_VAR);
	if (empty($parameter ['present'])) {
		return $result;
	}

	$result ['requested'] = true;
	if (empty($parameter ['valid'])) {
		return $result;
	}

	$source = seometataginfo_content_normalize_local_image_path($parameter ['value']);
	if ($source === '') {
		return $result;
	}

	$imageInfo = seometataginfo_content_local_image_meta($source, $baseUrl);
	if (empty($imageInfo ['absolute_path']) || empty($imageInfo ['mime'])) {
		return $result;
	}

	$result ['image_info'] = $imageInfo;
	return $result;
}

function seometataginfo_get_content_og_image_meta($baseUrl) {
	$empty = seometataginfo_content_empty_image_meta();

	if (!function_exists('plugin_bbcode_init')) {
		return $empty;
	}

	if (function_exists('is_static') && is_static()) {
		$content = seometataginfo_get_current_static_content();
		return seometataginfo_find_first_content_image_meta($content, $baseUrl, false);
	}

	if (function_exists('is_single') && is_single()) {
		if (function_exists('seometataginfo_get_current_single_entry_data')) {
			$data = seometataginfo_get_current_single_entry_data();
			if (is_array($data) && !empty($data ['entry']) && is_array($data ['entry']) && isset($data ['entry'] ['content'])) {
				return seometataginfo_find_first_content_image_meta($data ['entry'] ['content'], $baseUrl, false);
			}
		}
		return $empty;
	}

	// FlatPress search uses its own result collection rather than the ordinary
	// FPDB stream window. Avoid publishing an unrelated image in that context.
	if (function_exists('is_search') && is_search()) {
		return $empty;
	}

	return seometataginfo_get_stream_content_image_meta($baseUrl);
}
?>
