<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;

/**
 * Handles synonym operations for indexes.
 */
class Synonyms extends Base
{
    /**
     * Get all synonyms for an index.
     *
     * @param string $indexName Name of the index
     * @return array Synonyms for the index
     * @throws SearchcraftException
     */
    public function getSynonyms(string $indexName): array
    {
        return $this->request('GET', "/index/{$indexName}/synonyms");
    }

    /**
     * Add synonyms to an index.
     *
     * @param string $indexName Name of the index
     * @param array $synonyms Array of synonym definitions in format ["synonym:original-term", "lotr:lord of the rings"]
     * @return array Result of the operation
     * @throws SearchcraftException
     */
    public function addSynonyms(string $indexName, array $synonyms): array
    {
        return $this->request('POST', "/index/{$indexName}/synonyms", $synonyms);
    }

    /**
     * Delete specific synonyms from an index.
     *
     * @param string $indexName Name of the index
     * @param array $synonyms Array of synonym terms to delete
     * @return array Result of the operation
     * @throws SearchcraftException
     */
    public function deleteSynonyms(string $indexName, array $synonyms): array
    {
        return $this->request('DELETE', "/index/{$indexName}/synonyms", $synonyms);
    }

    /**
     * Delete all synonyms from an index.
     *
     * @param string $indexName Name of the index
     * @return array Result of the operation
     * @throws SearchcraftException
     */
    public function deleteAllSynonyms(string $indexName): array
    {
        return $this->request('DELETE', "/index/{$indexName}/synonyms/all");
    }
}
