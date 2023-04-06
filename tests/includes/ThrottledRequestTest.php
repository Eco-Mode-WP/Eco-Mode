<?php

namespace EcoMode\EcoModeWP\Tests\Includes;

use EcoMode\EcoModeWP\Throttled_Request;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Throttled Request class.
 *
 * @coversDefaultClass ::ThrottledRequest
 */
final class ThrottledRequestTest extends TestCase {


	/**
	 * Tests that creating a throttled request without setting a method parameter
	 * defaults the method parameter to `GET`.
	 *
	 * @return void
	 */
	public function test_has_GET_as_default_value(): void {
		$url                      = 'https://example.org';
		$max_frequency_in_seconds = 10;

		$actual = new Throttled_Request( $url, $max_frequency_in_seconds );

		$this->assertSame( $actual->method, 'GET' );
	}
}
