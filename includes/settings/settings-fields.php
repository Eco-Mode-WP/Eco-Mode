<?php
/**
 * Settings page
 *
 * @package Eco-Mode
 */

declare( strict_types=1 );

namespace EcoMode\Settings\Fields;

add_action( 'init', __NAMESPACE__ . '\\register_plugin_settings', 10 );

/**
 * Register plugin settings in api
 *
 * @return void
 */
function register_plugin_settings(): void {
	register_setting(
		'eco_mode_settings',
		'eco_mode_data',
		[
			'type'              => 'string',
			'description'       => 'Eco Mode Settings',
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_plugin_settings',
			'show_in_rest'      => true,
		]
	);
}
