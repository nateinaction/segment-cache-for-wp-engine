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
 * Class Send_Vary_Header
 *
 * Use WordPress's send_headers hook to send a vary header if a cache segment is provided
 */
class Send_Vary_Header {

	/**
	 * Name of the segment
	 *
	 * @var null|string
	 */
	private $segment_name;

	/**
	 * Constructor
	 *
	 * @param null|string $segment_name Name of header to segment on.
	 */
	public function __construct( $segment_name = null ) {
		$this->segment_name = $segment_name;
	}

	/**
	 * Set the Vary response header to allow segmented caching feature to work
	 */
	public function set_vary_header() {
		header( 'Vary: X-WPENGINE-SEGMENT' );
	}

	/**
	 * Hook WordPress to send vary header in response only when segment_name is present.
	 *
	 * @param null|string $segment_name Name of header to segment on.
	 * @return Send_Vary_Header
	 */
	public static function add_action( $segment_name = null ) {
		// Send vary header in response when segment request header is present.
		$send_vary_header = new Send_Vary_Header( $segment_name );
		if ( $segment_name ) {
			add_action( 'send_headers', array( $send_vary_header, 'set_vary_header' ) );
		}
		return $send_vary_header;
	}
}
