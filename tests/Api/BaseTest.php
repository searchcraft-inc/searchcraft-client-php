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
