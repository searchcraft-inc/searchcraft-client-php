<?php

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Searchcraft\Api\Base;
use Searchcraft\Exception\SearchcraftException;

// Create a concrete implementation of the abstract Base class for testing
class TestApi extends Base
{
    public function testRequest(string $method, string $path, array $params = [], array $headers = []): array
    {
        return $this->request($method, $path, $params, $headers);
    }

    public function testParseEventStream($stream, ?callable $onEvent = null): array
    {
        return $this->parseEventStream($stream, $onEvent);
    }
}

/**
 * Minimal in-memory StreamInterface-ish stub that yields chunks from a list.
 *
 * Only implements the handful of methods parseEventStream calls:
 * rewind(), eof(), read().
 */
class ChunkedStreamStub
{
    /** @var string[] */
    private $chunks;
    /** @var int */
    private $cursor = 0;

    public function __construct(array $chunks)
    {
        $this->chunks = array_values($chunks);
    }

    public function rewind(): void
    {
        $this->cursor = 0;
    }

    public function eof(): bool
    {
        return $this->cursor >= count($this->chunks);
    }

    public function read($_length): string
    {
        if ($this->eof()) {
            return '';
        }
        return $this->chunks[$this->cursor++];
    }
}

beforeEach(function () {
    $this->apiKey = 'test-api-key';
    $this->apiEndpoint = 'http://test-api-endpoint.com';
    $this->httpClient = Mockery::mock(ClientInterface::class);
    $this->requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $this->streamFactory = Mockery::mock(StreamFactoryInterface::class);
    $this->request = Mockery::mock(RequestInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
    $this->stream = Mockery::mock(StreamInterface::class);

    $this->api = new TestApi(
        $this->apiKey,
        $this->apiEndpoint,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );
});

afterEach(function () {
    Mockery::close();
});

test('Base makes GET request correctly', function () {
    $path = '/test-path';
    $responseData = ['status' => 'success'];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . $path)
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->httpClient->shouldReceive('sendRequest')
        ->once()
        ->with($this->request)
        ->andReturn($this->response);

    $this->response->shouldReceive('getBody')
        ->once()
        ->andReturn($this->stream);

    $this->response->shouldReceive('getStatusCode')
        ->once()
        ->andReturn(200);

    $this->stream->shouldReceive('__toString')
        ->once()
        ->andReturn($responseJson);

    $result = $this->api->testRequest('GET', $path);

    expect($result)->toBe($responseData);
});

test('Base makes POST request with JSON body correctly', function () {
    $path = '/test-path';
    $params = ['key' => 'value'];
    $responseData = ['status' => 'success'];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . $path)
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->streamFactory->shouldReceive('createStream')
        ->once()
        ->with(json_encode($params))
        ->andReturn($this->stream);

    $this->request->shouldReceive('withBody')
        ->once()
        ->with($this->stream)
        ->andReturn($this->request);

    $this->httpClient->shouldReceive('sendRequest')
        ->once()
        ->with($this->request)
        ->andReturn($this->response);

    $this->response->shouldReceive('getBody')
        ->once()
        ->andReturn($this->stream);

    $this->response->shouldReceive('getStatusCode')
        ->once()
        ->andReturn(200);

    $this->stream->shouldReceive('__toString')
        ->once()
        ->andReturn($responseJson);

    $result = $this->api->testRequest('POST', $path, $params);

    expect($result)->toBe($responseData);
});

test('Base handles error responses correctly', function () {
    $path = '/test-path';
    $errorData = [
        'error' => [
            'message' => 'Test error message',
            'code' => 400
        ]
    ];
    $errorJson = json_encode($errorData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . $path)
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->httpClient->shouldReceive('sendRequest')
        ->once()
        ->with($this->request)
        ->andReturn($this->response);

    $this->response->shouldReceive('getBody')
        ->once()
        ->andReturn($this->stream);

    $this->response->shouldReceive('getStatusCode')
        ->once()
        ->andReturn(400);

    $this->stream->shouldReceive('__toString')
        ->once()
        ->andReturn($errorJson);

    expect(fn() => $this->api->testRequest('GET', $path))
        ->toThrow(SearchcraftException::class, 'Test error message');
});

