<?php
/**
 * Unit tests for the entry file of the Segment Cache for WP Engine plugin
 *
 * @package segment-cache-for-wp-engine
 */

namespace SegmentCacheWPE\Shortcode;

use function SegmentCacheWPE\get_segment_name;

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
}
