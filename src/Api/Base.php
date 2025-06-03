<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Searchcraft\Exception\SearchcraftException;
use Searchcraft\Searchcraft;

abstract class Base
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiEndpoint;

    /**
     * @var string
     */
    protected $keyType;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @param string $apiKey Your Searchcraft API key
     * @param string $apiEndpoint API endpoint
     * @param ClientInterface $httpClient PSR-18 HTTP Client
     * @param RequestFactoryInterface $requestFactory PSR-17 Request Factory
     * @param StreamFactoryInterface $streamFactory PSR-17 Stream Factory
     * @param string $keyType Type of API key (ingest, read, or admin)
     */
    public function __construct(
        string $apiKey,
        string $apiEndpoint,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $keyType = Searchcraft::KEY_TYPE_ADMIN
    ) {
        $this->apiKey = $apiKey;
        $this->apiEndpoint = $apiEndpoint;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->keyType = $keyType;
    }

    /**
     * Perform a request to the API
     *
     * @param string $method HTTP method
     * @param string $path API path
     * @param array $params Request parameters
     * @param array $headers Additional headers
     * @return array Response data
     * @throws SearchcraftException On request error
     */
    protected function request(string $method, string $path, array $params = [], array $headers = []): array
    {
        // Ensure path starts with a slash
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }

        $url = $this->apiEndpoint . $path;
        $request = $this->requestFactory->createRequest($method, $url);

        // Add standard headers
        $request = $request->withHeader('User-Agent', \Searchcraft\Searchcraft::getVersion())
            ->withHeader('Authorization', $this->apiKey)
            ->withHeader('Accept', 'application/json');

        // Add custom headers
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        // Add request body for POST, PUT, PATCH, DELETE
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']) && !empty($params)) {
            $request = $request->withHeader('Content-Type', 'application/json');
            $body = $this->streamFactory->createStream(json_encode($params));
            $request = $request->withBody($body);
        } elseif ($method === 'GET' && !empty($params)) {
            // Add query parameters for GET
            $url .= '?' . http_build_query($params);
            $request = $request->withHeader('User-Agent', 'Searchcraft-PHP-Client ' . \Searchcraft\Searchcraft::getVersion())
                ->withHeader('Authorization', $this->apiKey)
                ->withHeader('Accept', 'application/json');

            foreach ($headers as $name => $value) {
                $request = $request->withHeader($name, $value);
            }
        }

        try {
            $response = $this->httpClient->sendRequest($request);
            return $this->handleResponse($response);
        } catch (\Throwable $e) {
            throw new SearchcraftException('API request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Handle API response
     *
     * @param ResponseInterface $response
     * @return array
     * @throws SearchcraftException
     */
    protected function handleResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SearchcraftException('Invalid JSON response from API');
        }

        if ($statusCode >= 400) {
            throw SearchcraftException::fromApiResponse($data, $statusCode);
        }

        return $data;
    }
}
