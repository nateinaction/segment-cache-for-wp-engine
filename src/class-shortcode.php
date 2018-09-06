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
		$segmentname_set                       = array_key_exists( 'segmentname', $atts );
		$segmentname_eq_to_header_name         = $segmentname_set && $atts['segmentname'] == $this->header_name;
		$segmentname_and_header_name_are_unset = ! $segmentname_set && ! $this->header_name;
		return $segmentname_eq_to_header_name || $segmentname_and_header_name_are_unset;
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
		if ( ! array_key_exists( 'dangerously-set-html', $atts ) && ! $atts['dangerously-set-html'] ) {
			$content = esc_html( $content );
		}
		return $content;
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
	 * @return null|bool True if cookie has been set.
	 */
	public function set_segment( $atts = [] ) {
		if ( isset( $atts['segmentname'] ) ) {
			$atts = $this->validate_set_segment_atts( $atts );
			return $this->setcookie(
				$atts['segmentname'],
				$atts['expire'],
				$atts['path'],
				$atts['domain'],
				$atts['secure'],
				$atts['httponly']
			);
		}
	}

	/**
	 * Wrapper for setcookie
	 *
	 * Wrapping so we can mock in tests to prevent "header already sent" message.
	 *
	 * @param string $segment_name The name of segment.
	 * @param int    $expire The number of seconds until cookie expires.
	 * @param string $path The path on the site where the segment will be available.
	 * @param string $domain The subdomain on the site where the segment will be available.
	 * @param bool   $secure Only set segment if connection is over HTTPS.
	 * @param bool   $httponly Only set segment if connection is via HTTP protocol.
	 * @return bool True if cookie has been set.
	 */
	public function setcookie( $segment_name, $expire, $path, $domain, $secure, $httponly ) {
		return setcookie( 'wpe-us', $segment_name, $expire, $path, $domain, $secure, $httponly );
	}
}
