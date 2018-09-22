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

		// Send vary header in response when segment request header is present.
		if ( $this->segment_name ) {
			$this->add_action();
		}
	}

	/**
	 * Send headers via WordPress hook
	 */
	public function add_action() {
		add_action( 'send_headers', array( $this, 'set_vary_header' ) );
	}

	/**
	 * Set the Vary response header to allow segmented caching feature to work
	 */
	public function set_vary_header() {
		header( 'Vary: X-WPENGINE-SEGMENT' );
	}
}
