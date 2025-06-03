<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;

/**
 * API client for transaction operations with Searchcraft.
 */
class Transactions extends Base
{
    /**
     * Commit a write transaction.
     * Transactions are implicit. Only needed if auto commit is disabled.
     *
     * @param string $indexName Index name
     * @return array Commit result
     * @throws SearchcraftException
     */
    public function commit(string $indexName): array
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
    public function rollback(string $indexName): array
    {
        return $this->request('POST', "/index/{$indexName}/rollback");
    }
}
