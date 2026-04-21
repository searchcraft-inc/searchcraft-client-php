<?php

use Searchcraft\Exception\SearchcraftException;
use Searchcraft\Validators;

test('Validators::validateLimit accepts values 1 through MAX_SEARCH_LIMIT', function () {
    Validators::validateLimit(1);
    Validators::validateLimit(50);
    Validators::validateLimit(Validators::MAX_SEARCH_LIMIT);
    expect(true)->toBeTrue();
});

test('Validators::validateLimit rejects zero', function () {
    expect(fn () => Validators::validateLimit(0))
        ->toThrow(SearchcraftException::class, 'limit must be a positive integer');
});

test('Validators::validateLimit rejects negative values', function () {
    expect(fn () => Validators::validateLimit(-1))
        ->toThrow(SearchcraftException::class, 'limit must be a positive integer');
});

test('Validators::validateLimit rejects values above the engine cap', function () {
    expect(fn () => Validators::validateLimit(Validators::MAX_SEARCH_LIMIT + 1))
        ->toThrow(SearchcraftException::class, 'limit cannot exceed 200');
});

test('Validators::validateLimit rejects non-integers', function () {
    expect(fn () => Validators::validateLimit('10'))
        ->toThrow(SearchcraftException::class, 'limit must be a positive integer');

    expect(fn () => Validators::validateLimit(10.5))
        ->toThrow(SearchcraftException::class, 'limit must be a positive integer');
});

test('Validators::validateOffset accepts zero and positive integers', function () {
    Validators::validateOffset(0);
    Validators::validateOffset(100);
    expect(true)->toBeTrue();
});

test('Validators::validateOffset rejects negative values', function () {
    expect(fn () => Validators::validateOffset(-1))
        ->toThrow(SearchcraftException::class, 'offset must be a non-negative integer');
});

test('Validators::validateOffset rejects non-integers', function () {
    expect(fn () => Validators::validateOffset('5'))
        ->toThrow(SearchcraftException::class, 'offset must be a non-negative integer');
});

test('Search::query rejects over-limit limits before making the request', function () {
    $searchcraft = new \Searchcraft\Searchcraft(
        'test-key',
        \Searchcraft\Searchcraft::KEY_TYPE_ADMIN,
        null,
        Mockery::mock(\Psr\Http\Client\ClientInterface::class),
        Mockery::mock(\Psr\Http\Message\RequestFactoryInterface::class),
        Mockery::mock(\Psr\Http\Message\StreamFactoryInterface::class)
    );

    expect(fn () => $searchcraft->search()->query('products', 'foo', ['limit' => 500]))
        ->toThrow(SearchcraftException::class, 'limit cannot exceed 200');

    Mockery::close();
});
