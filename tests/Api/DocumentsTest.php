<?php
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Searchcraft\Api\Documents;

beforeEach(function () {
    $this->apiKey = 'test-api-key';
    $this->apiEndpoint = 'http://test-api-endpoint.com';
    $this->httpClient = Mockery::mock(ClientInterface::class);
    $this->requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $this->streamFactory = Mockery::mock(StreamFactoryInterface::class);
    $this->request = Mockery::mock(RequestInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
    $this->stream = Mockery::mock(StreamInterface::class);

    $this->documents = new Documents(
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

test('Documents::addDocuments', function () {
    $indexName = 'test-index';
    $documents = [
        ['id' => '1', 'title' => 'Document 1'],
        ['id' => '2', 'title' => 'Document 2']
    ];

    $responseData = ['status' => 'success', 'added' => 2];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/index/{$indexName}/documents")
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

    $result = $this->documents->addDocuments($indexName, $documents);

    expect($result)->toBe($responseData);
});

test('Documents::deleteDocumentsByField', function () {
    $indexName = 'test-index';
    $criteria = ['title' => 'Document 1'];

    $responseData = ['status' => 'success', 'deleted' => 1];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('DELETE', $this->apiEndpoint . "/index/{$indexName}/documents")
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

    $result = $this->documents->deleteDocumentsByField($indexName, $criteria);

    expect($result)->toBe($responseData);
});

test('Documents::getDocument', function () {
    $indexName = 'test-index';
    $documentId = 'doc123';

    $responseData = ['id' => $documentId, 'title' => 'Document 1'];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . "/index/{$indexName}/documents/{$documentId}")
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

    $result = $this->documents->getDocument($indexName, $documentId);

    expect($result)->toBe($responseData);
});

test('Documents::deleteDocument', function () {
    $indexName = 'test-index';
    $documentId = 'doc123';

    $responseData = ['status' => 'success', 'deleted' => 1];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('DELETE', $this->apiEndpoint . "/index/{$indexName}/documents/{$documentId}")
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

    $result = $this->documents->deleteDocument($indexName, $documentId);

    expect($result)->toBe($responseData);
});

test('Documents::deleteDocumentsByQuery', function () {
    $indexName = 'test-index';
    $query = ['fuzzy' => ['ctx' => 'search term']];

    $responseData = ['status' => 'success', 'deleted' => 5];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('DELETE', $this->apiEndpoint . "/index/{$indexName}/documents/query")
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

    $result = $this->documents->deleteDocumentsByQuery($indexName, $query);

    expect($result)->toBe($responseData);
});

test('Documents::deleteAllDocuments', function () {
    $indexName = 'test-index';

    $responseData = ['status' => 'success', 'deleted' => 100];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('DELETE', $this->apiEndpoint . "/index/{$indexName}/documents/all")
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

    $result = $this->documents->deleteAllDocuments($indexName);

    expect($result)->toBe($responseData);
});

test('Documents::commitTransaction', function () {
    $indexName = 'test-index';

    $responseData = ['status' => 'success', 'message' => 'Transaction committed'];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/index/{$indexName}/commit")
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

    $result = $this->documents->commitTransaction($indexName);

    expect($result)->toBe($responseData);
});

test('Documents::rollbackTransaction', function () {
    $indexName = 'test-index';

    $responseData = ['status' => 'success', 'message' => 'Transaction rolled back'];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/index/{$indexName}/rollback")
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

    $result = $this->documents->rollbackTransaction($indexName);

    expect($result)->toBe($responseData);
});
