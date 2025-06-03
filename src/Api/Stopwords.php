<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;

/**
 * API client for stopwords operations with Searchcraft.
 */
class Stopwords extends Base
{
    /**
     * Get the stopwords for an index.
     *
     * @param string $indexName The name of the index
     * @return array List of stopwords
     * @throws SearchcraftException
     */
    public function getStopwords(string $indexName): array
    {
        return $this->request('GET', "/index/{$indexName}/stopwords");
    }

    /**
     * Add stopwords to an index.
     *
     * @param string $indexName The name of the index
     * @param array $stopwords Array of stopwords to add
     * @return array Result of the operation
     * @throws SearchcraftException
     */
    public function addStopwords(string $indexName, array $stopwords): array
    {
        return $this->request('POST', "/index/{$indexName}/stopwords", $stopwords);
    }

    /**
     * Delete specific stopwords from an index.
     *
     * @param string $indexName The name of the index
     * @param array $stopwords Array of stopwords to delete
     * @return array Result of the operation
     * @throws SearchcraftException
     */
    public function deleteStopwords(string $indexName, array $stopwords): array
    {
        return $this->request('DELETE', "/index/{$indexName}/stopwords", $stopwords);
    }

    /**
     * Delete all stopwords from an index.
     *
     * @param string $indexName The name of the index
     * @return array Result of the operation
     * @throws SearchcraftException
     */
    public function deleteAllStopwords(string $indexName): array
    {
        return $this->request('DELETE', "/index/{$indexName}/stopwords/all");
    }
}
