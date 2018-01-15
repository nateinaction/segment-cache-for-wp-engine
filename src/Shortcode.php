<?php

namespace SegmentCacheWPE;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Shortcode
{
    /**
     * @var string
     */
    private $header_name;

    /**
     * Constructor
     *
     * @param null $header_name
     */
    public function __construct($header_name = null)
    {
        $this->header_name = $header_name;
    }

    /**
     * Hook WordPress to enable shortcodes
     */
    public function hook_wp_shortcodes() {
        add_shortcode('segment-cache-set', array($this, 'set_segment'));
        add_shortcode('segment-cache-display', array($this, 'display_segmented_content'));
    }

    /**
     * Display Segmented Content
     *
     * Shows or hides content based on the value of the X-WPENGINE-SEGMENT header
     *
     * @param array $atts Key/value store of shortcode attributes
     * @param null|string $content Content within the opening/closing shortcode tags
     *
     * @return null|string Return content when on the requested segment
     */
    public function display_segmented_content($atts = [], $content = null)
    {
        // escape the content
        if (! isset($atts['dangerously-set-html']) && ! $atts['dangerously-set-html']) {
            $content = esc_html($content);
        }

        if (isset($atts['segment-on']) && $atts['segment-on'] == $this->header_name) {
            return $content;
        } else if (! isset($atts['segment-on']) && ! $this->header_name) {
            return $content;
        }
        return null;
    }

    /**
     * Set Segment
     *
     * Sets the 'wpe-us' cookie
     *
     * @param array $atts Key/value store of shortcode attributes
     *
     * @return null No need to output anything, returning null
     */
    public function set_segment($atts = [])
    {
        $expire = time()+60*60*24*365;
        $path = "/";
        $domain = "";
        $secure = false;
        $httponly = false;

        if (isset($atts['expire'])) {
            $expire = esc_html($atts['expire']);
        }

        if (isset($atts['path'])) {
            $expire = esc_html($atts['path']);
        }

        if (isset($atts['domain'])) {
            $expire = esc_html($atts['domain']);
        }

        if (isset($atts['secure'])) {
            $expire = esc_html($atts['secure']);
        }

        if (isset($atts['expire'])) {
            $expire = esc_html($atts['httponly']);
        }

        if (isset($atts['segment-on'])) {
            $value = esc_html($atts['segment-on']);
            setcookie('wpe-us', $value, $expire, $path, $domain, $secure, $httponly);
        }
        return null;
    }
}
