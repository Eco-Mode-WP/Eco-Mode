<?php
/**
 * Settings page fields
 *
 * @package Eco-Mode
 */

declare( strict_types=1 );

namespace EcoMode\Settings\Fields;

use EcoMode\EcoModeWP\OutgoingRequests;

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
	// Setup data array.
	$data = [
		'custom_eco_mode_data' => [],
		'daily_savings'        => [],
		'requests'             => OutgoingRequests::get_data(),
	];

	// Get prevented requests data.
	$eco_mode_data = get_option( 'eco_mode_prevented_requests' );
	if ( $eco_mode_data ) {
		$data['custom_eco_mode_data'] = $eco_mode_data;
	}

	// Get daily savings data.
	/**
	 * Create a query to get the daily saving data from the post type
	 */
	$args = [
		'post_type'      => 'EM-daily-savings',
		'posts_per_page' => 1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	];

	$the_query = new \WP_Query( $args );

	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$id = get_the_ID();

			// return full daya for the day.
			$data['daily_savings'][] = [
				'title'              => get_the_title(),
				'date'               => get_the_date(),
				'prevented_requests' => get_post_meta( $id, 'total_prevent_requests', true ),
				'outgoing_requests'  => get_post_meta( $id, 'total_outgoing_requests', true ),
			];

		}
	}

	return $data;
}



