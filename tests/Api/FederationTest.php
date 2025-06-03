<?php
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Searchcraft\Api\Federation;

beforeEach(function () {
    $this->apiKey = 'test-api-key';
    $this->apiEndpoint = 'http://test-api-endpoint.com';
    $this->httpClient = Mockery::mock(ClientInterface::class);
    $this->requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $this->streamFactory = Mockery::mock(StreamFactoryInterface::class);
    $this->request = Mockery::mock(RequestInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
    $this->stream = Mockery::mock(StreamInterface::class);

    $this->federation = new Federation(
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

test('Federation::listFederations', function () {
    $responseData = ['federations' => []];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . "/federation")
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

    $result = $this->federation->listFederations();

    expect($result)->toBe($responseData);
});

test('Federation::getFederation', function () {
    $federationName = 'test-federation';
    $responseData = ['name' => $federationName, 'indexes' => []];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . "/federation/{$federationName}")
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

    $result = $this->federation->getFederation($federationName);

    expect($result)->toBe($responseData);
});

test('Federation::getFederationsByOrganization', function () {
    $organizationId = 'org123';
    $responseData = ['federations' => []];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . "/federation/organization/{$organizationId}")
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

    $result = $this->federation->getFederationsByOrganization($organizationId);

    expect($result)->toBe($responseData);
});

test('Federation::createFederation', function () {
    $federationConfig = [
        'name' => 'new-federation',
        'indexes' => ['index1', 'index2'],
        'organization_id' => 1
    ];

    $responseData = $federationConfig;
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . "/federation")
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

    $result = $this->federation->createFederation($federationConfig);

    expect($result)->toBe($responseData);
});

test('Federation::updateFederation', function () {
    $federationName = 'test-federation';
    $federationConfig = [
        'indexes' => ['index1', 'index2', 'index3'],
        'organization_id' => 1
    ];

    $responseData = array_merge(['name' => $federationName], $federationConfig);
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('PUT', $this->apiEndpoint . "/federation/{$federationName}")
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

    $result = $this->federation->updateFederation($federationName, $federationConfig);

    expect($result)->toBe($responseData);
});

test('Federation::deleteFederation', function () {
    $federationName = 'test-federation';

    $responseData = ['status' => 'success', 'message' => 'Federation deleted'];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('DELETE', $this->apiEndpoint . "/federation/{$federationName}")
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

    $result = $this->federation->deleteFederation($federationName);

    expect($result)->toBe($responseData);
});

