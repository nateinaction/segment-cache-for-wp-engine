<?php
/**
 * Unit tests for the SendVaryHeader class of the Segment Cache for WP Engine plugin
 *
 * @package segment-cache-for-wp-engine
 */

namespace SegmentCacheWPE\Shortcode;

use SegmentCacheWPE\Send_Vary_Header;

/**
 * Unit tests for SetSegment shortcode class
 */
class SendVaryHeader_Test extends \WP_UnitTestCase {
	/**
	 * Ensure constructor logic agrees with whether or not segment name is provided
	 */
	public function test_constructor_logic() {
		// Test without segment_name.
		$send_vary_header = new Send_Vary_Header();
		$hook_priority    = has_action( 'send_headers', array( $send_vary_header, 'add_action' ) );
		$this->assertFalse( $hook_priority );

		// Test with segment_name.
		$send_vary_header = new Send_Vary_Header( 'test' );
		$hook_priority    = has_action( 'send_headers', array( $send_vary_header, 'set_vary_header' ) );
		$this->assertInternalType( 'int', $hook_priority );
	}

	/**
	 * Ensure vary header is added to headers
	 *
	 * @runInSeparateProcess
	 */
	public function test_set_vary_header() {
		new Send_Vary_Header( 'hello' );
		do_action( 'send_headers' );
		$expect         = 'Vary: X-WPENGINE-SEGMENT';
		$headers_string = join( '', xdebug_get_headers() );
		$this->assertContains( $expect, $headers_string );
	}
}
