<?php
/**
 * Manages daily outgoing requests.
 *
 * @package Eco-Mode
 */

namespace EcoMode\EcoModeWP;

/**
 * DailySavings class.
 */
class DailySavings {
	const POST_TYPE = 'EM-daily-savings';

	/**
	 * Registers the post type.
	 */
	public static function register_post_type() {
		\register_post_type(
			self::POST_TYPE,
			[
				'supports'     => [ 'title' ],
				'show_in_rest' => true,
			]
		);
	}

	//phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Reason: I'm pretty sure it's gonna be used in the future.
	/**
	 * Tracks an outgoing request by updating its total_outgoing_requests value.
	 *
	 * @param ThrottledRequest $request The request to track.
	 */
	public static function track_outgoing_request( ThrottledRequest $request ) {
		$post_id   = self::get_todays_savings();
		$old_count = (int) \get_post_meta( $post_id, 'total_outgoing_requests', true );
		\update_post_meta( $post_id, 'total_outgoing_requests', ++$old_count );
	}
	//phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.Found

	//phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Reason: I'm pretty sure it's gonna be used in the future.
	/**
	 * Tracks an outgoing request by updating its total_prevent_requests value.
	 *
	 * @param ThrottledRequest $request The request to track.
	 */
	public static function track_prevented_request( ThrottledRequest $request ) {
		$post_id   = self::get_todays_savings();
		$old_count = (int) \get_post_meta( $post_id, 'total_prevent_requests', true );
		\update_post_meta( $post_id, 'total_prevent_requests', ++$old_count );
	}
	//phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.Found

	/**
	 * Returns the post ID of today's savings.
	 *
	 * If there is no post for today, it creates one.
	 *
	 * @return int
	 */
	private static function get_todays_savings() {
		$today = getdate();

		$posts = \get_posts(
			[
				'numberposts' => 1,
				'post_type'   => self::POST_TYPE,
				'fields'      => 'ids',
				'date_query'  => [
					'year'  => $today['year'],
					'month' => $today['mon'],
					'day'   => $today['mday'],
				],
			]
		);

		if ( count( $posts ) === 0 ) {
			return self::start_new_day();
		}

		return $posts[0];
	}

	/**
	 * Creates a new post for today's savings.
	 *
	 * @return int
	 *
	 * @throws \RuntimeException Throws when a new post cannot be created.
	 */
	private static function start_new_day(): int {
		$post_id = \wp_insert_post(
			[
				'post_type'   => self::POST_TYPE,
				'post_title'  => \wp_date( 'Y-m-d' ),
				'post_status' => 'publish',
			],
			true
		);

		if ( $post_id === 0 || \is_wp_error( $post_id ) ) {
			throw new \RuntimeException( "Can't create post" );
		}

		return (int) $post_id;
	}
}
