<?php
/**
 * Plugin Name: Segment Cache for WP Engine
 * Plugin URI: http://wordpress.org/plugins/segment-cache-on-wp-engine/
 * Description: Implement Segmented Caching on WP Engine.
 * Version: 1.0.11
 * Author: Nate Gay
 * Author URI: https://nategay.me/
 * License: GPL3+
 *
 * @package segment-cache-for-wp-engine
 */

namespace SegmentCacheWPE;

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

require __DIR__ . '/vendor/autoload.php';

/**
 * Get name of the cache segment
 *
 * @return null|string Value of 'HTTP_X_WPENGINE_SEGMENT' server var
 */
function get_segment_name() {
	if ( isset( $_SERVER['HTTP_X_WPENGINE_SEGMENT'] ) ) {
		return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WPENGINE_SEGMENT'] ) );
	}
}
$segment_name = get_segment_name();

Send_Vary_Header::add_action( $segment_name );
Shortcode\Display_Segment::add_shortcode( $segment_name );
Shortcode\Set_Segment::add_shortcode();
