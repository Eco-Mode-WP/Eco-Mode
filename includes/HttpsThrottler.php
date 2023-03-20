<?php

namespace EcoMode\EcoModeWP;

use WP_Error;

/**
 * Https alteration class.
 */
class HttpsThrottler {
	const NAME             = 'https_mods';
	const SCHEDULED_ACTION = 'wp_https_detection';

	/**
	 * Throttles https check.
	 *
	 */
	public static function init(): void {		
		Alter_Schedule::register_alteration(
			self::NAME,
			true,
			self::SCHEDULED_ACTION,
			wp_is_using_https() ? 'weekly' : 'daily', // Alter to weekly if site is already HTTPS, daily otherwise
			wp_is_using_https() ? 1.8 : 1,
			home_url( '/', 'https' )
		);
	}
}
