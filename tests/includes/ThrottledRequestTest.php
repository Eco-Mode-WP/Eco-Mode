<?php

namespace includes;

use EcoMode\EcoModeWP\ThrottledRequest;
use PHPUnit\Framework\TestCase;

final class ThrottledRequestTest extends TestCase
{
	public function test_has_GET_as_default_value(): void {
		$url = "https://example.org";
		$max_frequency_in_seconds = 10;

		$actual = new ThrottledRequest( $url, $max_frequency_in_seconds );

		$this->assertSame( $actual->method, "GET" );
	}
}
