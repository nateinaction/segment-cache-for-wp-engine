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
 * Class SendVaryHeader
 *
 * Use WordPress's send_headers hook to send a vary header if a cache segment is provided
 */
class SendVaryHeader {

	/**
	 * Name of header to segment on
	 *
	 * @var null|string
	 */
	private $header_name;

	/**
	 * Constructor
	 *
	 * @param null|string $header_name Name of header to segment on.
	 */
	public function __construct( $header_name = null ) {
		$this->header_name = $header_name;

		// send vary header in response when segment request header is present.
		if ( $this->header_name ) {
			$this->hook_wp_headers();
		}
	}

	/**
	 * Send headers via WordPress hook
	 */
	public function hook_wp_headers() {
		add_action( 'send_headers', array( $this, 'send_vary_header' ) );
	}

	/**
	 * Set the Vary response header to allow segmented caching feature to work
	 */
	public function send_vary_header() {
		header( 'Vary: X-WPENGINE-SEGMENT' );
	}
}
