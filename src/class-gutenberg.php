<?php
/**
 * This file defines the class that loads the gutenberg blocks
 *
 * @package segment-cache-for-wp-engine
 */

namespace SegmentCacheWPE;

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Class Gutenberg_Blocks
 *
 * Use WordPress's add_action to register Gutenberg blocks
 */
class Gutenberg_Blocks {

	/**
	 * List of blocks to register
	 *
	 * @var array
	 */
	private $block_list = array(
		'set-segment',
	);

	/**
	 * Location of block js relative to this file
	 *
	 * @var string
	 */
	private $block_dir = 'block/';

	/**
	 * Register Blocks
	 *
	 * Loop registers all blocks in the array
	 */
	public function register_blocks() {
		foreach ( $this->block_list as $block_name ) {
			$block_js           = "${block_name}.js";
			$block_url          = plugins_url( $this->block_dir . $block_js, __FILE__ );
			$block_dir          = plugin_dir_path( __FILE__ ) . $this->block_dir . $block_js;
			$block_dependencies = array( 'wp-blocks', 'wp-element' );
			$block_version      = filemtime( $block_dir );
			wp_register_script( $block_name, $block_url, $block_dependencies, $block_version );
			register_block_type( $block_name, array( 'script' => $block_name ) );
		}
	}

	/**
	 * Hook WordPress to register Gutenberg blocks if Gutenberg is available.
	 *
	 * @return null|Gutenberg_Blocks
	 */
	public static function add_action() {
		// Once gutenberg is part of core this can be removed.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$gutenberg_blocks = new Gutenberg_Blocks();
		add_action( 'init', array( $gutenberg_blocks, 'register_blocks' ) );
		return $gutenberg_blocks;
	}
}
