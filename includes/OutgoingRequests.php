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
			[ 'supports' => [ 'title' , 'custom-fields' ],
				'show_in_rest' => true,
				'public'       => true,
			]
		);
		\register_meta(
			'post',
			'is_enabled',
			[
				'type'         => 'boolean',
				'description'  => 'Throttling is enabled',
				'single'       => true,
				'default'      => false,
				'show_in_rest' => true,
			]
		);
		\register_meta(
			'post',
			'max_frequency_in_seconds',
			[
				'type'              => 'integer',
				'single'            => true,
				'default'           => 9999,
				'sanitize_callback' => 'absint',
				'show_in_rest'      => true,
			]
		);
	}

	public static function get_data(): array {
		$request_posts = get_posts( [ 'post_type' => self::POST_TYPE, 'posts_per_page'=>15 ] );

		return array_map( function ( $request_post ) {
			$response_data            = (array) $request_post;
			$response_data['history'] = \get_post_meta( $request_post->ID, 'request_data', false );
			$response_data['is_enabled'] = \get_post_meta( $request_post->ID, 'is_enabled', true );
			$response_data['max_frequency_in_seconds'] = \get_post_meta( $request_post->ID, 'max_frequency_in_seconds', true );

			return $response_data;
		}, $request_posts );
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
