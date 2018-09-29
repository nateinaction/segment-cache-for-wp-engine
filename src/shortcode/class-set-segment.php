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
	 * Name of the WP Engine cookie that creates the segment
	 *
	 * @const string
	 */
	const COOKIE_NAME = 'wpe-us';

	/**
	 * Default attributes for set_segment shortcode
	 *
	 * @var array
	 */
	public $default_atts = array(
		self::COOKIE_NAME => 'default',
		'path'            => '/',
		'max-age'         => '31536000',
		'secure'          => 'false',
		'samesite'        => 'lax',
	);

	/**
	 * All valid attributes for set_segment shortcode
	 * https://developer.mozilla.org/en-US/docs/Web/API/Document/cookie
	 *
	 * @var array
	 */
	public $valid_atts = array(
		self::COOKIE_NAME,
		'path',
		'domain',
		'max-age',
		'expire',
		'secure',
		'samesite',
	);

	/**
	 * Cookie string
	 *
	 * @var string
	 */
	public $cookie_string = self::COOKIE_NAME . '=default';

	/**
	 * Set Segment
	 *
	 * This function acts as a factory and orchestrates all cookie setting actions after shortcode is invoked
	 *
	 * @param array $atts Key/value store of shortcode attributes as input from user.
	 */
	public function set_segment_cookie( $atts = [] ) {
		if ( isset( $atts['segment-name'] ) ) {
			$atts[ self::COOKIE_NAME ] = $atts['segment-name'];
			$atts                      = $this->validate_atts( $atts );
			$this->cookie_string       = $this->atts_to_cookie_string( $atts );
			$this->add_to_footer();
		}
	}

	/**
	 * Validate set_segment attributes
	 *
	 * Provide valid and default options to cookie setter
	 *
	 * @param array $atts Key/value store of shortcode attributes as input from user.
	 * @return array $atts Key/value store of valid attributes to be used by setcookie.
	 */
	public function validate_atts( $atts = [] ) {
		$validated_atts = $this->default_atts;
		foreach ( $this->valid_atts as $key ) {
			if ( array_key_exists( $key, $atts ) ) {
				$validated_atts[ $key ] = esc_html( $atts[ $key ] );
			}
		}
		return $validated_atts;
	}

	/**
	 * Convert atts array to javascript cookie param string
	 *
	 * @param array $atts Key/value store of valid attributes.
	 * @return string Javascript cookie param string
	 */
	public function atts_to_cookie_string( $atts = [] ) {
		return implode(
			';', array_map(
				function ( $val, $key ) {
					return "{$key}={$val}";
				},
				$atts,
				array_keys( $atts )
			)
		);
	}

	/**
	 * Hook WordPress to add the inline JS cookie setter to the footer
	 */
	public function add_to_footer() {
		add_action( 'wp_footer', array( $this, 'set_cookie' ) );
	}

	/**
	 * Set cookie via inline js
	 *
	 * Enqueue and localize are great options but I could not bring myself to
	 * add an extra http request for a one line js file so this is inline.
	 */
	public function set_cookie() {
		echo '<script type="text/javascript">';
		echo 'document.cookie = "' . esc_attr( $this->cookie_string ) . '";';
		echo '</script>';
	}

	/**
	 * Hook WordPress to add shortcode
	 */
	public static function add_shortcode() {
		$set_segment = new Set_Segment();
		add_shortcode( 'segment-cache-set', array( $set_segment, 'set_segment_cookie' ) );
	}
}
