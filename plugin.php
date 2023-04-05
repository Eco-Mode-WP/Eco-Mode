<?php
/**
 * Eco Mode WP
 *
 * Plugin Name:  Eco Mode WP
 * Version:      0.1.0
 * Description:  Reduce the carbon footprint of your website by intelligently preventing unnecessary web-requests and background tasks.
 * Author:       Team Eco-Mode
 * Text Domain:  eco-mode
 * Requires PHP: 7.4
 *
 * @package Eco-Mode
 */

namespace EcoMode\EcoModeWP;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$ecomode_blog_autoloader = __DIR__ . '/vendor/autoload.php';
if ( is_readable( $ecomode_blog_autoloader ) ) {
	require_once $ecomode_blog_autoloader;
}

define( 'ECO_MODE_VERSION', '0.1.0' );
define( 'ECO_MODE_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'ECO_MODE_DIR_URL', esc_url( plugin_dir_url( __FILE__ ) ) );
define(
	'ECO_MODE_BLOCKS_LIST',
	[
		'eco-mode-calculator',
	]
);

/* Load Settings */
require_once __DIR__ . '/includes/settings/settings.php';
require_once __DIR__ . '/includes/setup-blocks.php';
/**
 * Inits the plugin and registers required actions and filters.
 *
 * @since 0.1.0
 */
function init(): void {

	add_action( 'admin_init', [ Version_Check_Throttles::class, 'init' ] );
	add_action( 'admin_init', [ Disable_WordPress_News_Events_Widget::class, 'init' ] );
	add_action( 'admin_init', [ Https_Throttler::class, 'init' ] );

	\register_deactivation_hook( __FILE__, __NAMESPACE__ . '\do_deactivation_hook' );

	/**
	 * Allows the user to fully customize the Eco Mode, by replacing the mode function by a plugin of your own.
	 *
	 * Example:
	 *
	 * By adding
	 * add_filter( 'eco_mode_wp_select_mode', function() { return 'EcoMode\EcoModeWP\developer_mode'; } );
	 * To your code, you will be running in developer mode, or you simply replace the complete function.
	 *
	 * @param callable $callback The Callback function.
	 */
	call_user_func( apply_filters( 'eco_mode_wp_select_mode', __NAMESPACE__ . '\normal_mode' ) );
}

/**
 * Filter an array of ThrottledRequest objects.
 *
 * @param array $throttled_requests The array of ThrottledRequest objects.
 *
 * @return array The filtered array of ThrottledRequest objects.
 *
 * @since 0.1.0
 */
function filter_requests( array $throttled_requests ): array {
	$throttled_requests = (array) \apply_filters( 'eco_mode_wp_throttled_requests', $throttled_requests );
	$throttled_requests = \array_filter(
		$throttled_requests,
		function ( $throttled_request ) {
			return is_a( $throttled_request, Throttled_Request::class );
		}
	);

	return $throttled_requests;
}

/**
 * Normal Mode
 *
 * @since 0.1.0
 */
function normal_mode() {
	do_action( 'eco_mode_wp_mode_start', 'normal' );
	$throttled_requests = [
		// Throttle Recommended PHP Version Checks from Once a Week to Once a Month.
		new Throttled_Request( 'http://api.wordpress.org/core/serve-happy/1.0/', \MONTH_IN_SECONDS, 'GET' ),

		// Throttle Recommended Browser Version Checks from Once a Week to Once every 3 Months.
		new Throttled_Request( 'http://api.wordpress.org/core/browse-happy/1.1/', 3 * \MONTH_IN_SECONDS, 'GET' ),
	];
	$throttler          = new Request_Throttler( filter_requests( $throttled_requests ) );

	add_filter( 'pre_http_request', [ $throttler, 'throttle_request' ], 10, 3 );
	add_filter( 'http_response', [ $throttler, 'cache_response' ], 10, 3 );
	add_action( 'init', [ Daily_Savings::class, 'register_post_type' ] );

	$outgoing_requests = new Outgoing_Requests();
	add_action( 'init', [ $outgoing_requests, 'register_post_type' ] );
	add_filter( 'http_request_args', [ $outgoing_requests, 'start_request_timer' ] );
	add_action( 'http_api_debug', [ $outgoing_requests, 'capture_request' ], 10, 5 );

	do_action( 'eco_mode_wp_mode_end', 'normal' );
}

/**
 * Developer Mode
 *
 * @since 0.1.0
 */
function developer_mode() {
	do_action( 'eco_mode_wp_mode_start', 'developer' );
	$throttled_requests = [
		// Throttle Recommended PHP Version Checks from Once a Week to Once a Month.
		new Throttled_Request( 'http://api.wordpress.org/core/serve-happy/1.0/', \MONTH_IN_SECONDS, 'GET' ),

		// Throttle Recommended Browser Version Checks from Once a Week to Once every 3 Months.
		new Throttled_Request( 'http://api.wordpress.org/core/browse-happy/1.1/', 3 * \MONTH_IN_SECONDS, 'GET' ),
	];
	$throttler          = new Request_Throttler( filter_requests( $throttled_requests ) );

	add_filter( 'pre_http_request', [ $throttler, 'throttle_request' ], 10, 3 );
	add_filter( 'http_response', [ $throttler, 'cache_response' ], 10, 3 );
	add_action( 'init', [ Daily_Savings::class, 'register_post_type' ] );

	Alter_Schedule::disable( 'wp_https_detection' );
	$outgoing_requests = new Outgoing_Requests();
	add_action( 'init', [ $outgoing_requests, 'register_post_type' ] );
	add_filter( 'http_request_args', [ $outgoing_requests, 'start_request_timer' ] );
	add_action( 'http_api_debug', [ $outgoing_requests, 'capture_request' ], 10, 5 );

	do_action( 'eco_mode_wp_mode_end', 'developer' );
}

add_action( 'init', __NAMESPACE__ . '\init', 0 );


/**
 * Callback for the WordPress deactivation hook.
 */
function do_deactivation_hook(): void {
	$options = Alter_Schedule::get_options();

	if ( empty( $options ) ) {
		return;
	}

	foreach ( $options as $option ) {
		if ( isset( $option['scheduled_action'] ) ) {
			\wp_clear_scheduled_hook( $option['scheduled_action'] );
		}
	}

	\delete_site_option( Alter_Schedule::OPTION_NAME );
	\delete_option( Alter_Schedule::OPTION_NAME );
	\do_action( 'eco_mode_deactivate' );
}
