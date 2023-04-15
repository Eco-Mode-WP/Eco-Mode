<?php
/**
 * The file that defines the Alter_Schedule class.
 *
 * @package EcoMode
 */

namespace EcoMode\EcoModeWP;

/**
 * Dummy class to show the idea of autoloading.
 */
class Alter_Schedule {
	public const OPTION_NAME = 'eco_mode_registered_alteration';

	/**
	 * Registers the scheduled action.
	 *
	 * @param string $name             The name of the scheduled action.
	 * @param bool   $condition        The condition to register the scheduled action.
	 * @param string $scheduled_action The name of the scheduled action.
	 * @param string $schedule         The schedule of the scheduled action.
	 * @param string $request_url      The request url of the scheduled action.
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
	 * @param array  $args The arguments for the option.
	 */
	private static function set_option( $name, array $args ) {
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
	 * Removes the option in the database.
	 *
	 * @param string $name The name of the option.
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
	 * Gets the prevented requests per day.
	 *
	 * @param string $original The original schedule.
	 * @param string $new      The new schedule.
	 *
	 * @return int|false
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
