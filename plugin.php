<?php
/**
 * Eco Mode WP
 *
 * Plugin Name:  Eco Mode WP
 * Version:      0.1.0
 * Description:  A Cloudfest Hackathon project.
 * Author:       Multiple
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
	add_action( 'admin_init', [ DisableDashboardWidgets::class, 'init' ] );
}

add_action( 'init', __NAMESPACE__ . '\init', 0 );
