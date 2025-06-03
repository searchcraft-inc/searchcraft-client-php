<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;

/**
 * Class for interacting with the Searchcraft index and document endpoints.
 */
class Index extends Base
{
    /**
     * List all indexes.
     *
     * @return array List of indexes
     * @throws SearchcraftException
     */
    public function listIndexes(): array
    {
        return $this->request('GET', '/index');
    }

    /**
     * Get an index details.
     *
     * @param string $indexName Index name
     * @return array Index details
     * @throws SearchcraftException
     */
    public function getIndex(string $indexName): array
    {
        return $this->request('GET', "/index/{$indexName}");
    }

    /**
     * Get index statistics.
     *
     * @param string $indexName Index name
     * @return array Index stats
     * @throws SearchcraftException
     */
    public function getIndexStats(string $indexName): array
    {
        return $this->request('GET', "/index/{$indexName}/stats");
    }

    /**
     * Create a new index.
     *
     * @param string $indexName Index name
     * @param array $options Index configuration options
     * @return array Created index details
     * @throws SearchcraftException
     */
    public function createIndex(string $indexName, array $options = []): array
    {
        $params = array_merge(['name' => $indexName], $options);
        return $this->request('POST', '/index', [ 'index' => $params ]);
    }

    /**
     * Update an index configuration.
     *
     * @param string $indexName Index name
     * @param array $options Index configuration options to update
     * @return array Updated index details
     * @throws SearchcraftException
     */
    public function updateIndex(string $indexName, array $options): array
    {
        $params = array_merge(['name' => $indexName], $options);
        return $this->request('PUT', "/index/{$indexName}", [ 'index' => $params ] );
    }

    /**
     * Patch an index configuration.
     *
     * Allows partial configuration changes without re-ingesting data.
     * Updates are limited to search_fields, weight_multipliers, language,
     * time_decay_field, auto_commit_delay and exclude_stop_words settings.
     *
     * @param string $indexName Index name
     * @param array $options Partial configuration options to update
     * @return array Updated index details
     * @throws SearchcraftException
     */
    public function patchIndex(string $indexName, array $options): array
    {
        // Note: For PATCH operation, payload should not be nested inside an "index" object
        return $this->request('PATCH', "/index/{$indexName}", $options);
    }

    /**
     * Delete an index.
     *
     * @param string $indexName Index name
     * @return array Deletion result
     * @throws SearchcraftException
     */
    public function deleteIndex(string $indexName): array
    {
        return $this->request('DELETE', "/index/{$indexName}");
    }
}
