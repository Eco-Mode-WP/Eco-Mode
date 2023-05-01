<?php
/**
 * Optionally disable the WP News Events dashboard that ships with WordPress core.
 * The dashboard widgets requests a feed when visiting the wp-admin dashboard.
 * This is wasteful if the widget is not frequently looked at.
 *
 * @package Eco-Mode\WP-Core
 */

namespace EcoMode\EcoModeWP;

/**
 * Class to disable the WP news events dashboard widget for saving requests.
 */
class Disable_WordPress_News_Events_Widget {
	private const OPTION_NAME = 'eco_mode_disable_wordpress_news_events_widget_default';

	/**
	 * Initializes this class.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Clean up on deactivation.
		add_action(
			'eco_mode_deactivate',
			[ self::class, 'delete_option' ]
		);

		$is_disabled = self::get_option();

		add_filter(
			'eco_mode_settings_data',
			static function ( $data ) use ( $is_disabled ) {
				$data['disableWordPressNewsEventsWidgetNonce'] = wp_create_nonce( self::OPTION_NAME );
				$data['disableWordPressNewsEventsWidget']      = $is_disabled;

				return $data;
			}
		);

		add_action(
			'wp_ajax_eco_mode_disable_wordpress_news_events_widget_default',
			[ self::class, 'ajax_save_option' ]
		);

		if ( apply_filters( 'eco_mode_disable_wordpress_news_events_widget', $is_disabled ) ) {
			remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' ); // Removes the 'WordPress News' widget.
		}
	}

	/**
	 * Saves the options.
	 *
	 * @return void
	 */
	public static function ajax_save_option() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1, 403 );
		}

		check_ajax_referer( self::OPTION_NAME );

		if ( ! isset( $_POST['value'] ) || ! is_string( $_POST['value'] ) || $_POST['value'] === '' ) {
			wp_die( -1, 403 );
		}

		$value = wp_unslash( $_POST['value'] ) === 'true';
		if ( ! self::update_option( $value ) ) {
			wp_die( -1, 500 );
		}

		wp_die();
	}

	/**
	 * Removes the option.
	 *
	 * @return bool Whether the delete succeeded.
	 */
	public static function delete_option(): bool {
		return delete_site_option( self::OPTION_NAME );
	}

	/**
	 * Retrieves the option.
	 *
	 * @return bool Whether the WP news events widget is disabled by default.
	 */
	public static function get_option(): bool {
		return get_site_option( self::OPTION_NAME, 'on' ) === 'on';
	}

	/**
	 * Updates the option value.
	 *
	 * @param {bool} $value Whether to disable the WP news events widget by default.
	 *
	 * @return bool Whether the update succeeded.
	 */
	public static function update_option( $value ): bool {
		if ( ! is_bool( $value ) ) {
			return false;
		}

		return update_site_option( self::OPTION_NAME, $value ? 'on' : 'off' );
	}
}
