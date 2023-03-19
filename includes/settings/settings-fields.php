<?php
/**
 * Settings page fields
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

/**
 * Expose data to rest api
 *
 * @return array
 */
function get_custom_eco_mode_data(): array {
	$data = [];

	$eco_mode_data = get_option( 'eco_mode_prevented_requests' );
	if ( ! $eco_mode_data ) {
		$data['custom_eco_mode_data'] = $eco_mode_data;
	}
	return $data;
}



