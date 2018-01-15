<?php

namespace SegmentCacheWPE;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Main
{
    /**
     * @var Shortcode
     */
    private $shortcode;

    /**
     * Constructor
     *
     * @param null $shortcode
     */
    public function __construct($shortcode = null)
    {
        $this->shortcode = $shortcode;
        $this->factory();
    }

    /**
     * Factory
     *
     * 1. Detect request header
     * 2. If request header exists, hook WordPress to send Vary response
     * 3. Initialize Shortcode class
     * 4. Hook WordPress to enable shortcodes
     */
    public function factory() {
        $header_name = null;

        // if header set, send Vary response hook into set header
        if (isset($_SERVER["HTTP_X_WPENGINE_SEGMENT"])) {
            $header_name = $_SERVER["HTTP_X_WPENGINE_SEGMENT"];
            $this->hook_wp_headers();
        }

        $this->shortcode = new Shortcode($header_name);
        $this->shortcode->hook_wp_shortcodes();
    }

    /**
     * Send headers via WordPress hook
     */
    public function hook_wp_headers() {
        add_action('send_headers', array($this, 'send_vary_header'));
    }

    /**
     * Set the Vary response header to allow segmented caching feature to work
     */
    public function send_vary_header() {
        header('Vary: X-WPENGINE-SEGMENT');
    }
}
