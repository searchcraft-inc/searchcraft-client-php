<?php

declare(strict_types=1);

namespace Searchcraft;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
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

class Searchcraft
{
    public const VERSION = '0.7.5';
    public const DEFAULT_API_ENDPOINT = 'http://localhost:8000';

    // API Key types
    public const KEY_TYPE_INGEST = 'ingest';
    public const KEY_TYPE_READ = 'read';
    public const KEY_TYPE_ADMIN = 'admin';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $keyType;

    /**
     * @var string
     */
    private $apiEndpoint;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @param string $apiKey Your Searchcraft API key
     * @param string $keyType Type of API key (ingest, read, or admin)
     * @param string|null $apiEndpoint Optional custom API endpoint
     * @param ClientInterface|null $httpClient PSR-18 HTTP Client
     * @param RequestFactoryInterface|null $requestFactory PSR-17 Request Factory
     * @param StreamFactoryInterface|null $streamFactory PSR-17 Stream Factory
     * @throws SearchcraftException If an invalid key type is provided
     */
    public function __construct(
        string $apiKey,
        string $keyType = self::KEY_TYPE_ADMIN,
        ?string $apiEndpoint = null,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ) {
        // Validate key type
        if (!in_array($keyType, [self::KEY_TYPE_INGEST, self::KEY_TYPE_READ, self::KEY_TYPE_ADMIN])) {
            throw new Exception\SearchcraftException(
                sprintf('Invalid key type "%s". Must be one of: ingest, read, admin', $keyType)
            );
        }

        $this->apiKey = $apiKey;
        $this->keyType = $keyType;
        $this->apiEndpoint = $apiEndpoint ?? self::DEFAULT_API_ENDPOINT;
        $this->httpClient = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * @return non-empty-string
     */
    public static function getVersion(): string
    {
        return \sprintf('Searchcraft PHP (v%s)', self::VERSION);
    }

    /**
     * @return string
     */
    public function getKeyType(): string
    {
        return $this->keyType;
    }

    /**
     * Authentication key operations.
     *
     * @return Authentication
     * @throws SearchcraftException
     */
    public function authentication(): Authentication
    {
        return new Authentication(
            $this->apiKey,
            $this->apiEndpoint,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->keyType
        );
    }
    /**
    * Document management operations.
    *
    * @return Documents
    */
    public function documents(): Documents
    {

        return new Documents(
            $this->apiKey,
            $this->apiEndpoint,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->keyType
        );
    }

    /**
     * Federation operations
     *
     * @return Federation
     * @throws SearchcraftException
     */
    public function federation(): Federation
    {

        return new Federation(
            $this->apiKey,
            $this->apiEndpoint,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->keyType
        );
    }

    /**
     * Healthcheck operations.
     *
     * @return Healthcheck
     * @throws SearchcraftException
     */
    public function healthcheck(): Healthcheck
    {
        return new Healthcheck(
            $this->apiKey,
            $this->apiEndpoint,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->keyType
        );
    }

    /**
    * Index management operations.
    *
    * @return Index
    * @throws SearchcraftException
     */
    public function index(): Index
    {

        return new Index(
            $this->apiKey,
            $this->apiEndpoint,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->keyType
        );
    }

    /**
     * Search operations
     *
     * @return Search
     * @throws SearchcraftException
     */
    public function search(): Search
    {

        return new Search(
            $this->apiKey,
            $this->apiEndpoint,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->keyType
        );
    }

    /**
     * Stopwords operations.
     *
     * @return Stopwords
     * @throws SearchcraftException
     */
    public function stopwords(): Stopwords
    {
        return new Stopwords(
            $this->apiKey,
            $this->apiEndpoint,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->keyType
        );
    }

    /**
     * Synonyms operations.
     *
     * @return Synonyms
     * @throws SearchcraftException
     */
    public function synonyms(): Synonyms
    {
        return new Synonyms(
            $this->apiKey,
            $this->apiEndpoint,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->keyType
        );
    }

    /**
    * Transaction operations.
    *
    * @return Transactions
    * @throws SearchcraftException
    */
    public function transactions(): Transactions
    {

        return new Transactions(
            $this->apiKey,
            $this->apiEndpoint,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->keyType
        );
    }

}