test('Base handles invalid JSON responses', function () {
    $path = '/test-path';
    $invalidJson = '{invalid:json}';

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . $path)
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->httpClient->shouldReceive('sendRequest')
        ->once()
        ->with($this->request)
        ->andReturn($this->response);

    $this->response->shouldReceive('getBody')
        ->once()
        ->andReturn($this->stream);

    $this->response->shouldReceive('getStatusCode')
        ->once()
        ->andReturn(200);

    $this->stream->shouldReceive('__toString')
        ->once()
        ->andReturn($invalidJson);

    expect(fn() => $this->api->testRequest('GET', $path))
        ->toThrow(SearchcraftException::class, 'Invalid JSON response from API');
});

test('Base::parseEventStream parses a single event with JSON data', function () {
    $stream = new ChunkedStreamStub([
        "event: delta\ndata: {\"content\":\"hi\"}\n\n",
    ]);

    $events = $this->api->testParseEventStream($stream);

    expect($events)->toBe([
        ['event' => 'delta', 'data' => ['content' => 'hi']],
    ]);
});

test('Base::parseEventStream handles CRLF line endings', function () {
    $stream = new ChunkedStreamStub([
        "event: delta\r\ndata: ok\r\n\r\n",
    ]);

    $events = $this->api->testParseEventStream($stream);

    expect($events)->toBe([
        ['event' => 'delta', 'data' => 'ok'],
    ]);
});

test('Base::parseEventStream coalesces multi-line data fields with \\n', function () {
    $stream = new ChunkedStreamStub([
        "event: msg\ndata: line1\ndata: line2\n\n",
    ]);

    $events = $this->api->testParseEventStream($stream);

    expect($events)->toBe([
        ['event' => 'msg', 'data' => "line1\nline2"],
    ]);
});

test('Base::parseEventStream reassembles events split across chunk boundaries', function () {
    $stream = new ChunkedStreamStub([
        "event: delta\nda",
        "ta: hel",
        "lo\n\nevent: done\ndata: {\"n\":1}\n\n",
    ]);

    $events = $this->api->testParseEventStream($stream);

    expect($events)->toBe([
        ['event' => 'delta', 'data' => 'hello'],
        ['event' => 'done', 'data' => ['n' => 1]],
    ]);
});

test('Base::parseEventStream ignores comment lines starting with colon', function () {
    $stream = new ChunkedStreamStub([
        ": keep-alive\nevent: delta\ndata: ok\n\n",
    ]);

    $events = $this->api->testParseEventStream($stream);

    expect($events)->toBe([
        ['event' => 'delta', 'data' => 'ok'],
    ]);
});

test('Base::parseEventStream invokes per-event callback as events arrive', function () {
    $stream = new ChunkedStreamStub([
        "event: metadata\ndata: {\"results_count\":1}\n\n",
        "event: delta\ndata: {\"content\":\"hi\"}\n\n",
        "event: done\ndata: {\"results_count\":1}\n\n",
    ]);

    $captured = [];
    $this->api->testParseEventStream($stream, function (string $event, $data) use (&$captured) {
        $captured[] = [$event, $data];
    });

    expect($captured)->toBe([
        ['metadata', ['results_count' => 1]],
        ['delta', ['content' => 'hi']],
        ['done', ['results_count' => 1]],
    ]);
});

test('Base::parseEventStream defaults event name to "message" when absent', function () {
    $stream = new ChunkedStreamStub([
        "data: hello\n\n",
    ]);

    $events = $this->api->testParseEventStream($stream);

    expect($events)->toBe([
        ['event' => 'message', 'data' => 'hello'],
    ]);
});

test('Base handles request exceptions', function () {
    $path = '/test-path';
    $exceptionMessage = 'Connection failed';

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . $path)
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->httpClient->shouldReceive('sendRequest')
        ->once()
        ->with($this->request)
        ->andThrow(new Exception($exceptionMessage));

    expect(fn() => $this->api->testRequest('GET', $path))
        ->toThrow(SearchcraftException::class, 'API request failed: ' . $exceptionMessage);
});
