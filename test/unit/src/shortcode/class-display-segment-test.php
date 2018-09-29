<?php
/**
 * Unit tests for the DisplaySegment shortcode class of the Segment Cache for WP Engine plugin
 *
 * @package segment-cache-for-wp-engine
 */

namespace SegmentCacheWPE\Shortcode;

/**
 * Unit tests for DisplaySegment shortcode class
 */
class DisplaySegment_Test extends \WP_UnitTestCase {
	/**
	 * Shortcode is automatically added when bootstrap.php loads segment-cache-for-wp-engine.php.
	 * Let's remove it so we can test some things.
	 */
	public function setUp() {
		remove_shortcode( 'segment-cache-display' );
	}

	/**
	 * Ensure shortcode is added
	 */
	public function test_adding_shortcode() {
		$shortcode_exists = shortcode_exists( 'segment-cache-display' );
		$this->assertFalse( $shortcode_exists );

		Display_Segment::add_shortcode();
		$shortcode_exists = shortcode_exists( 'segment-cache-display' );
		$this->assertTrue( $shortcode_exists );
	}

	/**
	 * Test display segmented content
	 */
	public function test_display_segmented_content() {
		$atts    = array();
		$content = 'Hello, world!';

		$shortcode_mock = $this->getMockBuilder( '\SegmentCacheWPE\Shortcode\Display_Segment' )
			->disableOriginalConstructor()
			->setMethods( array( 'escape_content', 'should_show_content' ) )
			->getMock();

		$shortcode_mock->expects( $this->exactly( 2 ) )
			->method( 'escape_content' )
			->willReturn( $content );

		$shortcode_mock->expects( $this->exactly( 2 ) )
			->method( 'should_show_content' )
			->will( $this->onConsecutiveCalls( true, false ) );

		$result = $shortcode_mock->display_segmented_content( $atts, $content );
		$this->assertEquals( $result, $content );

		$result = $shortcode_mock->display_segmented_content( $atts, $content );
		$this->assertNull( $result );
	}

	/**
	 * Test escaping content
	 */
	public function test_escape_content() {
		$shortcode       = new Display_Segment();
		$content         = '<b>Hello, world!</b>';
		$escaped_content = '&lt;b&gt;Hello, world!&lt;/b&gt;';

		// Test when no attributes passed.
		$atts   = array();
		$result = $shortcode->escape_content( $atts, $content );
		$this->assertEquals( $result, $escaped_content );

		// Test when no dangerously-set-html attribute passed.
		$atts   = array( 'foo' => 'bar' );
		$result = $shortcode->escape_content( $atts, $content );
		$this->assertEquals( $result, $escaped_content );

		// // Test when dangerously-set-html attribute set to false.
		$atts   = array( 'dangerously-set-html' => false );
		$result = $shortcode->escape_content( $atts, $content );
		$this->assertEquals( $result, $escaped_content );

		// Test when dangerously-set-html attribute set to true.
		$atts   = array( 'dangerously-set-html' => true );
		$result = $shortcode->escape_content( $atts, $content );
		$this->assertEquals( $result, $content );

		// Test when random truthy dangerously-set-html attribute passed.
		$atts   = array( 'dangerously-set-html' => '123456' );
		$result = $shortcode->escape_content( $atts, $content );
		$this->assertEquals( $result, $content );
	}

	/**
	 * Test should show content
	 */
	public function test_should_show_content() {
		$shortcode             = new Display_Segment();
		$shortcode_with_header = new Display_Segment( 'foo' );

		// Test when no attributes passed. No segment-name and no header = true.
		$atts   = array();
		$result = $shortcode->should_show_content( $atts );
		$this->assertTrue( $result );

		// Test when random attributes passed. Random segment-name and no header = true.
		$atts   = array( 'foo' => 'bar' );
		$result = $shortcode->should_show_content( $atts );
		$this->assertTrue( $result );

		// Test when random attributes passed. segment-name but no header = false.
		$atts   = array( 'segment-name' => 'foo' );
		$result = $shortcode->should_show_content( $atts );
		$this->assertFalse( $result );

		// Test with header when no attributes passed. No segment-name but with header = false.
		$atts   = array();
		$result = $shortcode_with_header->should_show_content( $atts );
		$this->assertFalse( $result );

		// Test with header when no attributes passed. Random segment-name and with header = false.
		$atts   = array( 'foo' => 'bar' );
		$result = $shortcode_with_header->should_show_content( $atts );
		$this->assertFalse( $result );

		// Test with header when incorrect segment-name passed.
		$atts   = array( 'segment-name' => 'bar' );
		$result = $shortcode_with_header->should_show_content( $atts );
		$this->assertFalse( $result );

		// Test with header when correct segment-name passed.
		$atts   = array( 'segment-name' => 'foo' );
		$result = $shortcode_with_header->should_show_content( $atts );
		$this->assertTrue( $result );
	}
}
