<?php
/**
 * Represents a throttled request.
 *
 * @package Eco-Mode
 */

namespace EcoMode\EcoModeWP;

/**
 * ThrottledRequest class.
 */
class Throttled_Request {

	/**
	 * The URL of the request.
	 *
	 * @var string
	 */
	public string $url;

	/**
	 * The maximum frequency of the request in seconds.
	 *
	 * @var int
	 */
	public int $max_frequency_in_seconds;

	/**
	 * The HTTP method of the request.
	 *
	 * @var string
	 */
	public string $method;

	/**
	 * Constructor.
	 *
	 * @param string $url                      The URL of the request.
	 * @param int    $max_frequency_in_seconds The maximum frequency of the request in seconds.
	 * @param string $method                   The HTTP method of the request.
	 */
	public function __construct( string $url, int $max_frequency_in_seconds, string $method = 'GET' ) {
		$this->url                      = $url;
		$this->max_frequency_in_seconds = $max_frequency_in_seconds;
		$this->method                   = $method;
	}
}
