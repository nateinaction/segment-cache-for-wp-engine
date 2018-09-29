<?php
/**
 * Unit tests for the entry file of the Segment Cache for WP Engine plugin
 *
 * @package segment-cache-for-wp-engine
 */

namespace SegmentCacheWPE;

/**
 * Unit tests for SetSegment shortcode class
 */
class SegmentCacheForWPEngine_Test extends \WP_UnitTestCase {
	/**
	 * Test get_segment_name
	 */
	public function test_get_segment_name() {
		// Test without server var set.
		$segment_name = get_segment_name();
		$this->assertNull( $segment_name );

		// Test with server var set.
		$expect                             = 'Hello, world!';
		$_SERVER['HTTP_X_WPENGINE_SEGMENT'] = $expect;
		$segment_name                       = get_segment_name();
		$this->assertEquals( $expect, $segment_name );
	}

	/**
	 * Verify shortcodes are added
	 */
	public function test_adding_shortcodes() {
		$segment_cache_display_shortcode = shortcode_exists( 'segment-cache-display' );
		$segment_cache_set_shortcode     = shortcode_exists( 'segment-cache-set' );
		$this->assertTrue( $segment_cache_display_shortcode );
		$this->assertTrue( $segment_cache_set_shortcode );
	}

	/**
	 * Since $segment_name is not set when bootstrap.php includes segment-cache-for-wp-engine.php,
	 * verify action is not added
	 */
	public function test_adding_action() {
		global $wp_filter;
		$action_exists = array_key_exists( 'send_headers', $wp_filter );
		$this->assertFalse( $action_exists );
	}
}
