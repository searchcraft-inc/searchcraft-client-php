<?php

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Searchcraft\Api\Measure;

beforeEach(function () {
    $this->apiKey = 'test-api-key';
    $this->apiEndpoint = 'http://test-api-endpoint.com';
    $this->httpClient = Mockery::mock(ClientInterface::class);
    $this->requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $this->streamFactory = Mockery::mock(StreamFactoryInterface::class);
    $this->request = Mockery::mock(RequestInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
    $this->stream = Mockery::mock(StreamInterface::class);

    $this->measure = new Measure(
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

test('Measure::trackEvent posts to /measure/event', function () {
    $event = [
        'event_name' => 'document_clicked',
        'properties' => [
            'searchcraft_index_names' => ['products'],
            'external_document_id' => 'abc-123',
            'document_position' => 2,
        ],
        'user' => [
            'user_id' => 'user-1',
            'user_type' => 'anonymous',
        ],
    ];

    $responseJson = json_encode(['status' => 'ok']);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . '/measure/event')
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')->andReturn($this->request);
    $this->streamFactory->shouldReceive('createStream')->once()->andReturn($this->stream);
    $this->request->shouldReceive('withBody')->once()->andReturn($this->request);
    $this->httpClient->shouldReceive('sendRequest')->once()->andReturn($this->response);

    $this->response->shouldReceive('getBody')->once()->andReturn($this->stream);
    $this->response->shouldReceive('getStatusCode')->once()->andReturn(200);
    $this->stream->shouldReceive('__toString')->once()->andReturn($responseJson);

    $result = $this->measure->trackEvent($event);

    expect($result)->toBe(['status' => 'ok']);
});

test('Measure::trackBatch posts to /measure/batch', function () {
    $events = [
        [
            'event_name' => 'search_completed',
            'properties' => ['searchcraft_index_names' => ['products']],
            'user' => ['user_id' => 'user-1'],
        ],
        [
            'event_name' => 'api_summary_requested',
            'properties' => [
                'searchcraft_index_names' => ['products'],
                'ai_provider' => 'anthropic',
            ],
            'user' => ['user_id' => 'user-1'],
        ],
    ];

    $responseJson = json_encode(['status' => 'ok']);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('POST', $this->apiEndpoint . '/measure/batch')
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')->andReturn($this->request);
    $this->streamFactory->shouldReceive('createStream')->once()->andReturn($this->stream);
    $this->request->shouldReceive('withBody')->once()->andReturn($this->request);
    $this->httpClient->shouldReceive('sendRequest')->once()->andReturn($this->response);

    $this->response->shouldReceive('getBody')->once()->andReturn($this->stream);
    $this->response->shouldReceive('getStatusCode')->once()->andReturn(200);
    $this->stream->shouldReceive('__toString')->once()->andReturn($responseJson);

    $result = $this->measure->trackBatch($events);

    expect($result)->toBe(['status' => 'ok']);
});

test('Measure::getStatus gets /measure/status', function () {
    $responseData = ['enabled' => true];
    $responseJson = json_encode($responseData);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . '/measure/status')
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')->andReturn($this->request);
    $this->httpClient->shouldReceive('sendRequest')->once()->andReturn($this->response);

    $this->response->shouldReceive('getBody')->once()->andReturn($this->stream);
    $this->response->shouldReceive('getStatusCode')->once()->andReturn(200);
    $this->stream->shouldReceive('__toString')->once()->andReturn($responseJson);

    $result = $this->measure->getStatus();

    expect($result)->toBe($responseData);
});

test('Measure::getDashboardSummary forwards filters as query string', function () {
    $filters = [
        'organization_id' => '4',
        'date_start' => '2026-01-01',
        'date_end' => '2026-04-01',
        // Unknown keys should be dropped before the request is built.
        'bogus' => 'ignored',
    ];

    $expectedQuery = http_build_query([
        'organization_id' => '4',
        'date_start' => '2026-01-01',
        'date_end' => '2026-04-01',
    ]);
    $expectedUrl = $this->apiEndpoint . '/measure/dashboard/summary?' . $expectedQuery;

    $responseJson = json_encode(['totals' => []]);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $expectedUrl)
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')->andReturn($this->request);
    $this->httpClient->shouldReceive('sendRequest')->once()->andReturn($this->response);

    $this->response->shouldReceive('getBody')->once()->andReturn($this->stream);
    $this->response->shouldReceive('getStatusCode')->once()->andReturn(200);
    $this->stream->shouldReceive('__toString')->once()->andReturn($responseJson);

    $result = $this->measure->getDashboardSummary($filters);

    expect($result)->toBe(['totals' => []]);
});

test('Measure::getDashboardConversion sends empty query when no filters given', function () {
    $responseJson = json_encode(['rows' => []]);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $this->apiEndpoint . '/measure/dashboard/conversion')
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')->andReturn($this->request);
    $this->httpClient->shouldReceive('sendRequest')->once()->andReturn($this->response);

    $this->response->shouldReceive('getBody')->once()->andReturn($this->stream);
    $this->response->shouldReceive('getStatusCode')->once()->andReturn(200);
    $this->stream->shouldReceive('__toString')->once()->andReturn($responseJson);

    $result = $this->measure->getDashboardConversion();

    expect($result)->toBe(['rows' => []]);
});

test('Measure::getDashboardUsage forwards filters as query string', function () {
    $filters = [
        'application_id' => '9',
        'granularity' => 'day',
        'rpp' => 50,
        'page' => 1,
    ];

    $expectedQuery = http_build_query($filters);
    $expectedUrl = $this->apiEndpoint . '/measure/dashboard/usage?' . $expectedQuery;

    $responseJson = json_encode(['rows' => []]);

    $this->requestFactory->shouldReceive('createRequest')
        ->once()
        ->with('GET', $expectedUrl)
        ->andReturn($this->request);

    $this->request->shouldReceive('withHeader')->andReturn($this->request);
    $this->httpClient->shouldReceive('sendRequest')->once()->andReturn($this->response);

    $this->response->shouldReceive('getBody')->once()->andReturn($this->stream);
    $this->response->shouldReceive('getStatusCode')->once()->andReturn(200);
    $this->stream->shouldReceive('__toString')->once()->andReturn($responseJson);

    $result = $this->measure->getDashboardUsage($filters);

    expect($result)->toBe(['rows' => []]);
});
