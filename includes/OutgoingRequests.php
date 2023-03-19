<?php

namespace EcoMode\EcoModeWP;

class OutgoingRequests {
	const POST_TYPE = 'EM-outgoing-requests';

	private $request_start_time;

    public function __construct() {
        $this->request_start_time = 0;


    }

	public static function register_post_type() {
		\register_post_type( self::POST_TYPE,
			[ 'supports' => [ 'title' ] ] );
	}

    public function start_request_timer( $args ) {
        $this->request_start_time = microtime( true );

        return $args;
    }

    public function capture_request( $response, $context, $transport, $args, $url ) {
        $parsed = parse_url( $url );
        if ( ! is_array( $parsed ) ) {
            $parsed = [];
        }

        if ( is_wp_error( $response ) ) {
            /** @var WP_Error $response */
            $response = [ 'response' => [ 'code' => 999, 'message' => $response->get_error_message() ] ];
        }

        unset( $parsed['user'] );
        unset( $parsed['pass'] );
        unset( $parsed['query'] );

        $runtime = ( microtime( true ) - $this->request_start_time );

		$identifier = ($args['method'] ?? '') . '_' . $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'];
		$request_post = self::get_request_post($identifier);

		// Update request_post with our data
		$data = [
			'url'                => $parsed,
			'timestamp'          => $this->request_start_time,
			'runtime_in_s'       => $runtime,
			'response_http_code' => $response['response']['code'] ?? 999,
			'response_size'      => \mb_strlen( \json_encode( $response ), '8bit' ),
			'request_size'       => \mb_strlen( \json_encode(
				[
					$args['body'] ?? [],
					$args['headers'] ?? [],
					$args['cookies'] ?? [],
				]
			), '8bit' ),
			'response_hash'      => \md5( \json_encode(
				[
					$args['body'] ?? [],
					$args['headers'] ?? [],
					$args['cookies'] ?? [],
				]
			) ),
		];
		self::update_post_metas($request_post, $data);

    }

	public static function update_post_metas($request_post, $data) {
		add_post_meta($request_post, 'request_data', $data);
	}

	public static function get_request_post($identifier) {
		if (!isset($identifier)) {
			return '';
		}

		$existing_posts = \get_posts( [
			'title' => $identifier,
			'numberposts' => 1,
			'post_type'   => self::POST_TYPE,
			'fields'      => "ids",
		] );

		if ( count( $existing_posts ) === 0 ) {

			// Create new post
			$post_id = \wp_insert_post( [
				'post_type'   => self::POST_TYPE,
				'post_title'  => $identifier,
				'post_status' => 'publish',
			], true );

			if ( $post_id === 0 || \is_wp_error( $post_id ) ) {
				var_dump( $post_id );
				throw new \RuntimeException( "Can't create post" );
			}

			return $post_id;
		}

		return $existing_posts[0];
	}

}
