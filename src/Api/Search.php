<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;

/**
 * Class for interacting with the Searchcraft search endpoints.
 */
class Search extends Base
{
    /**
     * Perform a search query on an index.
     *
     * @param string $indexName The name of the index to search
     * @param string|array $query The search query string or complex query object
     * @param array $options Search options including:
     *   - limit: Maximum number of results to return (default: 20)
     *   - offset: Number of results to skip (default: 0)
     *   - order_by: Field to order results by
     *   - sort: Sort direction "asc" or "desc" (default: "desc")
     *   - occur: "should" (default) or "must" for Boolean queries
     *   - mode: "fuzzy" (default) or "exact" query mode for string queries
     * @return array Search results
     * @throws SearchcraftException
     */
    public function query(string $indexName, $query, array $options = []): array
    {
        $params = $this->buildSearchParams($query, $options);

        return $this->request('POST', "/index/{$indexName}/search", $params);
    }

    /**
     * Perform a federated search query across multiple indices.
     *
     * @param string $federationName The name of the federation to search
     * @param string|array $query The search query string or complex query object
     * @param array $options Search options including:
     *   - limit: Maximum number of results to return (default: 20)
     *   - offset: Number of results to skip (default: 0)
     *   - order_by: Field to order results by
     *   - sort: Sort direction "asc" or "desc" (default: "desc")
     *   - occur: "should" (default) or "must" for Boolean queries
     *   - mode: "fuzzy" (default) or "exact" query mode for string queries
     * @return array Search results from all indices in the federation
     * @throws SearchcraftException
     */
    public function federatedQuery(string $federationName, $query, array $options = []): array
    {
        $params = $this->buildSearchParams($query, $options);

        return $this->request('POST', "/federation/{$federationName}/search", $params);
    }

    /**
     * Build search parameters from query and options
     *
     * @param string|array $query The search query
     * @param array $options Additional search options
     * @return array The formatted search parameters
     */
    private function buildSearchParams($query, array $options = []): array
    {
        $params = [];

        // Handle query parameter
        if (is_string($query)) {
            // Get query mode - fuzzy (default) or exact
            $mode = isset($options['mode']) && $options['mode'] === 'exact' ? 'exact' : 'fuzzy';

            $params['query'] = [
                $mode => [
                    'ctx' => $query
                ]
            ];
        } elseif (is_array($query)) {
            // Complex query object - pass as is
            $params['query'] = $query;
        }

        // Add pagination, sorting and other options
        if (isset($options['limit'])) {
            $params['limit'] = $options['limit'];
        }

        if (isset($options['offset'])) {
            $params['offset'] = $options['offset'];
        }

        if (isset($options['order_by'])) {
            $params['order_by'] = $options['order_by'];
        }

        if (isset($options['sort'])) {
            $params['sort'] = $options['sort'];
        }

        // Add any other options directly to the params
        foreach ($options as $key => $value) {
            if (!in_array($key, ['limit', 'offset', 'order_by', 'sort', 'mode'])) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
