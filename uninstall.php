<?php

namespace EcoMode\EcoModeWP;

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
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
