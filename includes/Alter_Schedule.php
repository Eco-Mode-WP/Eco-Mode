<?php
/**
 * The file that defines the Alter_Schedule class.
 *
 * @package EcoMode
 */

namespace EcoMode\EcoModeWP;

/**
 * Persistently alter the schedule of WP_Cron events.
 */
class Alter_Schedule {
	public const OPTION_NAME = 'eco_mode_registered_alteration';

	/**
	 * Register a persistent change in the schedule of a WP_Cron event.
	 *
	 * @param string       $name             A unique description of this alteration.
	 * @param bool         $condition        Whether to schedule the change (true) or undo the alteration (false).
	 * @param string       $scheduled_action The name of the event to reschedule.
	 * @param string|false $schedule         The new schedule name or false to disable. e.g. daily. See wp_get_schedules() for accepted values.
	 * @param string       $request_url      The url of the request that is being prevented by this alteration.
	 *
	 * @return void
	 */
	public static function register_alteration( $name, $condition, $scheduled_action, $schedule, $request_url ) {
		$registered_alternation = get_site_option( self::OPTION_NAME );

		if ( $condition ) {
			$scheduled_event = wp_get_scheduled_event( $scheduled_action );

			if ( $schedule === false && ! empty( $scheduled_event ) ) {
				wp_clear_scheduled_hook( $scheduled_action );
			} elseif ( $schedule !== $scheduled_event->schedule ) {
				wp_schedule_event( $scheduled_event->timestamp, $schedule, $scheduled_action );
			}

			if ( ! isset( $registered_alternation[ $name ] ) ) {
				self::set_option(
					$name,
					[
						'scheduled_action'   => $scheduled_action,
						'prevented_requests' => self::get_prevented_request_per_day( $scheduled_event->schedule, $schedule ),
						'request_url'        => $request_url,
					]
				);
			}
		}

		if ( ! $condition && isset( $registered_alternation[ $name ] ) ) {
			self::remove_option( $name );
			wp_clear_scheduled_hook( $scheduled_action );
		}
	}

	/**
	 * Gets the options array.
	 *
	 * @return array
	 */
	public static function get_options(): array {
		return get_site_option( self::OPTION_NAME, [] );
	}

	/**
	 * Sets the option in the database.
	 *
	 * @param string $name The name of the option.
	 * @param array  $args The value to use.
	 *
	 * @return void
	 */
	private static function set_option( string $name, array $args ) {
		$option = get_site_option( self::OPTION_NAME, [] );

		$option[ $name ] = $args;

		update_site_option( self::OPTION_NAME, $option );
	}

	/**
	 * Checks if the option is set.
	 *
	 * @param string $name The name of the option.
	 *
	 * @return bool
	 */
	private static function is_option_set( $name ) {
		$option = get_site_option( self::OPTION_NAME, [] );

		return isset( $option[ $name ] );
	}

	/**
	 * Removes the option in the database if it was set.
	 *
	 * @param string $name The name of the option.
	 *
	 * @return void
	 */
	private static function remove_option( $name ) {
		$option = get_site_option( self::OPTION_NAME, [] );

		$default_args = [
			'scheduled_action'   => '',
			'prevented_requests' => '',
			'request_url'        => '',
		];

		if ( isset( $option[ $name ] ) ) {
			unset( $option[ $name ] );
			update_site_option( self::OPTION_NAME, $option );
		}
	}

	/**
	 * Calculates the number of requests saved every day between two cron schedules, assuming that one request is sent in the cron.
	 *
	 * @param string $original The name of the original cron schedule before throttling.
	 * @param string $new      The name of the cron schedule after throttling.
	 *
	 * @return false|float The number of prevented request per day. False if the schedule is not known and calculation fails.
	 */
	private static function get_prevented_request_per_day( $original, $new ) {
		$preset = [
			'hourly'     => 24,
			'twicedaily' => 2,
			'daily'      => 1,
			'weekly'     => 0.15,
			'monthly'    => 0.032,
		];

		if ( ! array_key_exists( $original, $preset ) || ! array_key_exists( $new, $preset ) ) {
			return false;
		}

		return $preset[ $original ] - $preset[ $new ];
	}
}
