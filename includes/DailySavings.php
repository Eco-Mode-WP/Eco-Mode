<?php

namespace EcoMode\EcoModeWP;

class DailySavings {
	const POST_TYPE = 'EM-daily-savings';

	public static function register_post_type() {
		\register_post_type( self::POST_TYPE,
			[ 'supports' => [ 'title' ] ] );
	}

	public static function track_outgoing_request( ThrottledRequest $request ) {
		$post_id   = self::get_todays_savings();
		$old_count = (int) \get_post_meta( $post_id, 'total_outgoing_requests', true );
		\update_post_meta( $post_id, 'total_outgoing_requests', ++$old_count );
	}

	public static function track_prevented_request( ThrottledRequest $request ) {
		$post_id   = self::get_todays_savings();
		$old_count = (int) \get_post_meta( $post_id, 'total_prevent_requests', true );
		\update_post_meta( $post_id, 'total_prevent_requests', ++$old_count );
	}

	private static function get_todays_savings() {
		$today = getdate();

		$posts = \get_posts( [
			'numberposts' => 1,
			'post_type'   => self::POST_TYPE,
			'fields'      => "ids",
			'date_query'  => [
				'year'  => $today['year'],
				'month' => $today['mon'],
				'day'   => $today['mday'],
			],
		] );

		if ( count( $posts ) === 0 ) {
			return self::start_new_day();
		}

		return $posts[0];
	}

	private static function start_new_day(): int {
		$post_id = \wp_insert_post( [
			'post_type'   => self::POST_TYPE,
			'post_title'  => \wp_date( "Y-m-d" ),
			'post_status' => 'publish',
		], true );

		if ( $post_id === 0 || \is_wp_error( $post_id ) ) {
			var_dump( $post_id );
			throw new \RuntimeException( "Can't create post" );
		}

		return (int) $post_id;
	}

}
