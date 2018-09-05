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
	 * Test if class can be instantiated
	 */
	public function test_validate_set_segment_atts() {
        $shortcode = new Shortcode();

        // test no attributes
        $atts = array();
        $expect = $shortcode->default_set_segment_atts;
        $actual = $shortcode->validate_set_segment_atts($atts);
        $this->assertEquals($expect, $actual);

	    // test non-valid attribute
        $atts = array('blah' => '123');
        $expect = $shortcode->default_set_segment_atts;
        $actual = $shortcode->validate_set_segment_atts($atts);
        $this->assertEquals($expect, $actual);

        // tests html escaping
		$atts = array('path' => '<script></script>');
		$atts_expect = array('path' => '&lt;script&gt;&lt;/script&gt;');
		$expect = array_merge($shortcode->default_set_segment_atts, $atts_expect); // latter keys overwrite exiting
		$actual = $shortcode->validate_set_segment_atts($atts);
		$this->assertEquals($expect, $actual);

        // test replacing attributes
        $atts = array('path' => '/blog/', 'expire' => 12345, 'secure' => true);
        $expect = array_merge($shortcode->default_set_segment_atts, $atts); // latter keys overwrite exiting
        $actual = $shortcode->validate_set_segment_atts($atts);
        $this->assertEquals($expect, $actual);
    }
}
