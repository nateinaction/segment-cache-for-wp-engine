<?php
/**
 * This file defines the class that creates the display-segment shortcode
 *
 * @package segment-cache-for-wp-engine
 */

namespace SegmentCacheWPE\Shortcode;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Display_Segment
 *
 * Add a WordPress shortcode to display segmented content
 */
class Display_Segment {

	/**
	 * Name of header to segment on
	 *
	 * @var string
	 */
	private $header_name;

	/**
	 * Constructor
	 *
	 * @param null|string $segment_name Name of header to segment on.
	 */
	public function __construct( $segment_name = null ) {
		$this->header_name = $segment_name;
		$this->add_shortcode();
	}

	/**
	 * Hook WordPress to add shortcode
	 */
	public function add_shortcode() {
		add_shortcode( 'segment-cache-display', array( $this, 'display_segmented_content' ) );
	}

	/**
	 * Display Segmented Content
	 *
	 * Shows or hides content based on the value of the X-WPENGINE-SEGMENT header
	 *
	 * @param array  $atts Key/value store of shortcode attributes.
	 * @param string $content Content within the opening/closing shortcode tags.
	 *
	 * @return string Return content when on the requested segment
	 */
	public function display_segmented_content( $atts = [], $content = '' ) {
		$content = $this->escape_content( $atts, $content );
		return $this->should_show_content( $atts ) ? $content : null;
	}

	/**
	 * Should Show Content
	 *
	 * Determines whether content should be hidden or shown.
	 * Content should only be shown if the segment name and header name are the same or if both are unset.
	 *
	 * @param array $atts Key/value store of shortcode attributes.
	 *
	 * @return string Return content when on the requested segment
	 */
	public function should_show_content( $atts = [] ) {
		$segment_name_set                       = isset( $atts['segment-name'] );
		$segment_name                           = $segment_name_set ? $atts['segment-name'] : null;
		$segment_name_eq_to_header_name         = $segment_name_set && $segment_name == $this->header_name;
		$segment_name_and_header_name_are_unset = ! $segment_name_set && ! $this->header_name;
		return $segment_name_eq_to_header_name || $segment_name_and_header_name_are_unset;
	}

	/**
	 * Escape content
	 *
	 * Escapes content unless "dangerously-set-html" or "dangerously-set-html => true" are passed to shortcode
	 *
	 * @param array  $atts Key/value store of shortcode attributes.
	 * @param string $content Content within the opening/closing shortcode tags.
	 *
	 * @return string Returns content escaped or unescaped
	 */
	public function escape_content( $atts = [], $content = '' ) {
		$dangerously_set_html = isset( $atts['dangerously-set-html'] ) ? $atts['dangerously-set-html'] : false;
		return $dangerously_set_html ? $content : esc_html( $content );
	}
}
