<?php
/**
 * Handles throttles for wp_version_check.
 *
 * @package EcoMode
 */

namespace EcoMode\EcoModeWP;

/**
 * Handles throttles for wp_version_check.
 */
class Version_Check_Throttles {
	const FILE_MODS_THROTTLER  = 'file_mods';
	const WP_VERSION_THROTTLER = 'wp_version';
	const REQUEST_URL          = 'https://api.wordpress.org/core/version-check/';
	const SCHEDULED_ACTION     = 'wp_version_check';

	/**
	 * Initializer for throttles of wp_version_check.
	 */
	public static function init(): void {

		Alter_Schedule::register_alteration( self::FILE_MODS_THROTTLER, ! self::is_file_mod_allowed(), self::SCHEDULED_ACTION, false, self::REQUEST_URL );

		Alter_Schedule::register_alteration( self::WP_VERSION_THROTTLER, self::is_wp_outdated(), self::SCHEDULED_ACTION, 'weekly', self::REQUEST_URL );
	}

	/**
	 * Checks if file modifications are allowed.
	 *
	 * @return bool
	 */
	private static function is_file_mod_allowed() {
		return ! defined( 'DISALLOW_FILE_MODS' ) || ! DISALLOW_FILE_MODS;
	}

	/**
	 * Checks if WordPress is outdated.
	 *
	 * @return bool
	 */
	private static function is_wp_outdated() {
		$current = get_site_transient( 'update_core' );

		if ( ! isset( $current->updates ) || ! is_array( $current->updates ) ) {
			return false;
		}

		$updates = $current->updates;
		if ( $updates[0]->response === 'upgrade' ) {
			return true;
		}

		return false;
	}
}
