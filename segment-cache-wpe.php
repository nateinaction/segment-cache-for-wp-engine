<?php
/**
 * Plugin Name: Segment Cache on WP Engine
 * Plugin URI: http://wordpress.org/plugins/segment-cache-on-wp-engine/
 * Description: Implement Segmented Caching on WP Engine.
 * Version: 0.1.0
 * Author: Nate Gay
 * Author URI: https://nategay.me/
 * License: GPL3+
 */

namespace SegmentCacheWPE;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

new Main;