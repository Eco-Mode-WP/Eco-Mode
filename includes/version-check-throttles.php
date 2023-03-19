<?php
namespace EcoMode\EcoModeWP;

/**
 * Handles throttles for wp_version_check.
 */
class Version_Check_Throttles {
	const OPTION_NAME      = 'version_check';
	const THROTTLER_NAME   = 'file_mods';
	const REQUEST_URL      = 'https://api.wordpress.org/core/version-check/';
	const SCHEDULED_ACTION = 'wp_version_check';

	/**
	 * Initializer for throttles of wp_version_check.
	 */
	public static function init(): void {
		if ( ! self::is_file_mod_allowed() ) {
			// @TODO: replace self::stop_scheduled_action when we have the implementation for rescheduling/unscheduling ready.
			$throttled_frequency = self::stop_scheduled_action( self::SCHEDULED_ACTION );
			
			if ( ! $throttled_frequency ) {
				return;
			}

			// TODO: abstract this.
			$throttle_info = [
				'scheduled_action'   => self::SCHEDULED_ACTION,
				'prevented_requests' => self::frequency_to_requests_per_day( $throttled_frequency ),
				'request_url'        => self::REQUEST_URL,
			];

			$prevented_requests = get_site_option( 'eco_mode_prevented_requests' );
			$prevented_requests[ self::OPTION_NAME ][ self::THROTTLER_NAME ] = $throttle_info;
			update_site_option( 'eco_mode_prevented_requests', $prevented_requests );
		} else {
			$prevented_requests = get_site_option( 'eco_mode_prevented_requests' );
			unset( $prevented_requests[ self::OPTION_NAME ][ self::THROTTLER_NAME ] );
		}
	}


	/**
	 * Stops the scheduled action.
	 *
	 * @param string $frequency The scheduled action we want to stop.
	 *
	 * @return string The frequency of stopped scheduled actions.
	 */
	private static function stop_scheduled_action( $scheduled_action ): string {
		$event = wp_get_scheduled_event( $scheduled_action );
		if ( ! $event ) {
			return false;
		}
		wp_unschedule_event( $event->timestamp, $scheduled_action );

		return $event->schedule;
	}


	/**
	 * Translates frequency to requests per day.
	 *
	 * @param string $frequency The frequency we want to translate into requests.
	 *
	 * @return int The amount of requests the frequency is translated into.
	 */
	private static function frequency_to_requests_per_day( string $frequency ): int {
		switch ( $frequency ) {
			case 'daily':
				return 1;
			case 'twicedaily':
				return 2;
			case 'never':
				return 0;
		}

		return 0;
	}

	private static function is_file_mod_allowed() {
		return ! defined( 'DISALLOW_FILE_MODS' ) || ! DISALLOW_FILE_MODS;
	}
}
