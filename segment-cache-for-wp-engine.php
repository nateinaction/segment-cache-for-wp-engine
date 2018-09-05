<?php
/**
 * Plugin Name: Segment Cache for WP Engine
 * Plugin URI: http://wordpress.org/plugins/segment-cache-on-wp-engine/
 * Description: Implement Segmented Caching on WP Engine.
 * Version: 1.0.0
 * Author: Nate Gay
 * Author URI: https://nategay.me/
 * License: GPL3+
 *
 * @package segment-cache-for-wp-engine
 */

namespace SegmentCacheWPE;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require __DIR__ . '/vendor/autoload.php';

$segment_cache_wpe_header_name = null;
if ( isset( $_SERVER['HTTP_X_WPENGINE_SEGMENT'] ) ) {
	$segment_cache_wpe_header_name = $_SERVER['HTTP_X_WPENGINE_SEGMENT'];
}

new SendVaryHeader( $segment_cache_wpe_header_name );
new Shortcode( $segment_cache_wpe_header_name );
