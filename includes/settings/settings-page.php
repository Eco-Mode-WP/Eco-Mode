<?php
/**
 * Settings page
 *
 * @package Eco-Mode
 */

declare( strict_types=1 );

namespace EcoMode\Settings\Page;

use function EcoMode\Settings\Fields\get_custom_eco_mode_data;

add_action( 'admin_menu', __NAMESPACE__ . '\\add_settings_page', 9 );

/**
 * Add settings page scripts
 */
function settings_assets(): void {

	if ( file_exists( ECO_MODE_DIR_PATH . '/build/settings/settings.js' ) ) {
		$script_deps_path    = ECO_MODE_DIR_PATH . '/build/settings/settings.asset.php';
		$script_dependencies = file_exists( $script_deps_path ) ?
			include $script_deps_path :
			[
				'dependencies' => [],
				'version'      => ECO_MODE_VERSION,
			];

		wp_register_script(
			'eco-mode-plugin-script',
			plugins_url( '../../build/settings/', __FILE__ ) . 'settings.js',
			$script_dependencies['dependencies'],
			$script_dependencies['version'],
			false
		);
		wp_enqueue_script( 'eco-mode-plugin-script' );
	}

	if ( file_exists( ECO_MODE_DIR_PATH . '/build/settings/settings.css' ) ) {
		wp_register_style(
			'eco-mode-settings-plugin-style',
			plugins_url( '../../build/settings/', __FILE__ ) . 'settings.css',
			[ 'wp-components' ],
			ECO_MODE_VERSION,
		);
		wp_enqueue_style( 'eco-mode-settings-plugin-style' );
	}

	/**
	 * Make settings available to the settings page
	 */
	\wp_localize_script( 'eco-mode-plugin-script', 'EcoModeSettings',
		get_custom_eco_mode_data()
	);
}

/**
 * Register settings page
 */
function add_settings_page(): void {
	$page_hook_suffix = add_submenu_page(
		'options-general.php',
		__('Eco Mode Settings','eco-mode'),
		__('Eco Mode Settings','eco-mode'),
		'manage_options',
		'eco_mode_settings',
		__NAMESPACE__ . '\\settings_page'
	);
	add_action( "admin_print_scripts-{$page_hook_suffix}", __NAMESPACE__ . '\\settings_assets' );
}

/**
 * Add React placeholder to settings page
 */
function settings_page(): void {
	echo '<div id="eco-mode-settings"></div>';
}
