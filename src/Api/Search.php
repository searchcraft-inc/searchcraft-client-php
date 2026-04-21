<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;
use Searchcraft\Validators;

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
     *   - limit: Maximum number of results to return (default: 20, max: 200)
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
        $this->validatePagination($options);
        $params = $this->buildSearchParams($query, $options);

        return $this->request('POST', "/index/{$indexName}/search", $params);
    }

    /**
     * Perform a federated search query across multiple indices.
     *
     * @param string $federationName The name of the federation to search
     * @param string|array $query The search query string or complex query object
     * @param array $options Search options including:
     *   - limit: Maximum number of results to return (default: 20, max: 200)
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
        $this->validatePagination($options);
        $params = $this->buildSearchParams($query, $options);

        return $this->request('POST', "/federation/{$federationName}/search", $params);
    }

    /**
     * Request a streaming AI-generated summary for search results on an index.
     *
     * The engine streams Server-Sent Events (SSE) — an initial `metadata`
     * event with `results_count` and `cached` flags, zero or more `delta`
     * events each containing a `content` chunk of the summary text, and a
     * final `done` event. When the generator errors an `error` event is
     * emitted instead. The index must have `ai_enabled: true` and an
     * AI configuration with a configured `search_summary` section.
     *
     * Added in Searchcraft Engine 0.10.0.
     *
     * @param string $indexName The name of the index to summarize.
     * @param string|array $query The search query string or a complex query
     *                            object. Strings are wrapped in a
     *                            `fuzzy` / `exact` term per
     *                            `$options['mode']`; arrays are forwarded
     *                            to the engine verbatim.
     * @param array $options Same options as {@see self::query} plus any
     *                       additional payload fields. Unknown keys are
     *                       forwarded to the engine.
     * @param callable|null $onEvent Optional callback invoked for each SSE
     *                               event as it is received. Signature:
     *                               `fn(string $event, mixed $data): void`.
     * @return array List of parsed SSE events. Each event is shaped as
     *               `['event' => string, 'data' => mixed]`, where `data`
     *               is JSON-decoded when the SSE `data:` field parses as
     *               JSON and the raw string otherwise.
     * @throws SearchcraftException When pagination is invalid, on network
     *                              failure, or on an HTTP status >= 400.
     */
    public function searchSummary(
        string $indexName,
        $query,
        array $options = [],
        ?callable $onEvent = null
    ): array {
        $this->validatePagination($options);
        $params = $this->buildSearchParams($query, $options);

        return $this->streamRequest(
            'POST',
            "/index/{$indexName}/search/summary",
            $params,
            $onEvent
        );
    }

    /**
     * Validate pagination options (limit and offset) when supplied.
     *
     * @param array $options The caller-supplied search options array. Only
     *                       the `limit` and `offset` keys are inspected;
     *                       other keys are ignored.
     * @return void
     * @throws SearchcraftException When `limit` or `offset` is present but
     *                              fails its corresponding validator.
     */
    private function validatePagination(array $options): void
    {
        if (isset($options['limit'])) {
            Validators::validateLimit($options['limit']);
        }

        if (isset($options['offset'])) {
            Validators::validateOffset($options['offset']);
        }
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
