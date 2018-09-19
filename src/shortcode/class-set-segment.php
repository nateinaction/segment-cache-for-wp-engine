<?php
/**
 * This file defines the class that creates the set-segment shortcode
 *
 * @package segment-cache-for-wp-engine
 */

namespace SegmentCacheWPE\Shortcode;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Set_Segment
 *
 * Add a WordPress shortcode to set a cache segment
 */
class Set_Segment {

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
		'segment-name' => 'default_segment_name',
		'expire'       => 0,
		'path'         => '/',
		'domain'       => '',
		'secure'       => false,
		'httponly'     => false,
	);

	/**
	 * Constructor
	 *
	 * @param null|string $header_name Name of header to segment on.
	 */
	public function __construct( $header_name = null ) {
		$this->header_name                        = $header_name;
		$this->default_set_segment_atts['expire'] = time() + 60 * 60 * 24 * 365;
		$this->add_shortcode();
	}

	/**
	 * Hook WordPress to add shortcode
	 */
	public function add_shortcode() {
		add_shortcode( 'segment-cache-set', array( $this, 'set_segment' ) );
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
		if ( isset( $atts['segment-name'] ) ) {
			$atts = $this->validate_set_segment_atts( $atts );
			return $this->setcookie(
				$atts['segment-name'],
				$atts['expire'],
				$atts['path'],
				$atts['domain'],
				$atts['secure'],
				$atts['httponly']
			);
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
