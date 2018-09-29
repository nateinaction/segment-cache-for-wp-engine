<?php
/**
 * Plugin Name: Segment Cache for WP Engine
 * Plugin URI: http://wordpress.org/plugins/segment-cache-on-wp-engine/
 * Description: Implement Segmented Caching on WP Engine.
 * Version: 1.0.4
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

/**
 * Get name of the cache segment
 *
 * @return null|string Value of 'HTTP_X_WPENGINE_SEGMENT' server var
 */
function get_segment_name() {
	$segment_name = null;
	if ( isset( $_SERVER['HTTP_X_WPENGINE_SEGMENT'] ) ) {
		$segment_name = $_SERVER['HTTP_X_WPENGINE_SEGMENT'];
	}
	return $segment_name;
}
$segment_name = get_segment_name();

Send_Vary_Header::add_action( $segment_name );
Shortcode\Display_Segment::add_shortcode( $segment_name );
Shortcode\Set_Segment::add_shortcode();
