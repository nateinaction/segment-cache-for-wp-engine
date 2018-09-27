<?php
/**
 * Unit tests for the SetSegment shortcode class of the Segment Cache for WP Engine plugin
 *
 * @package segment-cache-for-wp-engine
 */

namespace SegmentCacheWPE\Shortcode;

/**
 * Unit tests for SetSegment shortcode class
 */
class SetSegment_Test extends \WP_UnitTestCase {
	/**
	 * Ensure shortcode is added when class is initialized
	 */
	public function test_shortcode_added_on_init() {
		new Set_Segment();
		$shortcode_exists = shortcode_exists( 'segment-cache-set' );
		$this->assertTrue( $shortcode_exists );
	}

	/**
	 * Test validating attributes passed by humans to set_segment shortcode
	 */
	public function test_validate_atts() {
		$shortcode = new Set_Segment();

		// Test no attributes.
		$atts   = array();
		$expect = $shortcode->default_atts;
		$actual = $shortcode->validate_atts( $atts );
		$this->assertEquals( $expect, $actual );

		// Test non-valid attribute.
		$atts   = array( 'blah' => '123' );
		$expect = $shortcode->default_atts;
		$actual = $shortcode->validate_atts( $atts );
		$this->assertEquals( $expect, $actual );

		// Tests html escaping.
		$atts        = array( 'path' => '<script></script>' );
		$atts_expect = array( 'path' => '&lt;script&gt;&lt;/script&gt;' );
		$expect      = array_merge( $shortcode->default_atts, $atts_expect ); // Latter keys overwrite exiting.
		$actual      = $shortcode->validate_atts( $atts );
		$this->assertEquals( $expect, $actual );

		// Test replacing attributes.
		$atts   = array(
			'path'   => '/blog/',
			'expire' => 12345,
			'secure' => true,
		);
		$expect = array_merge( $shortcode->default_atts, $atts ); // Latter keys overwrite exiting.
		$actual = $shortcode->validate_atts( $atts );
		$this->assertEquals( $expect, $actual );
	}

	/**
	 * Test that the set_segment factory runs all the things
	 */
	public function test_set_segment() {
		$shortcode_mock = $this->getMockBuilder( '\SegmentCacheWPE\Shortcode\Set_Segment' )
			->disableOriginalConstructor()
			->setMethods( array( 'validate_atts', 'atts_to_cookie_string', 'add_to_footer' ) )
			->getMock();

		// Test when no attributes passed.
		$atts = array();
		$shortcode_mock->set_segment_cookie( $atts );

		// Test when no segment-name attribute passed.
		$atts = array( 'path' => '/blah' );
		$shortcode_mock->set_segment_cookie( $atts );

		// Test that validate_set_segment_atts and setcookie are run when valid segment-name passed.
		$shortcode_mock->expects( $this->once() )
			->method( 'validate_atts' );

		$shortcode_mock->expects( $this->once() )
			->method( 'atts_to_cookie_string' );

		$shortcode_mock->expects( $this->once() )
			->method( 'add_to_footer' );

		$atts = array( 'segment-name' => 'blah' );
		$shortcode_mock->set_segment_cookie( $atts );
	}

	/**
	 * Test add to footer
	 */
	public function test_add_to_footer() {
		$shortcode = new Set_Segment();
		$shortcode->add_to_footer();
		$hook_priority = has_action( 'wp_footer', array( $shortcode, 'set_cookie' ) ); // hook priority or false.
		$this->assertInternalType( 'int', $hook_priority, '' );
	}

	/**
	 * Test set cookie
	 */
	public function test_set_cookie() {
		$shortcode                = new Set_Segment();
		$shortcode->cookie_string = 'wpe-us=default;additional=var';
		$expect                   = '<script type="text/javascript">document.cookie = "' . $shortcode->cookie_string . '";</script>';
		$shortcode->set_cookie();
		$this->expectOutputString( $expect );
	}

	/**
	 * Test atts_to_cookie_string
	 */
	public function test_atts_to_cookie_string() {
		$shortcode = new Set_Segment();

		// Test no input.
		$expect = '';
		$actual = $shortcode->atts_to_cookie_string();
		$this->assertEquals( $expect, $actual );

		// Test empty atts.
		$expect = '';
		$atts   = array();
		$actual = $shortcode->atts_to_cookie_string( $atts );
		$this->assertEquals( $expect, $actual );

		// Test non-keyed array.
		$expect = '0=a;1=b;2=c';
		$atts   = array( 'a', 'b', 'c' );
		$actual = $shortcode->atts_to_cookie_string( $atts );
		$this->assertEquals( $expect, $actual );

		// Test one att.
		$expect = 'hello=world';
		$atts   = array( 'hello' => 'world' );
		$actual = $shortcode->atts_to_cookie_string( $atts );
		$this->assertEquals( $expect, $actual );

		// Test multiple atts.
		$expect = 'hello=world;goodnight=moon';
		$atts   = array(
			'hello'     => 'world',
			'goodnight' => 'moon',
		);
		$actual = $shortcode->atts_to_cookie_string( $atts );
		$this->assertEquals( $expect, $actual );
	}
}
