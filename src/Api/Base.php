<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Searchcraft\Exception\SearchcraftException;
use Searchcraft\Searchcraft;

abstract class Base
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiEndpoint;

    /**
     * @var string
     */
    protected $keyType;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @param string $apiKey Your Searchcraft API key
     * @param string $apiEndpoint API endpoint
     * @param ClientInterface $httpClient PSR-18 HTTP Client
     * @param RequestFactoryInterface $requestFactory PSR-17 Request Factory
     * @param StreamFactoryInterface $streamFactory PSR-17 Stream Factory
     * @param string $keyType Type of API key (ingest, read, or admin)
     */
    public function __construct(
        string $apiKey,
        string $apiEndpoint,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $keyType = Searchcraft::KEY_TYPE_ADMIN
    ) {
        $this->apiKey = $apiKey;
        $this->apiEndpoint = $apiEndpoint;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->keyType = $keyType;
    }

    /**
     * Perform a request to the API
     *
     * @param string $method HTTP method
     * @param string $path API path
     * @param array $params Request parameters
     * @param array $headers Additional headers
     * @return array Response data
     * @throws SearchcraftException On request error
     */
    protected function request(string $method, string $path, array $params = [], array $headers = []): array
    {
        // Ensure path starts with a slash
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }

        $url = $this->apiEndpoint . $path;
        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $request = $this->requestFactory->createRequest($method, $url);

        // Add standard headers
        $request = $request->withHeader('User-Agent', \Searchcraft\Searchcraft::getVersion())
            ->withHeader('Authorization', $this->apiKey)
            ->withHeader('Accept', 'application/json');

        // Add custom headers
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        // Add request body for POST, PUT, PATCH, DELETE
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']) && !empty($params)) {
            $request = $request->withHeader('Content-Type', 'application/json');
            $body = $this->streamFactory->createStream(json_encode($params, JSON_PRESERVE_ZERO_FRACTION));
            $request = $request->withBody($body);
        }

        try {
            $response = $this->httpClient->sendRequest($request);
            return $this->handleResponse($response);
        } catch (\Throwable $e) {
            throw new SearchcraftException('API request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Handle API response
     *
     * @param ResponseInterface $response
     * @return array
     * @throws SearchcraftException
     */
    protected function handleResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SearchcraftException('Invalid JSON response from API');
        }

        if ($statusCode >= 400) {
            throw SearchcraftException::fromApiResponse($data, $statusCode);
        }

        return $data;
    }

    /**
     * Perform a request that returns a Server-Sent Events (SSE) stream.
     *
     * Each event is parsed into an associative array of
     * `['event' => string, 'data' => mixed]`. When `$onEvent` is provided
     * it is invoked once per event as it is read from the stream,
     * receiving the event name and decoded data. The full list of events
     * is returned once the stream has been fully consumed.
     *
     * @param string $method HTTP method to use for the request (for
     *                       example `"GET"` or `"POST"`).
     * @param string $path API path relative to the configured endpoint.
     *                     A leading `/` is added automatically if absent.
     * @param array $params Request parameters. For POST/PUT/PATCH/DELETE
     *                      methods these are JSON-encoded and sent as the
     *                      request body; for GET they are ignored.
     * @param callable|null $onEvent Optional per-event callback. Signature:
     *                               `fn(string $event, mixed $data): void`.
     * @param array $headers Additional headers to apply to the request,
     *                       as an associative array of `name => value`.
     * @return array List of parsed SSE events as described above.
     * @throws SearchcraftException On network failure, an invalid JSON
     *                              error response, or an HTTP status >= 400.
     */
    protected function streamRequest(
        string $method,
        string $path,
        array $params = [],
        ?callable $onEvent = null,
        array $headers = []
    ): array {
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }

        $url = $this->apiEndpoint . $path;
        $request = $this->requestFactory->createRequest($method, $url);

        $request = $request->withHeader('User-Agent', \Searchcraft\Searchcraft::getVersion())
            ->withHeader('Authorization', $this->apiKey)
            ->withHeader('Accept', 'text/event-stream');

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']) && !empty($params)) {
            $request = $request->withHeader('Content-Type', 'application/json');
            $body = $this->streamFactory->createStream(json_encode($params, JSON_PRESERVE_ZERO_FRACTION));
            $request = $request->withBody($body);
        }

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Throwable $e) {
            throw new SearchcraftException('API request failed: ' . $e->getMessage(), 0, $e);
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            $errorBody = (string) $response->getBody();
            $errorData = json_decode($errorBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errorData = ['error' => ['message' => $errorBody ?: 'Unknown error']];
            }
            throw SearchcraftException::fromApiResponse($errorData, $statusCode);
        }

        return $this->parseEventStream($response->getBody(), $onEvent);
    }

    /**
     * Parse a Server-Sent Events stream into an array of events.
     *
     * Expects a PSR-7 {@see \Psr\Http\Message\StreamInterface} (or any
     * object exposing `eof()`, `read(int $length)`, and optionally
     * `rewind()`), reads it to completion, and groups each SSE frame into
     * an associative array keyed by `event` and `data`. When a frame's
     * `data:` field parses as JSON the decoded value is returned;
     * otherwise the raw string is returned as-is. Comment lines (`:`)
     * are ignored, CRLF and LF line endings are both accepted, and
     * events split across chunk boundaries are reassembled correctly.
     *
     * @param \Psr\Http\Message\StreamInterface|object $stream The stream
     *                      to read events from.
     * @param callable|null $onEvent Optional callback invoked once per
     *                               event with the event name and its
     *                               decoded data payload. Signature:
     *                               `fn(string $event, mixed $data): void`.
     * @return array List of parsed SSE events, each shaped as
     *               `['event' => string, 'data' => mixed]`.
     */
    protected function parseEventStream($stream, ?callable $onEvent = null): array
    {
        $events = [];
        $buffer = '';
        $currentEvent = null;
        $dataLines = [];

        $processEvent = function () use (&$events, &$currentEvent, &$dataLines, $onEvent) {
            if ($currentEvent === null && empty($dataLines)) {
                return;
            }

            $eventName = $currentEvent ?? 'message';
            $rawData = implode("\n", $dataLines);
            $decoded = json_decode($rawData, true);
            $data = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $rawData;

            $entry = ['event' => $eventName, 'data' => $data];
            $events[] = $entry;

            if ($onEvent !== null) {
                $onEvent($eventName, $data);
            }

            $currentEvent = null;
            $dataLines = [];
        };

        if (method_exists($stream, 'rewind')) {
            try {
                $stream->rewind();
            } catch (\Throwable $e) {
                // Stream may not be rewindable; continue reading from its
                // current position.
            }
        }

        while (!$stream->eof()) {
            $chunk = $stream->read(8192);
            if ($chunk === '' || $chunk === false) {
                break;
            }
            $buffer .= $chunk;

            while (($newlinePos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $newlinePos);
                $buffer = substr($buffer, $newlinePos + 1);

                if (substr($line, -1) === "\r") {
                    $line = substr($line, 0, -1);
                }

                if ($line === '') {
                    $processEvent();
                    continue;
                }

                if (strpos($line, ':') === 0) {
                    // Comment line — ignore.
                    continue;
                }

                $colonPos = strpos($line, ':');
                if ($colonPos === false) {
                    $field = $line;
                    $value = '';
                } else {
                    $field = substr($line, 0, $colonPos);
                    $value = substr($line, $colonPos + 1);
                    if (isset($value[0]) && $value[0] === ' ') {
                        $value = substr($value, 1);
                    }
                }

                if ($field === 'event') {
                    $currentEvent = $value;
                } elseif ($field === 'data') {
                    $dataLines[] = $value;
                }
            }
        }

        if ($buffer !== '') {
            // Flush any trailing line in the buffer that was not terminated
            // with a newline.
            $line = $buffer;
            if (substr($line, -1) === "\r") {
                $line = substr($line, 0, -1);
            }

            if ($line !== '' && strpos($line, ':') !== 0) {
                $colonPos = strpos($line, ':');
                if ($colonPos === false) {
                    $field = $line;
                    $value = '';
                } else {
                    $field = substr($line, 0, $colonPos);
                    $value = substr($line, $colonPos + 1);
                    if (isset($value[0]) && $value[0] === ' ') {
                        $value = substr($value, 1);
                    }
                }

                if ($field === 'event') {
                    $currentEvent = $value;
                } elseif ($field === 'data') {
                    $dataLines[] = $value;
                }
            }
        }

        $processEvent();

        return $events;
    }
}
