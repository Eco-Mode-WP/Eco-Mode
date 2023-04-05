<?php

namespace EcoMode\EcoModeWP;

class CleanupPluginData {

	public static function init(): void {

		// Set up regular cleaning of request logs


		// Monitor number of requests per time and adjust threshold accordingly


		// Hook our cleanup to the logging of outgoing requests
		add_action('eco_mode_outging_request_logged', [self::class, 'maybe_cleanup_request_logs']);

	}


	public static function maybe_cleanup_request_logs() {

		// TODO: define conditions
		$conditions = TRUE;

		// requests since last cleanup

		if ($conditions === TRUE) {
			self::cleanup_request_logs();
		}


		return false;
	}

	public static function cleanup_request_logs() {

		// TODO: put in some smart logic to either go by numbers or max_age
		$strategy = 'purge_amount'; // 'purge_max_age' as the other option

		// Do cleanup
		if ($strategy === 'purge_amount') {
			$purge_amount = self::get_amount_of_entries_to_purge();
			self::delete_oldest_log_entries_by_amount($purge_amount);
		}
		if ($strategy === 'purge_max_age') {
			$purge_max_age = self::get_max_age_of_entries_to_purge();
			self::delete_oldest_log_entries_by_max_age($purge_max_age);
		}

		// Save timestamp of this cleanup

		// Save no of entries remaining after cleanup

	}

	public static function get_amount_of_entries_to_purge() {

		// TODO: put in some smart logic to determine how many entries to purge
		// TODO: consider the percentage towards all entries?
		$amount = 30;

		return $amount;
	}

	public static function get_max_age_of_entries_to_purge() {

		// TODO: put in some smart logic to determine after which max age we want to purge entries
		// TODO: consider the percentage towards all entries?
		$max_age = 30 * DAY_IN_SECONDS;

		return $max_age;
	}

	private static function delete_oldest_log_entries_by_amount($amount) {
		if (!isset($amount)) {
			return;
		}

		// TODO: We need to order posts by the latest 'timestamp' within the array of 'request_data' within post_meta
		// How do we do this in a performant manner?
		// Might the post last updated time be usable? Only if it's updated when doing update_post_meta()...

	}

	private static function delete_oldest_log_entries_by_max_age($max_age) {
		if (!isset($max_age)) {
			return;
		}

		// TODO: determine posts to delete, delete posts
	}
}
