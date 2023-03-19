<?php
/**
 * Eco Mode WP
 *
 * Plugin Name:  Eco Mode WP
 * Version:      0.1.0
 * Description:  A Cloudfest Hackathon project.
 * Author:       Multiple
 * Text Domain:  eco-mode
 * Requires PHP: 7.4
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
define( 'ECO_MODE_BLOCKS_LIST', [
	'eco-mode-calculator'
]);

/* Load Settings */
require_once __DIR__ . '/includes/settings/settings.php';
require_once __DIR__ . '/includes/setup-blocks.php';
/**
 * Inits the plugin and registers required actions and filters.
 *
 * @since 0.1.0
 */
function init(): void {
    /**
     * How to add actions and filters for lazy loading via Composer
     *
     * add_action( 'action_handle', [ Dummy
     */

    add_action( 'plugins_loaded', [ Dummy::class, 'dummy' ] );
    add_action( 'admin_init', [ Version_Check_Throttles::class, 'init' ] );
    add_action( 'admin_init', [ DisableDashboardWidgets::class, 'init' ] );

    $throttler = new RequestThrottler( [

        // Throttle Recommended PHP Version Checks from Once a Week to Once a Month
        new ThrottledRequest( 'http://api.wordpress.org/core/serve-happy/1.0/', \MONTH_IN_SECONDS, 'GET' ),

        // Throttle Recommended Browser Version Checks from Once a Week to Once every 3 Months
        new ThrottledRequest( 'http://api.wordpress.org/core/browse-happy/1.1/', 3 * \MONTH_IN_SECONDS, 'GET' ),

	] );
	add_filter( 'pre_http_request', [ $throttler, 'throttle_request' ], 10, 3 );
	add_filter( 'http_response', [ $throttler, 'cache_response' ], 10, 3 );
	add_action( 'init', [ DailySavings::class, 'register_post_type' ] );

	$outgoing_requests = new OutgoingRequests();
	add_action( 'init', [ $outgoing_requests, 'register_post_type' ] );
	add_filter( 'http_request_args', [ $outgoing_requests, 'start_request_timer' ] );
    add_action( 'http_api_debug', [ $outgoing_requests, 'capture_request' ], 10, 5 );
}

add_action( 'init', __NAMESPACE__ . '\init', 0 );

// Alter_Schedule::reschedule('wp_https_detection', [ 'recurrence' => 'daily', 'start' => 'tomorrow 16:00' ] );
//Alter_Schedule::disable( 'wp_https_detection' );
//Alter_Schedule::clear_all();

// TODO: Remove this hook; it's only for generating some test data
add_action( 'dashboard_glance_items', function () {
	$res  = \wp_remote_get( "https://timeapi.io/api/Time/current/zone?timeZone=Europe/Amsterdam" );
	$res2 = \wp_remote_get( "https://timeapi.io/api/Time/current/zone?timeZone=Europe/Amsterdam", [ 'body' => [ 3 ] ] );
} );
