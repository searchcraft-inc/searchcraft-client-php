![Build faster, ship more. The next frontier in developer search tools is here. Searchcraft](./header.png)

# Searchcraft API PHP Client

A [PSR-compatible](https://www.php-fig.org/psr/) PHP client for the Searchcraft API.

[![Test Status](https://github.com/searchcraft-inc/searchcraft-client-php/actions/workflows/tests.yml/badge.svg)](https://github.com/searchcraft-inc/searchcraft-client-php/actions/workflows/tests.yml)

#### [Documentation](https://docs.searchcraft.io?utm_campaign=oss&utm_source=github&utm_medium=searchcraft-php-client) | [Discord](https://discord.com/invite/y3zUHkBk6e) | [FAQ](https://www.searchcraft.io/frequently-asked-questions?utm_campaign=oss&utm_source=github&utm_medium=searchcraft-php-client) | [Issues / Requests](https://github.com/searchcraft-inc/searchcraft-issues) | [Searchcraft Cloud](https://vektron.searchcraft.io?utm_campaign=oss&utm_source=github&utm_medium=searchcraft-php-client) | [Searchcraft Website](https://searchcraft.io?utm_campaign=oss&utm_source=github&utm_medium=searchcraft-php-client)

## Installation

```bash
composer require searchcraft/searchcraft-php
```

You will also need to install a PSR-18 compatible HTTP client, such as [Guzzle](https://github.com/guzzle/guzzle).

```bash
composer require guzzlehttp/guzzle http-interop/http-factory-guzzle:^1.0
```

## Basic Usage

### Initialize the client

The client can be initialized with different types of API keys, depending on your access requirements:

```php
use Searchcraft\Searchcraft;

// Using an admin key (full access)
$searchcraft_full = new Searchcraft('your-admin-key');

// Using a read-only key (search and read operations only)
$searchcraft_reader = new Searchcraft('your-read-key', Searchcraft::KEY_TYPE_READ);

// Using an ingest key (document operations only)
$searchcraft_ingestion = new Searchcraft('your-ingest-key', Searchcraft::KEY_TYPE_INGEST);
```

By default, the client connects to `http://localhost:8000`. To use a different endpoint such as a Searchcraft Cloud cluster:

```php
// Replace with your cluster endpoint
$searchcraft = new Searchcraft(
    'your-api-key',
    Searchcraft::KEY_TYPE_ADMIN,
    'https://yourcluster.io'
);
```

### Using PSR-18 HTTP Client

The client uses [PSR-18 HTTP Client discovery](https://github.com/php-http/discovery) to find an available HTTP client. You can also provide your own:

```php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

$httpClient = new Client();
$requestFactory = new HttpFactory();
$streamFactory = new HttpFactory();

$searchcraft = new Searchcraft(
    'your-api-key',
    Searchcraft::KEY_TYPE_ADMIN,
    'https://api.searchcraft.io/v1',
    $httpClient,
    $requestFactory,
    $streamFactory
);
```

Searchcraft PHP Client does not include a specific HTTP client in its `require` dependencies but Guzzle is recommended. You will need to install one in order to use the Searchcraft client.

## Search Operations

Search operations require an admin key or read key.

### Basic Search

```php
// Simple search query
$results = $searchcraft->search()->query('my_index', 'search term');

// Search with additional parameters
$results = $searchcraft->search()->query('my_index', 'search term', [
    'limit' => 20,
    'offset' => 0,
    'sort' => 'price:asc',
    'mode' => 'fuzzy'
]);

// Fuzzy search (default)
$results = $searchcraft->search()->query('my_index', 'search term');

// Explicit fuzzy search
$results = $searchcraft->search()->query('my_index', 'search term', ['mode' => 'fuzzy']);

// Exact search
$results = $searchcraft->search()->query('my_index', 'search term', ['mode' => 'exact']);

```

### Federation Search

```php
// Search across all indexes in a federation using federatedQuery
$searchResults = $searchcraft->search()->federatedQuery('my_federation', 'breaking news', [
    'limit' => 20,
    'offset' => 0,
    'mode' => 'fuzzy'
]);

// Federation search with additional options
$searchResults = $searchcraft->search()->federatedQuery('my_federation', 'search term', [
    'limit' => 50,
    'offset' => 10,
    'order_by' => 'publishedAt',
    'sort' => 'desc',
    'occur' => 'should',
    'mode' => 'exact'
]);
```

## Index Operations

Index administration operations require an admin key.

### List Indexes

```php
$indexes = $searchcraft->index()->listIndexes();
```

### Get Index Details

```php
$indexDetails = $searchcraft->index()->getIndex('products');
```

### Create Index

```php
$newIndex = $searchcraft->index()->createIndex('blog', [
    'index' => [
        'name' => 'blog',
        'language' => 'en',
        'search_fields' => ['title', 'content', 'tags'],
        'fields' => [
            'id' => [
                'type' => 'text',
                'required' => true,
                'stored' => true,
                'indexed' => false
            ],
            'title' => [
                'type' => 'text',
                'stored' => true
            ],
            'content' => [
                'type' => 'text',
                'stored' => true
            ],
            'tags' => [
                'type' => 'text',
                'stored' => true,
                'multi' => true
            ],
            'category' => [
                'type' => 'facet',
                'stored' => true
            ],
            'publishedAt' => [
                'type' => 'datetime',
                'fast' => true,
                'stored' => true,
                'indexed' => true
            ]
        ],
        'weight_multipliers' => [
            'title' => 2.0,
            'tags' => 1.0,
            'content' => 0.6
        ]
    ]
]);
```

### Update Index

Note if you are not adding, removing or changing properties of schema fields will likely want to use the `PATCH` operation instead. An update request will remove existing documents but for patchable updates your index is not emptied. See the [docs](https://docs.searchcraft.io/api/schema/?utm_campaign=oss&utm_source=github&utm_medium=searchcraft-php-client) for details on which properties are patchable.

```php
$updatedIndex = $searchcraft->index()->updateIndex('blog', [
    'index' => [
        'name' => 'blog',
        'language' => 'en',
        'search_fields' => ['title', 'content', 'tags', 'summary'],
        'fields' => [
            'id' => [
                'type' => 'text',
                'required' => true,
                'stored' => true,
                'indexed' => false
            ],
            'title' => [
                'type' => 'text',
                'stored' => true
            ],
            'content' => [
                'type' => 'text',
                'stored' => true
            ],
            'summary' => [
                'type' => 'text',
                'stored' => true
            ],
            'tags' => [
                'type' => 'text',
                'stored' => true,
                'multi' => true
            ],
            'category' => [
                'type' => 'facet',
                'stored' => true
            ],
            'publishedAt' => [
                'type' => 'datetime',
                'fast' => true,
                'stored' => true,
                'indexed' => true
            ]
        ],
        'weight_multipliers' => [
            'title' => 2.0,
            'content' => 1.0,
            'summary' => 1.5
        ]
    ]
]);
```

### Patch Index

```php
$patchedIndex = $searchcraft->index()->patchIndex('blog', [
    'search_fields' => ['title', 'content', 'tags', 'summary'],
    'weight_multipliers' => [
        'title' => 3.0,
        'content' => 1.0,
        'summary' => 1.5,
        'tags' => 0.8
    ],
    'language' => 'en',
    'time_decay_field' => 'publishedAt',
    'auto_commit_delay' => 2,
    'exclude_stop_words' => true
]);
```

### Delete Index

```php
$result = $searchcraft->index()->deleteIndex('my-index');
```

## Document Operations

Document operations require an admin key or ingest key.

### Add Documents

```php
$result = $searchcraft->index()->addDocuments('products', [
    [
        'id' => '1',
        'name' => 'Smartphone X',
        'price' => 699.99,
        'category' => 'Electronics',
        'brand' => 'BrandName'
    ],
    [
        'id' => '2',
        'name' => 'Laptop Pro',
        'price' => 1299.99,
        'category' => 'Electronics',
        'brand' => 'BrandName'
    ]
]);
```

### Update Documents

```php
$result = $searchcraft->index()->updateDocuments('products', [
    [
        'id' => '1',
        'price' => 649.99,
        'in_stock' => true
    ]
]);
```

### Get Document

```php
$document = $searchcraft->index()->getDocument('products', '1');
```

### Delete Documents

```php
$result = $searchcraft->index()->deleteDocuments('products', ['1', '2']);
```

## Federation Operations

Federation operations allow you to manage federations that combine multiple indexes for cross-index search. Federation administration operations require an admin key, while federation search requires a read key.

### List Federations

```php
// List all federations
$federations = $searchcraft->federation()->listFederations();
```

### Get Federation Details

```php
// Get details of a specific federation
$federation = $searchcraft->federation()->getFederation('galaxy_news_federation');
```

### Get Federations by Organization

```php
// Get all federations for a specific organization
$organizationFederations = $searchcraft->federation()->getFederationsByOrganization('4');
```

### Create Federation

```php
// Create a new federation with weighted index configurations
$newFederation = $searchcraft->federation()->createFederation([
    'name' => '4_galaxy_news_test',
    'friendly_name' => 'Galaxy News Test Federation',
    'created_by' => '1',
    'last_modified_by' => '1',
    'organization_id' => '4',
    'index_configurations' => [
        [
            'name' => 'news_articles',
            'weight_multiplier' => 1.0
        ],
        [
            'name' => 'blog_posts',
            'weight_multiplier' => 0.8
        ],
        [
            'name' => 'press_releases',
            'weight_multiplier' => 1.5
        ]
    ]
]);
```

### Update Federation

```php
// Update an existing federation
$updatedFederation = $searchcraft->federation()->updateFederation('galaxy_news_federation', [
    'friendly_name' => 'Updated Galaxy News Federation',
    'last_modified_by' => '1',
    'organization_id' => '4',
    'index_configurations' => [
        [
            'name' => 'news_articles',
            'weight_multiplier' => 1.2
        ],
        [
            'name' => 'blog_posts',
            'weight_multiplier' => 0.9
        ],
        [
            'name' => 'press_releases',
            'weight_multiplier' => 1.8
        ],
        [
            'name' => 'social_media',
            'weight_multiplier' => 0.6
        ]
    ]
]);
```

### Delete Federation

```php
// Delete a federation
$result = $searchcraft->federation()->deleteFederation('old_federation');
```

## Error Handling

All operations should be wrapped in a try/catch block to handle errors:

```php
use Searchcraft\Exception\SearchcraftException;

try {
    $results = $searchcraft->search()->query('products', 'smartphone');
} catch (SearchcraftException $e) {
    echo 'Error: ' . $e->getMessage();
}
```

## Documentation

For view the rest of the endpoints and operations, please refer to the full [Searchcraft API documentation](https://docs.searchcraft.io?utm_campaign=oss&utm_source=github&utm_medium=searchcraft-php-client).

## To run unit tests

`./vendor/bin/pest`

## License

[Apache 2.0 License](LICENSE)
