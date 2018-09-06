<?php
/**
 * Unit tests for the Shortcode class of the Segment Cache for WP Engine plugin
 *
 * @package segment-cache-for-wp-engine
 */

namespace SegmentCacheWPE;

/**
 * Unit tests for Shortcode class
 */
class Shortcode_Test extends \WP_UnitTestCase {

	/**
	 * Test validating attributes passed by humans to set_segment shortcode
	 */
	public function test_validate_set_segment_atts() {
		$shortcode = new Shortcode();

		// Test no attributes.
		$atts   = array();
		$expect = $shortcode->default_set_segment_atts;
		$actual = $shortcode->validate_set_segment_atts( $atts );
		$this->assertEquals( $expect, $actual );

		// Test non-valid attribute.
		$atts   = array( 'blah' => '123' );
		$expect = $shortcode->default_set_segment_atts;
		$actual = $shortcode->validate_set_segment_atts( $atts );
		$this->assertEquals( $expect, $actual );

		// Tests html escaping.
		$atts        = array( 'path' => '<script></script>' );
		$atts_expect = array( 'path' => '&lt;script&gt;&lt;/script&gt;' );
		$expect      = array_merge( $shortcode->default_set_segment_atts, $atts_expect ); // Latter keys overwrite exiting.
		$actual      = $shortcode->validate_set_segment_atts( $atts );
		$this->assertEquals( $expect, $actual );

		// Test replacing attributes.
		$atts   = array(
			'path'   => '/blog/',
			'expire' => 12345,
			'secure' => true,
		);
		$expect = array_merge( $shortcode->default_set_segment_atts, $atts ); // Latter keys overwrite exiting.
		$actual = $shortcode->validate_set_segment_atts( $atts );
		$this->assertEquals( $expect, $actual );
	}

	/**
	 * Test setting cache segment cookie
	 */
	public function test_set_segment() {
		$shortcode = new Shortcode();

		// Test when no attributes passed.
		$atts   = array();
		$result = $shortcode->set_segment( $atts );
		$this->assertNull( $result );

		// Test when no segmentname attribute passed.
		$atts   = array( 'path' => '/blah' );
		$result = $shortcode->set_segment( $atts );
		$this->assertNull( $result );

		// Test that validate_set_segment_atts and setcookie are run when valid segmentname passed.
		$shortcode_mock = $this->getMockBuilder( '\SegmentCacheWPE\Shortcode' )
			->disableOriginalConstructor()
			->setMethods( array( 'validate_set_segment_atts', 'setcookie' ) )
			->getMock();

		$shortcode_mock->expects( $this->exactly( 1 ) )
			->method( 'validate_set_segment_atts' )
			->willReturn( null );

		$shortcode_mock->expects( $this->exactly( 1 ) )
			->method( 'setcookie' )
			->willReturn( true );

		$atts   = array( 'segmentname' => 'blah' );
		$result = $shortcode_mock->set_segment( $atts );
		$this->assertTrue( $result );
	}
}
