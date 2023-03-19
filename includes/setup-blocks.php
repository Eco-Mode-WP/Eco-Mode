<?php
/**
 * Add Gutenberg blocks
 *
 * @package Eco-Mode
 */

declare( strict_types=1 );

namespace EcoMode\Blocks;

add_action( 'init', __NAMESPACE__ . '\\register_blocks' );

/**
 * Load all templates
 */
$blocks = ECO_MODE_BLOCKS_LIST;
foreach ( $blocks as $block ) {

	if ( file_exists( ECO_MODE_DIR_PATH . 'src/block-library/' . $block . '/template.php' ) ) {
		include_once ECO_MODE_DIR_PATH . 'src/block-library/' . $block . '/template.php';
	}
}

/**
 * Register blocks
 */
function register_blocks() {
	$blocks = ECO_MODE_BLOCKS_LIST;

	foreach ( $blocks as $block ) {

		$args = [];

		if ( file_exists( ECO_MODE_DIR_PATH . 'src/block-library/' . $block . '/template.php' ) ) {
			$args['render_callback'] = apply_filters( 'render_callback_' . $block, 'return__false' );
		}

		$registered_block = register_block_type_from_metadata(
			ECO_MODE_DIR_PATH . 'src/block-library/' . $block,
			$args
		);
	}
}

