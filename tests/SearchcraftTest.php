<?php

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Searchcraft\Searchcraft;
use Searchcraft\Exception\SearchcraftException;
use Searchcraft\Api\Authentication;
use Searchcraft\Api\Documents;
use Searchcraft\Api\Federation;
use Searchcraft\Api\Healthcheck;
use Searchcraft\Api\Index;
use Searchcraft\Api\Search;
use Searchcraft\Api\Stopwords;
use Searchcraft\Api\Synonyms;
use Searchcraft\Api\Transactions;

beforeEach(function () {
    $this->apiKey = 'test-api-key';
    $this->apiEndpoint = 'http://test-api-endpoint.com';
    $this->httpClient = Mockery::mock(ClientInterface::class);
    $this->requestFactory = Mockery::mock(RequestFactoryInterface::class);
    $this->streamFactory = Mockery::mock(StreamFactoryInterface::class);
});

afterEach(function () {
    Mockery::close();
});

test('Searchcraft constructor creates instance with defaults', function () {
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_ADMIN,
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );

    expect($searchcraft)->toBeInstanceOf(Searchcraft::class);
    expect($searchcraft->getKeyType())->toBe(Searchcraft::KEY_TYPE_ADMIN);
});

test('Searchcraft constructor supports READ key type', function () {
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_READ,
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );

    expect($searchcraft->getKeyType())->toBe(Searchcraft::KEY_TYPE_READ);
});

test('Searchcraft constructor supports INGEST key type', function () {
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_INGEST,
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );

    expect($searchcraft->getKeyType())->toBe(Searchcraft::KEY_TYPE_INGEST);
});

test('Searchcraft constructor accepts custom API endpoint', function () {
    $customEndpoint = 'https://custom-api.example.com';
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_ADMIN,
        $customEndpoint,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );

    // We can't directly test the private property, but we can verify it works
    // by checking the API endpoint is passed to a new API instance
    $search = $searchcraft->search();
    expect($search)->toBeInstanceOf(Search::class);
});

test('Searchcraft throws exception with invalid key type', function () {
    expect(fn() => new Searchcraft(
        $this->apiKey,
        'invalid-key-type',
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    ))->toThrow(SearchcraftException::class);
});

test('Searchcraft::getVersion returns correct version string', function () {
    $version = Searchcraft::getVersion();
    expect($version)->toBeString();
    expect($version)->toMatch('/^Searchcraft PHP \(v\d+\.\d+\.\d+\)$/');
});

test('Searchcraft::search returns Search instance', function () {
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_ADMIN,
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );
    expect($searchcraft->search())->toBeInstanceOf(Search::class);
});

test('Searchcraft::index returns Index instance', function () {
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_ADMIN,
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );
    expect($searchcraft->index())->toBeInstanceOf(Index::class);
});

test('Searchcraft::documents returns Documents instance', function () {
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_ADMIN,
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );
    expect($searchcraft->documents())->toBeInstanceOf(Documents::class);
});

test('Searchcraft::authentication returns Authentication instance', function () {
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_ADMIN,
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );
    expect($searchcraft->authentication())->toBeInstanceOf(Authentication::class);
});

test('Searchcraft::federation returns Federation instance', function () {
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_ADMIN,
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );
    expect($searchcraft->federation())->toBeInstanceOf(Federation::class);
});

test('Searchcraft::healthcheck returns Healthcheck instance', function () {
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_ADMIN,
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );
    expect($searchcraft->healthcheck())->toBeInstanceOf(Healthcheck::class);
});

test('Searchcraft::stopwords returns Stopwords instance', function () {
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_ADMIN,
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );
    expect($searchcraft->stopwords())->toBeInstanceOf(Stopwords::class);
});

test('Searchcraft::synonyms returns Synonyms instance', function () {
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_ADMIN,
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );
    expect($searchcraft->synonyms())->toBeInstanceOf(Synonyms::class);
});

test('Searchcraft::transactions returns Transactions instance', function () {
    $searchcraft = new Searchcraft(
        $this->apiKey,
        Searchcraft::KEY_TYPE_ADMIN,
        null,
        $this->httpClient,
        $this->requestFactory,
        $this->streamFactory
    );
    expect($searchcraft->transactions())->toBeInstanceOf(Transactions::class);
});
