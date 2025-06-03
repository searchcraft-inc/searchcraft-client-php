<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;

/**
 * API client for document operations with Searchcraft.
 */
class Documents extends Base
{
    /**
     * Add one or several documents to an index.
     *
     * @param string $indexName Index name
     * @param array $documents Array of document objects
     * @return array Response data
     * @throws SearchcraftException
     */
    public function addDocuments(string $indexName, array $documents): array
    {
        return $this->request('POST', "/index/{$indexName}/documents", $documents);
    }

    /**
     * Delete one or several documents from an index by field term match.
     * Example: {title: foo} or {id: "xyz"}
     *
     * @param string $indexName Index name
     * @param array $criteria Criteria to match documents to delete
     * @return array Deletion result
     * @throws SearchcraftException
     */
    public function deleteDocumentsByField(string $indexName, array $criteria): array
    {
        return $this->request('DELETE', "/index/{$indexName}/documents", $criteria);
    }

    /**
     * Get a single document from an index by its internal Searchcraft ID.
     *
     * @param string $indexName Index name
     * @param string $documentId Internal Searchcraft document ID (_id)
     * @return array Document data
     * @throws SearchcraftException
     */
    public function getDocument(string $indexName, string $documentId): array
    {
        return $this->request('GET', "/index/{$indexName}/documents/{$documentId}");
    }

    /**
     * Delete a single document from an index by its internal Searchcraft ID.
     *
     * @param string $indexName Index name
     * @param string $documentId Internal Searchcraft document ID (_id)
     * @return array Deletion result
     * @throws SearchcraftException
     */
    public function deleteDocument(string $indexName, string $documentId): array
    {
        return $this->request('DELETE', "/index/{$indexName}/documents/{$documentId}");
    }

    /**
     * Delete one or several documents from an index by query match.
     *
     * @param string $indexName Index name
     * @param array $query Query to match documents to delete
     * @return array Deletion result
     * @throws SearchcraftException
     */
    public function deleteDocumentsByQuery(string $indexName, array $query): array
    {
        return $this->request('DELETE', "/index/{$indexName}/documents/query", $query);
    }

    /**
     * Delete all documents from an index.
     *
     * @param string $indexName Index name
     * @return array Deletion result
     * @throws SearchcraftException
     */
    public function deleteAllDocuments(string $indexName): array
    {
        return $this->request('DELETE', "/index/{$indexName}/documents/all");
    }

    /**
     * Commit a write transaction.
     * Transactions are implicit. Only needed if auto commit is disabled.
     *
     * @param string $indexName Index name
     * @return array Commit result
     * @throws SearchcraftException
     */
    public function commitTransaction(string $indexName): array
    {
        return $this->request('POST', "/index/{$indexName}/commit");
    }

    /**
     * Rollback a write transaction.
     * Transactions are implicit. Only needed if auto commit is disabled.
     *
     * @param string $indexName Index name
     * @return array Rollback result
     * @throws SearchcraftException
     */
    public function rollbackTransaction(string $indexName): array
    {
        return $this->request('POST', "/index/{$indexName}/rollback");
    }
}
