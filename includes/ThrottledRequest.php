<?php

namespace EcoMode\EcoModeWP;

class ThrottledRequest {
	public string $url;
	public int $max_frequency_in_seconds;
	public string $method;

	public function __construct( string $url, int $max_frequency_in_seconds, string $method = 'GET' ) {
		$this->url                      = $url;
		$this->max_frequency_in_seconds = $max_frequency_in_seconds;
		$this->method                   = $method;
	}
}
