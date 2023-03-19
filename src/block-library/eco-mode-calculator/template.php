<?php
/**
 * Block template
 *
 * @package WPS_Blocks
 */

declare( strict_types=1 );

namespace EcoMode\Block\Template;

/**
 * Render callback template
 *
 * @param array $attributes Block attributes.
 */
function template( array $attributes ): string {

	$wrapper_attrs           = [];

	$wrapper_attributes = get_block_wrapper_attributes( $wrapper_attrs );

ob_start();
?>
	<div <?php echo $wrapper_attributes; //phpcs:ignore ?>>
		Calculate stuff here
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Callback function name
 *
 * @return string The template function name.
 **/
function block_frontend_template(): string {
	return __NAMESPACE__ . '\\template';
}
add_filter( 'render_callback_eco-mode-calculator', __NAMESPACE__ . '\\block_frontend_template' );
