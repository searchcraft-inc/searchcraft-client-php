<?php
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Searchcraft\Api\Index;

beforeEach(function () {
    $this->apiKey = 'test-api-key';
    $this->apiEndpoint = 'http://test-api-endpoint.com';
    $this->httpClient = Mockery::mock(ClientInterface::class);
    $this->requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $this->streamFactory = Mockery::mock(StreamFactoryInterface::class);
    $this->request = Mockery::mock(RequestInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
    $this->stream = Mockery::mock(StreamInterface::class);

    $this->index = new Index(
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

test('Index::listIndexes', function () {
    $responseData = ['indexes' => []];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . "/index")
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

    $result = $this->index->listIndexes();

    expect($result)->toBe($responseData);
});

test('Index::getIndex', function () {
    $indexName = 'test-index';
    $responseData = ['name' => $indexName, 'config' => []];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . "/index/{$indexName}")
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

    $result = $this->index->getIndex($indexName);

    expect($result)->toBe($responseData);
});

test('Index::getIndexStats', function () {
    $indexName = 'test-index';
    $responseData = ['name' => $indexName, 'document_count' => 100, 'size' => 1024];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . "/index/{$indexName}/stats")
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

    $result = $this->index->getIndexStats($indexName);

    expect($result)->toBe($responseData);
});

test('Index::createIndex', function () {
    $indexName = 'new-index';
    $options = [
        'schema' => [
            'title' => 'text',
            'description' => 'text',
        ]
    ];

    $responseData = ['name' => $indexName, 'schema' => $options['schema']];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/index")
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

    $result = $this->index->createIndex($indexName, $options);

    expect($result)->toBe($responseData);
});

test('Index::updateIndex', function () {
    $indexName = 'test-index';
    $options = [
        'schema' => [
            'title' => 'text',
            'description' => 'text',
            'tags' => 'text'
        ]
    ];

    $responseData = ['name' => $indexName, 'schema' => $options['schema']];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('PUT', $this->apiEndpoint . "/index/{$indexName}")
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

    $result = $this->index->updateIndex($indexName, $options);

    expect($result)->toBe($responseData);
});

test('Index::patchIndex', function () {
    $indexName = 'test-index';
    $options = [
        'search_fields' => ['title', 'description'],
        'weight_multipliers' => ['title' => 3, 'description' => 1]
    ];

    $responseData = ['name' => $indexName, 'config' => array_merge(['schema' => []], $options)];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('PATCH', $this->apiEndpoint . "/index/{$indexName}")
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

    $result = $this->index->patchIndex($indexName, $options);

    expect($result)->toBe($responseData);
});

test('Index::deleteIndex', function () {
    $indexName = 'test-index';

    $responseData = ['status' => 'success', 'message' => 'Index deleted'];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('DELETE', $this->apiEndpoint . "/index/{$indexName}")
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

    $result = $this->index->deleteIndex($indexName);

    expect($result)->toBe($responseData);
});
