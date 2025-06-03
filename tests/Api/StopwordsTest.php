<?php
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Searchcraft\Api\Stopwords;

beforeEach(function () {
    $this->apiKey = 'test-api-key';
    $this->apiEndpoint = 'http://test-api-endpoint.com';
    $this->httpClient = Mockery::mock(ClientInterface::class);
    $this->requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $this->streamFactory = Mockery::mock(StreamFactoryInterface::class);
    $this->request = Mockery::mock(RequestInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
    $this->stream = Mockery::mock(StreamInterface::class);

    $this->stopwords = new Stopwords(
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

test('Stopwords::getStopwords', function () {
    $indexName = 'test-index';
    $responseData = ['stopwords' => ['a', 'an', 'the']];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . "/index/{$indexName}/stopwords")
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

    $result = $this->stopwords->getStopwords($indexName);

    expect($result)->toBe($responseData);
});

test('Stopwords::addStopwords', function () {
    $indexName = 'test-index';
    $stopwords = ['and', 'or', 'but'];

    $responseData = ['added' => 3, 'stopwords' => ['a', 'an', 'the', 'and', 'or', 'but']];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/index/{$indexName}/stopwords")
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

    $result = $this->stopwords->addStopwords($indexName, $stopwords);

    expect($result)->toBe($responseData);
});

test('Stopwords::deleteStopwords', function () {
    $indexName = 'test-index';
    $stopwords = ['and', 'or'];

    $responseData = ['deleted' => 2, 'stopwords' => ['a', 'an', 'the', 'but']];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('DELETE', $this->apiEndpoint . "/index/{$indexName}/stopwords")
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

    $result = $this->stopwords->deleteStopwords($indexName, $stopwords);

    expect($result)->toBe($responseData);
});

test('Stopwords::deleteAllStopwords', function () {
    $indexName = 'test-index';

    $responseData = ['deleted' => 6, 'stopwords' => []];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('DELETE', $this->apiEndpoint . "/index/{$indexName}/stopwords/all")
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

    $result = $this->stopwords->deleteAllStopwords($indexName);

    expect($result)->toBe($responseData);
});
