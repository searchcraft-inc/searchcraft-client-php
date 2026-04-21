<?php

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Searchcraft\Api\Search;
use Searchcraft\Exception\SearchcraftException;

beforeEach(function () {
    $this->apiKey = 'test-api-key';
    $this->apiEndpoint = 'http://test-api-endpoint.com';
    $this->httpClient = Mockery::mock(ClientInterface::class);
    $this->requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $this->streamFactory = Mockery::mock(StreamFactoryInterface::class);
    $this->request = Mockery::mock(RequestInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
    $this->stream = Mockery::mock(StreamInterface::class);

    $this->search = new Search(
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

test('Search::query with string query in fuzzy mode', function () {
    $indexName = 'test-index';
    $query = 'test query';
    $options = ['limit' => 10, 'mode' => 'fuzzy'];

    $expectedParams = [
        'query' => [
            'fuzzy' => [
                'ctx' => $query
            ]
        ],
        'limit' => 10
    ];

    $responseData = ['data' => ['hits' => []]];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/index/{$indexName}/search")
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->streamFactory->shouldReceive('createStream')
        ->once()
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

    $result = $this->search->query($indexName, $query, $options);

    expect($result)->toBe($responseData);
});

test('Search::query with string query in exact mode', function () {
    $indexName = 'test-index';
    $query = 'test query';
    $options = ['limit' => 10, 'mode' => 'exact'];

    $responseData = ['data' => ['hits' => []]];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/index/{$indexName}/search")
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->streamFactory->shouldReceive('createStream')
        ->once()
        ->andReturn($this->stream);

    $this->request->shouldReceive('withBody')
        ->once()
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

    $result = $this->search->query($indexName, $query, $options);

    expect($result)->toBe($responseData);
});

test('Search::query with complex query', function () {
    $indexName = 'test-index';
    $query = [
        'bool' => [
            'must' => [
                ['term' => ['field' => 'value']]
            ]
        ]
    ];
    $options = ['limit' => 20, 'offset' => 10];

    $responseData = ['data' => ['hits' => []]];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/index/{$indexName}/search")
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->streamFactory->shouldReceive('createStream')
        ->once()
        ->andReturn($this->stream);

    $this->request->shouldReceive('withBody')
        ->once()
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

    $result = $this->search->query($indexName, $query, $options);

    expect($result)->toBe($responseData);
});

test('Search::federatedQuery with string query', function () {
    $federationName = 'test-federation';
    $query = 'test query';
    $options = ['limit' => 10];

    $responseData = ['data' => ['hits' => []]];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/federation/{$federationName}/search")
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->streamFactory->shouldReceive('createStream')
        ->once()
        ->andReturn($this->stream);

    $this->request->shouldReceive('withBody')
        ->once()
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

    $result = $this->search->federatedQuery($federationName, $query, $options);

    expect($result)->toBe($responseData);
});

test('Search::federatedQuery with complex query', function () {
    $federationName = 'test-federation';
    $query = [
        'bool' => [
            'must' => [
                ['term' => ['field' => 'value']]
            ]
        ]
    ];
    $options = ['limit' => 20, 'offset' => 10, 'order_by' => 'score', 'sort' => 'desc'];

    $responseData = ['data' => ['hits' => []]];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/federation/{$federationName}/search")
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->streamFactory->shouldReceive('createStream')
        ->once()
        ->andReturn($this->stream);

    $this->request->shouldReceive('withBody')
        ->once()
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

    $result = $this->search->federatedQuery($federationName, $query, $options);

    expect($result)->toBe($responseData);
});

test('Search::searchSummary parses SSE events and invokes callback', function () {
    $indexName = 'products';
    $query = 'smartphone';
    $options = ['limit' => 5, 'mode' => 'fuzzy'];

    $sseBody = implode('', [
        "event: metadata\ndata: {\"results_count\":2,\"cached\":false}\n\n",
        "event: delta\ndata: {\"content\":\"Hello \"}\n\n",
        "event: delta\ndata: {\"content\":\"world\"}\n\n",
        "event: done\ndata: {\"results_count\":2}\n\n",
    ]);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/index/{$indexName}/search/summary")
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->streamFactory->shouldReceive('createStream')
        ->once()
        ->andReturn($this->stream);

    $this->request->shouldReceive('withBody')
        ->once()
        ->andReturn($this->request);

    $this->httpClient->shouldReceive('sendRequest')
        ->once()
        ->with($this->request)
        ->andReturn($this->response);

    $this->response->shouldReceive('getStatusCode')
        ->once()
        ->andReturn(200);

    $this->response->shouldReceive('getBody')
        ->once()
        ->andReturn($this->stream);

    $this->stream->shouldReceive('rewind')->andReturnNull();
    $this->stream->shouldReceive('eof')
        ->andReturnValues([false, true]);
    $this->stream->shouldReceive('read')
        ->once()
        ->with(8192)
        ->andReturn($sseBody);

    $captured = [];
    $result = $this->search->searchSummary(
        $indexName,
        $query,
        $options,
        function (string $event, $data) use (&$captured) {
            $captured[] = [$event, $data];
        }
    );

    expect($result)->toBe([
        ['event' => 'metadata', 'data' => ['results_count' => 2, 'cached' => false]],
        ['event' => 'delta', 'data' => ['content' => 'Hello ']],
        ['event' => 'delta', 'data' => ['content' => 'world']],
        ['event' => 'done', 'data' => ['results_count' => 2]],
    ]);
    expect($captured)->toHaveCount(4);
    expect($captured[0])->toBe(['metadata', ['results_count' => 2, 'cached' => false]]);
    expect($captured[2])->toBe(['delta', ['content' => 'world']]);
});

test('Search::searchSummary throws on API error response', function () {
    $indexName = 'products';
    $query = 'smartphone';

    $errorJson = json_encode([
        'error' => [
            'message' => 'AI features are disabled for this index',
            'code' => 400,
        ],
    ]);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/index/{$indexName}/search/summary")
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->streamFactory->shouldReceive('createStream')
        ->once()
        ->andReturn($this->stream);

    $this->request->shouldReceive('withBody')
        ->once()
        ->andReturn($this->request);

    $this->httpClient->shouldReceive('sendRequest')
        ->once()
        ->with($this->request)
        ->andReturn($this->response);

    $this->response->shouldReceive('getStatusCode')
        ->once()
        ->andReturn(400);

    $this->response->shouldReceive('getBody')
        ->once()
        ->andReturn($this->stream);

    $this->stream->shouldReceive('__toString')
        ->once()
        ->andReturn($errorJson);

    expect(fn() => $this->search->searchSummary($indexName, $query))
        ->toThrow(SearchcraftException::class, 'AI features are disabled for this index');
});

test('Search::query handles API error', function () {
    $indexName = 'test-index';
    $query = 'test query';

    $errorData = [
        'error' => [
            'message' => 'Index not found',
            'code' => 404
        ]
    ];
    $errorJson = json_encode($errorData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')
        ->andReturn($this->request);

    $this->streamFactory->shouldReceive('createStream')
        ->andReturn($this->stream);

    $this->request->shouldReceive('withBody')
        ->andReturn($this->request);

    $this->httpClient->shouldReceive('sendRequest')
        ->once()
        ->andReturn($this->response);

    $this->response->shouldReceive('getBody')
        ->once()
        ->andReturn($this->stream);

    $this->response->shouldReceive('getStatusCode')
        ->once()
        ->andReturn(404);

    $this->stream->shouldReceive('__toString')
        ->once()
        ->andReturn($errorJson);

    expect(fn() => $this->search->query($indexName, $query))
        ->toThrow(SearchcraftException::class, 'Index not found');
});
