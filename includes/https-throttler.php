<?php
/**
 * Https throttler class.
 *
 * @package Eco-Mode
 */

namespace EcoMode\EcoModeWP;

/**
 * Https alteration class.
 */
class Https_Throttler {
	const NAME             = 'https_mods';
	const SCHEDULED_ACTION = 'wp_https_detection';

	/**
	 * Throttles https check.
	 */
	public static function init(): void {
		Alter_Schedule::register_alteration(
			self::NAME,
			true,
			self::SCHEDULED_ACTION,
			wp_is_using_https() ? 'weekly' : 'daily', // Alter to weekly if site is already HTTPS, daily otherwise.
			home_url( '/', 'https' )
		);
	}
}
