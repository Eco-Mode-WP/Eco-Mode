<?php

namespace EcoMode\EcoModeWP;

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$ecomode_blog_autoloader = __DIR__ . '/vendor/autoload.php';
if ( is_readable( $ecomode_blog_autoloader ) ) {
	require_once $ecomode_blog_autoloader;
}

$options = Alter_Schedule::get_options();

if ( empty( $options ) ) {
	return;
}

foreach ( $options as $option ) {
	if ( isset( $option['scheduled_action'] ) ) {
		wp_clear_scheduled_hook( $option['scheduled_action'] );
	}
}

delete_site_option( Alter_Schedule::OPTION_NAME );
