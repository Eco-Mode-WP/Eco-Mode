<?php
/**
 * The file that defines the Request_Throttler class.
 *
 * @package EcoMode
 */

namespace EcoMode\EcoModeWP;

use WP_Error;

/**
 * Class Request_Throttler
 */
class Request_Throttler {

	/**
	 * The throttled requests.
	 *
	 * @var Throttled_Request[]
	 */
	private array $throttled_requests;

	/**
	 * Request_Throttler constructor.
	 *
	 * @param Throttled_Request[] $throttled_requests The throttled requests.
	 */
	public function __construct( array $throttled_requests ) {
		$this->throttled_requests = \array_reduce(
			$throttled_requests,
			function ( $map, $throttled_request ) {
				$map[ $throttled_request->url ] = $throttled_request;

				return $map;
			},
			[]
		);
	}

	/**
	 * Throttles requests by serving from cache.
	 *
	 * Intended to be used on the `pre_http_request` filter.
	 *
	 * Returning a non-false value from the filter will short-circuit the HTTP request and return
	 * early with that value. A filter should return one of:
	 *
	 *  - An array containing 'headers', 'body', 'response', 'cookies', and 'filename' elements
	 *  - A WP_Error instance
	 *  - boolean false to avoid short-circuiting the response.
	 *
	 * Returning any other value may result in unexpected behaviour.
	 *
	 * @param false|array|WP_Error $response    A preemptive return value of an HTTP request. Default false.
	 * @param array                $parsed_args HTTP request arguments.
	 * @param string               $url         The request URL.
	 *
	 * @return false|array|WP_Error A preemptive return value of an HTTP request.
	 */
	public function throttle_request( $response = false, $parsed_args, $url ) {
		if ( ! isset( $this->throttled_requests[ $url ] ) ) {
			return $response;
		}

		$throttled_request = $this->throttled_requests[ $url ];

		$cache = \get_transient( $this->get_cache_key( $throttled_request, $parsed_args ) );

		if ( ! $cache ) {
			Daily_Savings::track_outgoing_request( $throttled_request );

			return $response;
		}

		Daily_Savings::track_prevented_request( $throttled_request );

		return $cache;
	}

	/**
	 * Caches the result of any request that should be throttled.
	 *
	 * Intended to be used on the `http_response` filter.
	 *
	 * Filters a successful HTTP API response immediately before the response is returned.
	 *
	 * @param array  $response    HTTP response.
	 * @param array  $parsed_args HTTP request arguments.
	 * @param string $url         The request URL.
	 *
	 * @return array The response.
	 */
	public function cache_response( $response, $parsed_args, $url ) {
		if ( ! isset( $this->throttled_requests[ $url ] ) ) {
			return $response;
		}

		$throttled_request = $this->throttled_requests[ $url ];

		$method = $parsed_args['method'];
		if ( $throttled_request->method !== $method ) {
			return $response;
		}

		if ( $response['response']['code'] < 200 || $response['response']['code'] >= 300 ) {
			return $response;
		}

		if ( $parsed_args['stream'] ) {
			return $response;
		}

		\set_transient(
			$this->get_cache_key( $throttled_request, $parsed_args ),
			$response,
			$throttled_request->max_frequency_in_seconds,
		);

		return $response;
	}

	/**
	 * Gets the cache key for a request.
	 *
	 * @param Throttled_Request $throttled_request The throttled request.
	 * @param array             $parsed_args The parsed arguments for the request.
	 *
	 * @return string
	 */
	private function get_cache_key( Throttled_Request $throttled_request, $parsed_args ): string {
		$relevant_args = [
			'method'              => $parsed_args['method'],
			'timeout'             => $parsed_args['timeout'],
			'redirection'         => $parsed_args['redirection'],
			'httpversion'         => $parsed_args['httpversion'],
			'user-agent'          => $parsed_args['user-agent'],
			'reject_unsafe_urls'  => $parsed_args['reject_unsafe_urls'],
			'blocking'            => $parsed_args['blocking'],
			'headers'             => $parsed_args['headers'],
			'cookies'             => $parsed_args['cookies'],
			'body'                => $parsed_args['body'],
			'compress'            => $parsed_args['compress'],
			'decompress'          => $parsed_args['decompress'],
			'sslverify'           => $parsed_args['sslverify'],
			'sslcertificates'     => $parsed_args['sslcertificates'],
			'stream'              => $parsed_args['stream'],
			'filename'            => $parsed_args['filename'],
			'limit_response_size' => $parsed_args['limit_response_size'],
		];

		return 'Eco-mode-wp-' . $throttled_request->method . '-' . $throttled_request->url . '-' . md5( \maybe_serialize( $relevant_args ) );
	}
}
