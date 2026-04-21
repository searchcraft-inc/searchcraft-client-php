<?php

declare(strict_types=1);

namespace Searchcraft\Tests\Helpers;

use Searchcraft\Api\Base;

/**
 * Concrete subclass of the abstract Searchcraft\Api\Base class used to
 * exercise its protected `request()` and `parseEventStream()` helpers
 * from Pest tests.
 */
class TestApi extends Base
{
    /**
     * Expose Base::request() for direct testing.
     *
     * @param string $method HTTP method.
     * @param string $path API path.
     * @param array $params Request parameters forwarded to Base::request().
     * @param array $headers Additional headers forwarded to Base::request().
     * @return array Decoded JSON response from the mocked HTTP client.
     */
    public function testRequest(string $method, string $path, array $params = [], array $headers = []): array
    {
        return $this->request($method, $path, $params, $headers);
    }

    /**
     * Expose Base::parseEventStream() for direct testing.
     *
     * @param object $stream Stream-like object whose `eof()`, `read()`,
     *                       and optional `rewind()` methods are used to
     *                       supply bytes to the parser.
     * @param callable|null $onEvent Optional per-event callback forwarded
     *                               to parseEventStream().
     * @return array List of parsed SSE events.
     */
    public function testParseEventStream($stream, ?callable $onEvent = null): array
    {
        return $this->parseEventStream($stream, $onEvent);
    }
}
