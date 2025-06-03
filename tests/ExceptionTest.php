<?php
use Searchcraft\Exception\SearchcraftException;

test('SearchcraftException::constructor', function () {
    $message = 'Test error message';
    $code = 400;
    $previous = new \Exception('Previous exception');
    $errorData = ['error_code' => 'BAD_REQUEST', 'detail' => 'Invalid parameter'];

    $exception = new SearchcraftException($message, $code, $previous, $errorData);

    expect($exception->getMessage())->toBe($message);
    expect($exception->getCode())->toBe($code);
    expect($exception->getPrevious())->toBe($previous);
    expect($exception->getErrorData())->toBe($errorData);
});

test('SearchcraftException::fromApiResponse', function () {
    $responseData = [
        'error' => [
            'message' => 'Invalid query parameters',
            'code' => 400,
            'details' => 'The parameter "limit" must be a positive integer'
        ]
    ];
    $statusCode = 400;

    $exception = SearchcraftException::fromApiResponse($responseData, $statusCode);

    expect($exception->getMessage())->toBe('Invalid query parameters');
    expect($exception->getCode())->toBe(400);
    expect($exception->getErrorData())->toBe($responseData['error']);
});

test('SearchcraftException::fromApiResponse with missing error code', function () {
    $responseData = [
        'error' => [
            'message' => 'Server error',
            'details' => 'Internal server error'
        ]
    ];
    $statusCode = 500;

    $exception = SearchcraftException::fromApiResponse($responseData, $statusCode);

    expect($exception->getMessage())->toBe('Server error');
    expect($exception->getCode())->toBe(500);
    expect($exception->getErrorData())->toBe($responseData['error']);
});

test('SearchcraftException::fromApiResponse with unknown error format', function () {
    $responseData = [
        'error' => 'Something went wrong'
    ];
    $statusCode = 500;

    $exception = SearchcraftException::fromApiResponse($responseData, $statusCode);

    expect($exception->getMessage())->toBe('Unknown API error');
    expect($exception->getCode())->toBe(500);
    // The test should now expect the wrapped array format:
    expect($exception->getErrorData())->toBe(['__original_error' => $responseData['error']]);
});


