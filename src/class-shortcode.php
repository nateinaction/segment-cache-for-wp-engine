<?php
/**
 * This file defines the class that sends the vary response header
 *
 * @package segment-cache-for-wp-engine
 */

namespace SegmentCacheWPE;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shortcode
 *
 * Add WordPress shortcodes to allow cache segments to be set and for dynamic content
 * to be displayed
 */
class Shortcode {

	/**
	 * Name of header to segment on
	 *
	 * @var string
	 */
	private $header_name;

	/**
	 * Default attributes for set_segment shortcode
	 *
	 * @var array
	 */
	public $default_set_segment_atts = array(
		'segmentname' => 'default_segment_name',
		'expire'      => 0,
		'path'        => '/',
		'domain'      => '',
		'secure'      => false,
		'httponly'    => false,
	);

	/**
	 * Constructor
	 *
	 * @param null|string $header_name Name of header to segment on.
	 */
	public function __construct( $header_name = null ) {
		$this->header_name                        = $header_name;
		$this->default_set_segment_atts['expire'] = time() + 60 * 60 * 24 * 365;

		// Hook shortcodes into WP.
		$this->hook_wp_shortcodes();
	}

	/**
	 * Hook WordPress to enable shortcodes
	 */
	public function hook_wp_shortcodes() {
		add_shortcode( 'segment-cache-set', array( $this, 'set_segment' ) );
		add_shortcode( 'segment-cache-display', array( $this, 'display_segmented_content' ) );
	}

	/**
	 * Display Segmented Content
	 *
	 * Shows or hides content based on the value of the X-WPENGINE-SEGMENT header
	 *
	 * @param array       $atts Key/value store of shortcode attributes.
	 * @param null|string $content Content within the opening/closing shortcode tags.
	 *
	 * @return null|string Return content when on the requested segment
	 */
	public function display_segmented_content( $atts = [], $content = null ) {
		// Escape the content.
		if ( ! isset( $atts['dangerously-set-html'] ) && ! $atts['dangerously-set-html'] ) {
			$content = esc_html( $content );
		}

		if ( isset( $atts['segmentname'] ) && $atts['segmentname'] == $this->header_name ) {
			return $content;
		} elseif ( ! isset( $atts['segmentname'] ) && ! $this->header_name ) {
			return $content;
		}
		return null;
	}

	/**
	 * Validate set_segment attributes
	 *
	 * Provide valid and default options to cookie setter
	 *
	 * @param array $atts Key/value store of shortcode attributes as input from user.
	 * @return array $atts Key/value store of valid attributes to be used by setcookie.
	 */
	public function validate_set_segment_atts( $atts = [] ) {
		$valid_atts = $this->default_set_segment_atts;
		foreach ( $valid_atts as $key => $value ) {
			if ( array_key_exists( $key, $atts ) ) {
				$valid_atts[ $key ] = esc_html( $atts[ $key ] );
			}
		}
		return $valid_atts;
	}

	/**
	 * Set Segment
	 *
	 * Sets the 'wpe-us' cookie
	 *
	 * @param array $atts Key/value store of shortcode attributes as input from user.
	 */
	public function set_segment( $atts = [] ) {
		if ( isset( $atts['segmentname'] ) ) {
			$atts = $this->validate_set_segment_atts( $atts );
			setcookie(
				'wpe-us',
				$atts['segmentname'],
				$atts['expire'],
				$atts['path'],
				$atts['domain'],
				$atts['secure'],
				$atts['httponly']
			);
		}
	}
}
